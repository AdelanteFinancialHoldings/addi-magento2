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

class SaveInfoAddi implements ObserverInterface {
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
    protected $logger;
    /**
     * @var State
     */
    protected $_state;

    /**
     * @var CheckoutSession
     */
    protected $checkSession;

    public function __construct(
        InputParamsResolver $inputParamsResolver,
        QuoteRepository $quoteRepository,
        LoggerInterface $logger,
        State $state,
        CheckoutSession $checkSession
    ) {
        $this->_inputParamsResolver = $inputParamsResolver;
        $this->_quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->_state = $state;
        $this->checkSession = $checkSession;
    }
    public function execute(EventObserver $observer) {
        $event = $observer->getEvent();
        $input = $event->getInput();
        /* @var $quote \Magento\Quote\Model\Quote */
        $quote = $event->getPayment()->getQuote();
        $additionalData = (array)$input->getAdditionalData();
        if (isset($additionalData['document_number'])) {
            $paymentQuote = $quote->getPayment();

            $paymentOrder = $observer->getEvent()->getPayment();
            $paymentOrder->setData('document_number', $additionalData['document_number']);
            $paymentQuote->setData('document_number', $additionalData['document_number']);

            $paymentQuote->save();
            $paymentOrder->save();
            $this->checkSession->setDocumentNumber($additionalData['document_number']);
        }
    }
}
