<?php

namespace Addi\Data;

class Amount
{
    /**
     * @var float
     */
    private $totalAmount=0.00;

    /**
     * @var float
     */
    private $shippingAmount=0.00;

    /**
     * @var float
     */
    private $totalTaxesAmount=0.00;

    /**
     * @var string
     */
    private $currency="COP";

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     */
    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return float
     */
    public function getShippingAmount(): float
    {
        return $this->shippingAmount;
    }

    /**
     * @param float $shippingAmount
     */
    public function setShippingAmount(float $shippingAmount): void
    {
        $this->shippingAmount = $shippingAmount;
    }

    /**
     * @return float
     */
    public function getTotalTaxesAmount(): float
    {
        return $this->totalTaxesAmount;
    }

    /**
     * @param float $totalTaxesAmount
     */
    public function setTotalTaxesAmount(float $totalTaxesAmount): void
    {
        $this->totalTaxesAmount = $totalTaxesAmount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }
}
