<?php


namespace Addi\Payment\Model;


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

class AddiConfigProvider implements ConfigProviderInterface
{
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
        CheckoutSession $checkoutSession
    ) {
        $this->_escaper = $escaper;
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);
        $this->_assetRepo = $assetRepo;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_urlBuilder = $urlBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $instructions = __(
            "<br>
            <p>Finish your purchase with <b>Addi</b></p>
            <p>Is simple and fast:</p>
            <ul style='list-style-type: square;'>
                <li>No credit card needed and in minutes.</li>
                <li>Pay your first installment 1 month after your purchase.</li>
                <li>100% online. No paperwork or hidden charges.</li>
            </ul>
            <b>You only need your ID and WhatsApp!</b>"
        );

        //price range and discounts
        $quote = $this->_checkoutSession->getQuote();
        $country = $this->getScopeConfig('payment/addi/credentials/country');
        $sandbox = $this->getScopeConfig('payment/addi/credentials/sandbox');

        $request = new AddiPriceDiscount();
        $prices = $request->getPriceAndDiscounts($quote->getGrandTotal(), $country, $sandbox);

        $this->_checkoutSession->setAddiMinAmount($prices->minAmount);
        $this->_checkoutSession->setAddiMaxAmount($prices->maxAmount);
        $this->_checkoutSession->setAddiDiscount(0);

        if (isset($prices->policy->discount)) {
            $this->_checkoutSession->setAddiDiscount($prices->policy->discount*100);
        }

        return array(
            'payment' => array(
                'addi' => array(
                    'image' => $this->getViewFileUrl("Addi_Payment::images/addi-logo.png"),
                    'instructions' => $instructions,
                    'label' => "Paga después con Addi",
                    'country' => $country,
                    'discount' => $this->_checkoutSession->getAddiDiscount(),
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
