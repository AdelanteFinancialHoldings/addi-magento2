<?php


namespace Addi\Payment\Model\Payment;
use Detection\MobileDetect;
use Addi\Addi as AddiLib;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Addi\Payment\Helper\AddiHelper;
use Throwable;

class Addi extends AbstractMethod
{

    const PAYMENT_METHOD_ADDI_CODE = 'addi';
    /**
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_ADDI_CODE;

    /**
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var AddiHelper
     */
    protected $_addiHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkSession;

    public function __construct(
        CheckoutSession $checkSession,
        AddiHelper $addiHelper,
        ProductMetadataInterface $productMetadata,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig,
            $logger, $resource, $resourceCollection, $data, $directory);
        $this->productMetadata = $productMetadata;

        $this->checkSession = $checkSession;
        $this->_addiHelper = $addiHelper;
    }



    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(
        CartInterface $quote = null
    ) {
        $clientId = $this->getConfigData('client_id');
        $clientSecret = $this->getConfigData('client_secret');

        if(!$clientSecret && !$clientId){
            return false;
        }

        //currency validation
        $currencyCode = $quote->getCurrency()->getQuoteCurrencyCode();
        if($currencyCode != 'COP'){
            $this->logger('Addi Error: currency code '.$currencyCode.' is not allowed.');
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * @return string
     */
    public function getConfigPaymentAction() {
        return self::ACTION_ORDER;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param DataObject $stateObject
     * @return Addi
     * @throws LocalizedException
     */
    public function initialize($paymentAction, $stateObject) {

        $payment = $this->getInfoInstance();

        /** @var Order $order */
        $order = $payment->getOrder();

        $baseUrl = $order->getStore()->getBaseUrl();

        $isSandbox = $this->_scopeConfig->isSetFlag("payment/addi/sandbox");
        $clientId = $this->_scopeConfig->getValue("payment/addi/client_id");
        $clientSecret = $this->_scopeConfig->getValue("payment/addi/client_secret");
        $successPage = $this->_scopeConfig->getValue("payment/addi/success_page");

        if ( $successPage == "" ) {
            $successPage = $baseUrl;
        }

        try{
            $documentNumber = trim($this->checkSession->getDocumentNumber());

            $addi = new AddiLib(
                $order->getIncrementId(),
                "Addi Payment",
                $clientId,
                $clientSecret,
                $isSandbox,
                '', // agregar la imagen de magento
                $baseUrl."addi/callback/index",
                $baseUrl."addi/redirect/index",
                '',
                '');

            $addi->setAmountDetails($order->getGrandTotal(),$order->getShippingAmount(),$order->getTaxAmount(),$order->getOrderCurrencyCode());

            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach($order->getAllVisibleItems() as $item){
                /* TODO set picture URL , category name */
                $imageURL = $this->_addiHelper->getImage($item->getProduct());
                $categoryName = $this->_addiHelper->getCategoryName($item->getProduct()->getCategoryIds());
                $addi->addNewItem($item->getSku(),$item->getName(),$item->getQtyOrdered(),$item->getPrice(),$item->getTaxAmount(),$imageURL,$categoryName);
            }

            $telephone = "";
            $countryId = "CO";

            if($order->getShippingAddress()){
                $telephone = $order->getShippingAddress()->getTelephone();
            }elseif($order->getBillingAddress()){
                $telephone = $order->getBillingAddress()->getTelephone();
                $countryId = $order->getBillingAddress()->getCountryId();
            }

            $addi->setCustomerData(
                'CC',
                $documentNumber,
                $order->getCustomerFirstname(),
                $order->getCustomerLastname(),
                $order->getCustomerEmail(),
                $telephone,
                $this->_addiHelper->getPhoneCode($countryId));

            // shipping information
            if(!$order->getIsVirtual()){
                $addi->setShippingAddress(
                    implode(" ",$order->getShippingAddress()->getStreet()),
                    $order->getShippingAddress()->getCity(),
                    $order->getShippingAddress()->getCountryId());

            }else{
                if($order->getBillingAddress()){
                    $addi->setShippingAddress(
                        implode(" ",$order->getBillingAddress()->getStreet()),
                        $order->getBillingAddress()->getCity(),
                        $order->getBillingAddress()->getCountryId());
                }else{
                    // if dont have both address
                    $addi->setShippingAddress("virtual product without address","virtual product","CO");
                }
            }

            // billing information
            if($order->getBillingAddress()){
                $addi->setBillingAddress(
                    implode(" ",$order->getBillingAddress()->getStreet()),
                    $order->getBillingAddress()->getCity(),
                    $order->getBillingAddress()->getCountryId());
            }else{
                $addi->setBillingAddress("virtual product without address","virtual product","CO");
            }

            $this->logger(print_r($addi->makeJSONArray(),true));

            $payURL = $addi->getPayURL();

            if(empty($payURL)){
                throw new LocalizedException(__("Ocurrio un error al generar la redirección hacia el portal de Addi."));
            }

            $order->setAddiUrl($payURL);
            $order->setState(Order::STATE_NEW);
            $order->setStatus("pending_payment");
            $order->setCanSendNewEmailFlag(false);
            $order->save();
            $stateObject->setState(Order::STATE_PENDING_PAYMENT);
            $stateObject->setStatus('pending_payment');
            $stateObject->setIsNotified(false);


        }catch(Throwable $error){
               $this->logger($error->getMessage());
               throw new LocalizedException(__($error->getMessage()));
        }

        return $this;
    }

    public function logger($message){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/addi.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }
}
