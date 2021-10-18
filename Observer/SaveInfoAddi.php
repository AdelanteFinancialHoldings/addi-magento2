<?php
namespace Addi\Payment\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Addi\Payment\Model\Payment\Addi;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Quote\Model\Quote\Payment;
use Psr\Log\LoggerInterface;

class SaveInfoAddi implements ObserverInterface
{
    /**
     * @var InputParamsResolver
     */
    protected $_inputParamsResolver;
    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @var State
     */
    protected $_state;

    /**
     * @var CheckoutSession
     */
    protected $_checkSession;

    public function __construct(
        InputParamsResolver $inputParamsResolver,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        State $state,
        CheckoutSession $checkSession
    ) {
        $this->_inputParamsResolver = $inputParamsResolver;
        $this->_quoteRepository = $quoteRepository;
        $this->_logger = $logger;
        $this->_state = $state;
        $this->_checkSession = $checkSession;
    }
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        $input = $event->getInput();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $event->getPayment()->getQuote();
        $additionalData = (array)$input->getAdditionalData();
        if (isset($additionalData['document_number'])) {
            $paymentQuote = $quote->getPayment();

            $paymentOrder = $observer->getEvent()->getPayment();

            $documentNumber = $this->onlyNumbers($additionalData['document_number']);

            $paymentOrder->setData('document_number', $documentNumber);
            $paymentQuote->setData('document_number', $documentNumber);

            $paymentQuote->save();
            $paymentOrder->save();

            $this->_checkSession->setDocumentNumber($documentNumber);
        }
    }

    public function onlyNumbers($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }
}
