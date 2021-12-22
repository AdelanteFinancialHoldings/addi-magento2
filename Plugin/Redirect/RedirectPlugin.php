<?php

namespace Addi\Payment\Plugin\Redirect;
use Magento\Checkout\Controller\Onepage\Success;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;

class RedirectPlugin
{

    /** @var ResultFactory  */
    protected $_resultFactory;

    /** @var Session */
    protected $_checkoutSession;

    public function __construct(
        ResultFactory $resultFactory,
        Session $checkoutSession
    ) {
        $this->_resultFactory = $resultFactory;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param Success $subject
     * @param $result
     * @return ResultInterface|mixed
     */
    public function afterExecute(Success $subject, $result) // @codingStandardsIgnoreLine
    {

        $order = $this->_checkoutSession->getLastRealOrder();

        if ($order &&
            $order->getAddiUrl() &&
            $order->getStatus() == Order::STATE_PENDING_PAYMENT
        ) {
            /** @var ResultInterface $result */
            $result = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl($order->getAddiUrl());
            return $result;
        }

        return $result;
    }
}
