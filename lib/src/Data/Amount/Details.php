<?php


namespace Kueski\Data\Amount;


class Details
{
    /**
     * @var float
     */
    private $subtotal = 0.00;

    /**
     * @var float
     */
    private $shipping = 0.00;

    /**
     * @var float
     */
    private $handlingFee = 0.00;

    /**
     * @var float
     */
    private $tax = 0.00;

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @param float $subtotal
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;
    }

    /**
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return float
     */
    public function getHandlingFee()
    {
        return $this->handlingFee;
    }

    /**
     * @param float $handlingFee
     */
    public function setHandlingFee($handlingFee)
    {
        $this->handlingFee = $handlingFee;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }
}