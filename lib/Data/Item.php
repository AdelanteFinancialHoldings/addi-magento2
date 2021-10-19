<?php

namespace Addi\Payment\lib\Data;

class Item
{
    /**
     * @var string
     */
    protected $_sku="";

    /**
     * @var string
     */
    protected $_name="";

    /**
     * @var string
     */
    protected $_quantity="";

    /**
     * @var float
     */
    protected $_unitPrice=0.00;

    /**
     * @var float
     */
    protected $_tax=0.00;

    /**
     * @var string
     */
    protected $_pictureUrl="";

    /**
     * @var string
     */
    protected $_category="";

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->_sku;
    }

    /**
     * @param string $_sku
     */
    public function setSku(string $_sku): void
    {
        $this->_sku = $_sku;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * @param string $_name
     */
    public function setName(string $_name): void
    {
        $this->_name = $_name;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->_quantity;
    }

    /**
     * @param string $_quantity
     */
    public function setQuantity(string $_quantity): void
    {
        $this->_quantity = $_quantity;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->_unitPrice;
    }

    /**
     * @param float $_unitPrice
     */
    public function setUnitPrice(float $_unitPrice): void
    {
        $this->_unitPrice = $_unitPrice;
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        return $this->_tax;
    }

    /**
     * @param float $_tax
     */
    public function setTax(float $_tax): void
    {
        $this->_tax = $_tax;
    }

    /**
     * @return string
     */
    public function getPictureUrl(): string
    {
        return $this->_pictureUrl;
    }

    /**
     * @param string $_pictureUrl
     */
    public function setPictureUrl(string $_pictureUrl): void
    {
        $this->_pictureUrl = $_pictureUrl;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->_category;
    }

    /**
     * @param string $_category
     */
    public function setCategory(string $_category): void
    {
        $this->_category = $_category;
    }
}
