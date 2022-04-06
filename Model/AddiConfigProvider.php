<?php


namespace Addi\Payment\Model;


use Addi\Payment\Model\Payment\Addi;
use Addi\Payment\Model\Payment\Addi as AddiPayment;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Payment\Helper\Data as PaymentHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Addi\Payment\lib\AddiPriceDiscount;
use Addi\Payment\Helper\AddiHelper;

class AddiConfigProvider implements ConfigProviderInterface
{
    CONST LABEL_CO = "Paga después con Addi";
    CONST LABEL_BR_TEMPLATE_01 = "PIX parcelado";
    CONST LABEL_BR_TEMPLATE_02 = "PIX à vista ou parcelado";

    CONST LABEL_CO_DISCOUNT = "%1% de descuento";
    CONST LABEL_BR_DISCOUNT = "%1% de desconto";
    /**
     * @var string[]
     */
    protected $_methodCode = AddiPayment::PAYMENT_METHOD_ADDI_CODE;

    /**
     * @var Checkmo
     */
    protected $_method;

    /**
     * @var Escaper
     */
    protected $_escaper;
    /**
     * @var Repository
     */
    protected $_assetRepo;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var AddiHelper
     */
    protected $_addiHelper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param UrlInterface $urlBuilder
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Repository $assetRepo,
        RequestInterface $request,
        LoggerInterface $logger,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        AddiHelper $addiHelper
    ) {
        $this->_escaper = $escaper;
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);
        $this->_assetRepo = $assetRepo;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_urlBuilder = $urlBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_addiHelper = $addiHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        //price range and discounts
        $quote = $this->_checkoutSession->getQuote();
        $country = $this->getScopeConfig('payment/addi/credentials/country');
        $sandbox = $this->getScopeConfig('payment/addi/credentials/sandbox');
        $allySlug = $this->getScopeConfig('payment/addi/credentials/ally_slug');

        $request = new AddiPriceDiscount();
        $prices = $request->getPriceAndDiscounts($quote->getGrandTotal(), $country, $sandbox, $allySlug);

        $this->_checkoutSession->setAddiMinAmount($prices->minAmount);
        $this->_checkoutSession->setAddiMaxAmount($prices->maxAmount);
        $this->_checkoutSession->setAddiDiscount(0);

        if (isset($prices->policy->discount)) {
            $this->_checkoutSession->setAddiDiscount($prices->policy->discount*100);
        }

        $widgetVersion = $prices->widgetConfig->widgetVersion ?? '';

        $instructions = $this->_addiHelper->getCheckoutTemplate($country, $widgetVersion);

        $title = $country == 'CO'?self::LABEL_CO:self::LABEL_BR_TEMPLATE_01;

        if ($country == 'BR') {
            if ($widgetVersion == AddiHelper::WIDGET_VERSION_01) {
                $title = self::LABEL_BR_TEMPLATE_01;
            } elseif ($widgetVersion == AddiHelper::WIDGET_VERSION_02) {
                $title = self::LABEL_BR_TEMPLATE_02;
            }
        }

        $labelDiscount = $country=='CO' ? self::LABEL_CO_DISCOUNT:self::LABEL_BR_DISCOUNT;

        return array(
            'payment' => array(
                'addi' => array(
                    'image' => $this->getViewFileUrl("Addi_Payment::images/addi-logo.png"),
                    'instructions' => $instructions,
                    'label' => $title,
                    'country' => $country,
                    'discount' => $this->_checkoutSession->getAddiDiscount(),
                    'label_discount' => $labelDiscount,
                    'redirect_pay' => $this->_urlBuilder->getUrl('addi/redirect/pay'),
                ),
            ),
        );
    }

    /**
     * Get mailing address from config
     *
     * @return string
     */
    protected function getImageUrl()
    {
        return $this->_method->getPayableTo();
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    protected function getViewFileUrl($fileId, array $params = array())
    {
        try {
            $params = array_merge(array('_secure' => $this->_request->isSecure()), $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_urlBuilder->getUrl('', array('_direct' => 'core/index/notFound'));
        }
    }

    /**
     * @param $configPath
     * @return mixed
     */
    public function getScopeConfig($configPath)
    {
        return $this->_scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }
}
