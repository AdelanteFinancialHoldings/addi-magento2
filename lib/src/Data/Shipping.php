<?php


namespace Addi\Data;


class Shipping
{
    /** @var string */
    private $lineOne;

    /** @var string */
    private $city;

    /** @var string */
    private $country;

    /**
     * @return string
     */
    public function getLineOne(): string
    {
        return $this->lineOne;
    }

    /**
     * @param string $lineOne
     */
    public function setLineOne(string $lineOne): void
    {
        $this->lineOne = $lineOne;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @param string $lineOne
     * @param string $city
     * @param string $country
     * @return $this
     */
    public function setAddress(string $lineOne,string $city, string $country){
        $this->setCity($city);
        $this->setCountry($country);
        $this->setLineOne($lineOne);
        return $this;
    }

    /**
     * @return array
     */
    public function getAddress() {
        return [
            "lineOne" => $this->getLineOne(),
            "city" => $this->getCity(),
            "country" => $this->getCountry()
        ];
    }
}
