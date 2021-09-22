<?php


namespace Addi\Payment\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Response\Http;

class Addi extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Order
     */
    private $order;

    /** @var Http */
    public $_http;

    /**
     * Addi constructor.
     * @param Session $checkoutSession
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Session $checkoutSession,
        Template\Context $context,
        Http $http,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->_http = $http;
    }

    /**
     * @return Order
     */
    public function getOrder() {
        $order = $this->checkoutSession->getLastRealOrder();
        if(!$order || $order->getState() != Order::STATE_PENDING_PAYMENT){
            return false;
        }
        return $order;
    }

    public function redirect($url){
        return $this->_http->setRedirect($url);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getStoreConfig($path) {
        return $this->_scopeConfig->getValue($path);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getStoreConfigFlag($path) {
        return $this->_scopeConfig->isSetFlag($path);
    }
}

