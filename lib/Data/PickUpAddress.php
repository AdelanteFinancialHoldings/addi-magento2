<?php

namespace Addi\Payment\lib\Data;

class PickUpAddress
{
    /** @var string */
    protected $_lineOne;

    /** @var string */
    protected $_city;

    /** @var string */
    protected $_country;

    /**
     * @return string
     */
    public function getLineOne(): string
    {
        return $this->_lineOne;
    }

    /**
     * @param string $_lineOne
     */
    public function setLineOne(string $_lineOne): void
    {
        $this->_lineOne = $_lineOne;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->_city;
    }

    /**
     * @param string $_city
     */
    public function setCity(string $_city): void
    {
        $this->_city = $_city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->_country;
    }

    /**
     * @param string $_country
     */
    public function setCountry(string $_country): void
    {
        $this->_country = $_country;
    }

    /**
     * @param string $lineOne
     * @param string $city
     * @param string $country
     * @return $this
     */
    public function setAddress(string $lineOne,string $city, string $country)
    {
        $this->setCity($city);
        $this->setCountry($country);
        $this->setLineOne($lineOne);
        return $this;
    }

    /**
     * @return array
     */
    public function getAddress()
    {
        return array(
            "lineOne" => $this->getLineOne(),
            "city" => $this->getCity(),
            "country" => $this->getCountry()
        );
    }
}
