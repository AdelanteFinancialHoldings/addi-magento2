<?php

namespace Addi\Payment\lib\Data;

class Amount
{
    /**
     * @var float
     */
    protected $_totalAmount=0.00;

    /**
     * @var float
     */
    protected $_shippingAmount=0.00;

    /**
     * @var float
     */
    protected $_totalTaxesAmount=0.00;

    /**
     * @var string
     */
    protected $_currency="COP";

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->_totalAmount;
    }

    /**
     * @param float $_totalAmount
     */
    public function setTotalAmount(float $_totalAmount): void
    {
        $this->_totalAmount = $_totalAmount;
    }

    /**
     * @return float
     */
    public function getShippingAmount(): float
    {
        return $this->_shippingAmount;
    }

    /**
     * @param float $_shippingAmount
     */
    public function setShippingAmount(float $_shippingAmount): void
    {
        $this->_shippingAmount = $_shippingAmount;
    }

    /**
     * @return float
     */
    public function getTotalTaxesAmount(): float
    {
        return $this->_totalTaxesAmount;
    }

    /**
     * @param float $_totalTaxesAmount
     */
    public function setTotalTaxesAmount(float $_totalTaxesAmount): void
    {
        $this->_totalTaxesAmount = $_totalTaxesAmount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->_currency;
    }

    /**
     * @param string $_currency
     */
    public function setCurrency(string $_currency): void
    {
        $this->_currency = $_currency;
    }
}
