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
    protected $_checkoutSession;
    /**
     * @var Order
     */
    protected $_order;

    /** @var Http */
    public $http;

    /**
     * Addi constructor.
     * @param Session $checkoutSession
     * @param Template\Context $context
     * @param Http $http
     * @param array $data
     */
    public function __construct(
        Session $checkoutSession,
        Template\Context $context,
        Http $http,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->http = $http;
    }

    /**
     * @return false|Order
     */
    public function getOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        if (!$order || $order->getState() != Order::STATE_PENDING_PAYMENT) {
            return false;
        }

        return $order;
    }

    public function redirect($url)
    {
        return $this->http->setRedirect($url);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getStoreConfig($path)
    {
        return $this->_scopeConfig->getValue($path);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getStoreConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path);
    }
}

