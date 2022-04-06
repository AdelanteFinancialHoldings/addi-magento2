<?php

namespace Addi\Payment\Controller\Redirect;

use Addi\Payment\Logger\Logger as AddiLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Redirect to addi pay.
 */
class Pay extends Action
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var AddiLogger
     */
    private $addiLogger;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param AddiLogger $addiLogger
     */
    public function __construct(Context $context, Session $checkoutSession, AddiLogger $addiLogger)
    {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->addiLogger = $addiLogger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();

        if ($order && $order->getAddiUrl()) {
            /** @var ResultInterface $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl($order->getAddiUrl());
        } else {
            $this->addiLogger->error('Order for pay not found.');
            $this->messageManager->addWarningMessage('Invalid order');

            $result = $this->resultRedirectFactory->create();
            $result->setPath('checkout/cart');
        }

        return $result;
    }
}
