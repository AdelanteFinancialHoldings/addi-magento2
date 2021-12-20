<?php

namespace Addi\Payment\Model\Payment;
use Addi\Payment\lib\Addi as AddiLib;
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
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Addi\Payment\Logger\Logger as AddiLogger;

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
    protected $_productMetadata;

    /**
     * @var AddiHelper
     */
    protected $_addiHelper;

    /**
     * @var CheckoutSession
     */
    protected $_checkSession;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var AddiLogger
     */
    protected $_addiLogger;



    public function __construct(
        Filesystem $fileSystem,
        CheckoutSession $checkSession,
        AddiHelper $addiHelper,
        ProductMetadataInterface $productMetadata,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        AddiLogger $addiLogger,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = array(),
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig,
            $logger, $resource, $resourceCollection, $data, $directory
        );
        $this->_productMetadata = $productMetadata;

        $this->_checkSession = $checkSession;
        $this->_addiHelper = $addiHelper;
        $this->_fileSystem = $fileSystem;
        $this->_addiLogger = $addiLogger;
    }



    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(
        CartInterface $quote = null
    ) {
        $clientId = $this->getConfigData('credentials/client_id');
        $clientSecret = $this->getConfigData('credentials/client_secret');
        $country = $this->getConfigData('credentials/country');

        if (!$clientSecret && !$clientId) {
            return false;
        }

        //currency validation
        $currencyCode = $quote->getCurrency()->getQuoteCurrencyCode();
        if (!in_array($currencyCode, array('COP','BRL'))) {
            $this->logger('Addi Error: currency code '.$currencyCode.' is not allowed.');
            return false;
        }

        $minAmount = $this->_checkSession->getAddiMinAmount();
        $maxAmount = $this->_checkSession->getAddiMaxAmount();

        if (floatval($quote->getGrandTotal()) < floatval($minAmount)
            || floatval($quote->getGrandTotal()) > floatval($maxAmount)
        ) {
            $this->logger(
                'Addi Error: amount is not allowed. grandtotal:'.
                $quote->getGrandTotal()." country:".$country. ' min:'.$minAmount. ' max:'.$maxAmount
            );
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * @return string
     */
    public function getConfigPaymentAction()
    {
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
    // @codingStandardsIgnoreLine
    public function initialize($paymentAction, $stateObject)
    {

        $callbackURL = '';
        $payment = $this->getInfoInstance();

        /** @var Order $order */
        $order = $payment->getOrder();

        $baseUrl = $order->getStore()->getBaseUrl();

        $isSandbox = boolval($this->getConfigData("credentials/sandbox"));
        $clientId = $this->getConfigData("credentials/client_id");
        $clientSecret = $this->getConfigData("credentials/client_secret");
        $successPage = $this->getConfigData("credentials/success_page");
        $country = $this->getConfigData("credentials/country");


        $logFile = $this->_fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath();

        /*callback controller compatibility with 2.2 version */
        if (strpos($this->_productMetadata->getVersion(), '2.2') !== false) {
            $callbackURL = $baseUrl."addi/callback/post";
        } else {
            $callbackURL = $baseUrl."addi/callback/index";
        }

        if ($successPage == "" ) {
            $successPage = $baseUrl;
        }

        try{
            $documentNumber = trim($this->_checkSession->getDocumentNumber());

            $addi = new AddiLib(
                $order->getIncrementId(),
                "Addi Magento 2 Payment",
                $clientId,
                $clientSecret,
                $country,
                $isSandbox,
                '', // agregar la imagen de magento
                $callbackURL,
                $baseUrl."addi/redirect/index",
                '',
                ''
            );

            $addi->setDebugMode(true);
            $addi->setDestinationLogFile($logFile.'log/addi.log');

            $addi->setAmountDetails(
                $order->getGrandTotal(),
                $order->getShippingAmount(),
                $order->getTaxAmount(),
                $order->getOrderCurrencyCode()
            );

            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($order->getAllVisibleItems() as $item) {
                $imageURL = $this->_addiHelper->getImage($item->getProduct());
                $categoryName = $this->_addiHelper->getCategoryName($item->getProduct()->getCategoryIds());
                $addi->addNewItem(
                    $item->getSku(),
                    $item->getName(),
                    $item->getQtyOrdered(),
                    $item->getPrice(),
                    $item->getTaxAmount(),
                    $imageURL,
                    $categoryName
                );
            }

            $telephone = "";
            $countryId = $country;

            if ($order->getShippingAddress()) {
                $telephone = $order->getShippingAddress()->getTelephone();
            } elseif ($order->getBillingAddress()) {
                $telephone = $order->getBillingAddress()->getTelephone();
                $countryId = $order->getBillingAddress()->getCountryId();
            }

            $idType = ($country=='CO')?'CC':'CPF';

            $addi->setCustomerData(
                $idType,
                $documentNumber,
                $order->getCustomerFirstname(),
                $order->getCustomerLastname(),
                $order->getCustomerEmail(),
                $this->onlyNumbers($telephone),
                $this->_addiHelper->getPhoneCode($countryId)
            );

            // shipping information
            if (!$order->getIsVirtual()) {
                $addi->setShippingAddress(
                    implode(" ", $order->getShippingAddress()->getStreet()),
                    $order->getShippingAddress()->getCity(),
                    $order->getShippingAddress()->getCountryId()
                );
            } else {
                if ($order->getBillingAddress()) {
                    $addi->setShippingAddress(
                        implode(" ", $order->getBillingAddress()->getStreet()),
                        $order->getBillingAddress()->getCity(),
                        $order->getBillingAddress()->getCountryId()
                    );
                } else {
                    // if dont have both address
                    $addi->setShippingAddress("virtual product without address", "virtual product", $countryId);
                }
            }

            // billing information
            if ($order->getBillingAddress()) {
                $addi->setBillingAddress(
                    implode(" ", $order->getBillingAddress()->getStreet()),
                    $order->getBillingAddress()->getCity(),
                    $order->getBillingAddress()->getCountryId()
                );
            } else {
                $addi->setBillingAddress("virtual product without address", "virtual product", $countryId);
            }

            $this->logger(json_encode($addi->makeJSONArray()));

            $payURL = $addi->getPayURL();

            if (empty($payURL)) {
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

    public function logger($message)
    {
        $this->_addiLogger->info($message);
    }

    public function onlyNumbers($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }
}
