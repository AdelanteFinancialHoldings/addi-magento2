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

class AddiConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = AddiPayment::PAYMENT_METHOD_ADDI_CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var Repository
     */
    private $assetRepo;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

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
        UrlInterface $urlBuilder
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $instructions = __("<br><p>Finish your purchase with <b>Addi</b></p><p>Is simple and fast:</p><ul style='list-style-type: square;'><li>No credit card needed and in minutes.</li><li>Pay your first installment 1 month after your purchase.</li><li>100% online. No paperwork or hidden charges.</li></ul><b>You only need your ID and WhatsApp!</b>");

        return [
            'payment' => [
                'addi' => [
                    'image' => $this->getViewFileUrl("Addi_Payment::images/addi-logo.png"),
                    'instructions' => $instructions,
                    'label' => "Paga después con Addi",
                ],
            ],
        ];
    }

    /**
     * Get mailing address from config
     *
     * @return string
     */
    protected function getImageUrl()
    {
        return $this->method->getPayableTo();
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    protected function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }
}
