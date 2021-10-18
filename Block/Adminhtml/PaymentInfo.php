<?php


namespace Addi\Payment\Block\Adminhtml;


use Addi\Payment\Model\Payment\Addi;
use Magento\Framework\Registry;

class PaymentInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return payment method model
     *
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        $order = $this->_registry->registry('current_order');
        return $order->getPayment();
    }

    /**
     * Return payment method model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        $order = $this->_registry->registry('current_order');
        return $order;
    }

    /**
     * Produce and return the block's HTML output
     *
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->getPayment()->getMethod() === Addi::PAYMENT_METHOD_ADDI_CODE) ? parent::_toHtml() : '';
    }
}
