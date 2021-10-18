<?php


namespace Addi\Payment\lib\Data\Amount;


class Details
{
    /**
     * @var float
     */
    protected $_subtotal = 0.00;

    /**
     * @var float
     */
    protected $_shipping = 0.00;

    /**
     * @var float
     */
    protected $_handlingFee = 0.00;

    /**
     * @var float
     */
    protected $_tax = 0.00;

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->_subtotal;
    }

    /**
     * @param float $_subtotal
     */
    public function setSubtotal($_subtotal)
    {
        $this->_subtotal = $_subtotal;
    }

    /**
     * @return float
     */
    public function getShipping()
    {
        return $this->_shipping;
    }

    /**
     * @param float $_shipping
     */
    public function setShipping($_shipping)
    {
        $this->_shipping = $_shipping;
    }

    /**
     * @return float
     */
    public function getHandlingFee()
    {
        return $this->_handlingFee;
    }

    /**
     * @param float $_handlingFee
     */
    public function setHandlingFee($_handlingFee)
    {
        $this->_handlingFee = $_handlingFee;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->_tax;
    }

    /**
     * @param float $_tax
     */
    public function setTax($_tax)
    {
        $this->_tax = $_tax;
    }
}
