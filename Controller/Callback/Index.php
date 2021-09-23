<?php


namespace Addi\Payment\Controller\Callback;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Addi\Payment\Helper\AddiHelper;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;


class Index extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var AddiHelper
     */
    private $_addiHelper;

    /** @var CartManagementInterface */
    private $_cartManagement;

    /** @var Session */
    private $_checSession;

    /** @var OrderSender */
    private $_orderSender;

    /**
     * Index constructor.
     * @param OrderFactory $orderFactory
     * @param PaymentFactory $paymentFactory
     * @param JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        AddiHelper $addiHelper,
        OrderFactory $orderFactory,
        PaymentFactory $paymentFactory,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CartManagementInterface $cartManagement,
        Session $checSession,
        OrderSender $orderSender,
        Context $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->paymentFactory = $paymentFactory;
        $this->_addiHelper = $addiHelper;
        $this->_cartManagement = $cartManagement;
        $this->_orderSender = $orderSender;
        $this->_checSession = $checSession;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try{
        $retArray = array();
        $authenticationHeader = $this->getRequest()->getHeader('Authorization');

        if (strpos(strtolower($authenticationHeader),'basic') !== 0 ||
            substr($authenticationHeader, 6) != 'bUFnM250b0FkZDE6cGJkITJoIWtFN1MhczVCUw=='){

            http_response_code(401);
            exit;
        }

        $request = file_get_contents("php://input");

        $this->logger("ADDI CALLBACK REQUEST: ". $request);

        $params = json_decode($request);

        $resultJson = $this->resultJsonFactory->create();

        /** @var Order $order */
        $order = $this->orderFactory->create()->loadByIncrementId($params->orderId);

        if ( !$order->getId()) {
            $retArray = array("status" => "reject", "error" => "Order does not exist.");
        } else {
            switch ( $params->status ) {
                case "APPROVED": {

                    try{
                        $this->_orderSender->send($order);
                    }catch(Exception $error){
                        $this->logger($error->getMessage());
                    }

                    $retArray = $this->processApproved($order, $params);
                    break;
                }
                case "DECLINED": case "REJECTED": case "ABANDONED":{
                $retArray = $this->processCanceled($order, $params);
                break;
            }
                default: {
                    $retArray = array("status" => "reject", "error" => "Incorrect Status");
                }
            }
        }

        $this->logger(print_r($retArray, true));

        return $resultJson->setJsonData($request);

        }catch (Exception $error){
            $this->logger($error->getMessage());
            $this->messageManager->addErrorMessage($error->getMessage());
            return $this->_redirect('checkout/cart', ['_secure' => true]);
        }
    }

    /**
     * @param Order $order
     * @param $params
     * @return array
     */
    protected function processApproved(Order $order, $params) {

        if ( !$order->canInvoice() || $order->getGrandTotal() != $params->approvedAmount) {
            return array("status" => "reject", "error" => "Order cannot be invoiced");
        }

        try {
            /** @var Payment $payment */
            $payment = $order->getPayment();
            if (!$payment) {
                $payment = $this->paymentFactory->create();
                $payment->setOrder($order);
            }
            $payment->setAmountPaid($params->approvedAmount);
            $payment->setTransactionId($params->orderId);
            $payment->setParentTransactionId($params->applicationId);
            $payment->setShouldCloseParentTransaction(true);
            $payment->setIsTransactionClosed(0);
            $payment->setAdditionalInformation("addi_order_id",$params->orderId);
            $payment->setAdditionalInformation("addi_application_id",$params->applicationId);
            $payment->setAdditionalInformation("addi_approved_amount",$params->approvedAmount);
            $payment->setAdditionalInformation("addi_currency",$params->currency);
            $payment->setAdditionalInformation("addi_status",$params->status);
            $payment->setAdditionalInformation("addi_status_timestamp",$params->statusTimestamp);
            $payment->capture();
            $order->setPayment($payment);
            $order->setStatus("processing");
            $order->setState("processing");

            $order->save();

        } catch (Exception $e) {
            return array("status" => "reject", "error" => "Error while making invoice. Magento Error: " . $e->getMessage());
        }
        return array("status" => "accept", "error" => "");

    }

    /**
     * @param Order $order
     * @param $params
     * @return array
     */
    protected function processCanceled(Order $order, $params) {

        if ( !$order->canCancel()) {
            return array("status" => "reject", "error" => "Order cannot be canceled");
        }

        try {
            $order->cancel();
            $order->addStatusHistoryComment($params->status);
            $order->save();
        } catch (Exception $e) {
            return array("status" => "reject", "error" => "Error while canceling. Magento Error: " . $e->getMessage());
        }
        return array("status" => "accept", "error" => "");
    }

    public function logger($message){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addi.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }
}

