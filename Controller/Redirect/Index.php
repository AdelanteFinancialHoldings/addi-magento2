<?php


namespace Addi\Payment\Controller\Redirect;

use Exception;
use Addi\Payment\Controller\AbstractAddi;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\Order;

class Index extends AbstractAddi
{
    CONST TIMEOUT = 60;

    /**
     * @var CheckoutSession
     */
    protected $checkSession;

    public function __construct(
        UrlHelper $urlHelper,
        OrderFactory $orderFactory,
        Context $context,
        CheckoutSession $checkSession
    )
    {
        $this->checkSession = $checkSession;
        parent::__construct($urlHelper, $orderFactory, $context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute()
    {
        try{
            /** @var Order $order */
            $order = $this->getOrder();

            if(!$order){
                $this->logger("ERROR REDIRECT CONTROLLER ORDER NOT FOUND");
                return false;
            }

            $timeWaiting = 0;
            do{
                $order = $this->orderFactory->create()->load($order->getId());
                if($order->getStatus() !== 'pending_payment'){
                    break;
                }
                sleep(5);
                $timeWaiting+= 5;
            }while($timeWaiting < self::TIMEOUT);

            if($order->getStatus() == 'processing'){
                $this->checkSession->setLastSuccessQuoteId($order->getQuoteId());
                $this->checkSession->setLastQuoteId($order->getQuoteId());
                $this->checkSession->setLastOrderId($order->getId());

                return $this->_redirect('checkout/onepage/success');
            }else{
                if($timeWaiting > self::TIMEOUT){
                    $this->messageManager->addWarningMessage('Addi Payment wait timeout exceeded');
                }
                return $this->reOrder($order);
            }
        }catch(\Throwable $error){
            $this->logger("ERROR REDIRECT CONTROLLER: ".$error->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getLastRealOrder()
    {
        return $this->checkSession->getLastRealOrder();
    }

    /**
     * @return false|Order
     */
    public function getOrder()
    {
        $lastRealOrder = $this->getLastRealOrder();
        if ($lastRealOrder) {
            return $lastRealOrder;
        }
        return false;
    }

    public function logger($message){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addi.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }

}
