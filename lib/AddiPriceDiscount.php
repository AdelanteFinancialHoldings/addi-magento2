<?php

namespace Addi\Payment\lib;


class AddiPriceDiscount
{
    //PRICE RANGE AND DISCOUNTS
    const URL_PRICES_AND_DISCOUNTS_CO = "https://channels-public-api.addi.com/allies/%s/config?requestedAmount=%f";
    const URL_PRICES_AND_DISCOUNTS_BR = "https://channels-public-api.addi.com.br/allies/%s/config?requestedAmount=%f";

    const URLSANDBOX_PRICES_AND_DISCOUNTS_CO
        = "https://channels-public-api.addi-staging.com/allies/%s/config?requestedAmount=%f";

    const URLSANDBOX_PRICES_AND_DISCOUNTS_BR
        = "https://channels-public-api.addi-staging-br.com/allies/%s/config?requestedAmount=%f";

    const PRICE_RANGE_MIN_CO = 50000;
    const PRICE_RANGE_MAX_CO = 20000000;

    const PRICE_RANGE_MIN_BR = 50;
    const PRICE_RANGE_MAX_BR = 2000;

    const ALLYSLUG = 'magentoplugin-ecommerce';

    protected $_minAmount;
    protected $_maxAmount;

    /**
     * @return mixed
     */
    public function getMinAmount()
    {
        return $this->_minAmount;
    }

    /**
     * @param mixed $_minAmount
     */
    public function setMinAmount($_minAmount): void
    {
        $this->_minAmount = $_minAmount;
    }

    /**
     * @return mixed
     */
    public function getMaxAmount()
    {
        return $this->_maxAmount;
    }

    /**
     * @param mixed $_maxAmount
     */
    public function setMaxAmount($_maxAmount): void
    {
        $this->_maxAmount = $_maxAmount;
    }

    /**
     * @param $grandTotal
     * @param $country
     * @param $sandbox
     * @param string $allySlug
     * @return mixed
     */
    public function getPriceAndDiscounts($grandTotal,$country,$sandbox,$allySlug=self::ALLYSLUG)
    {
        $ch = curl_init();
        $url = sprintf($this->getPriceDiscountURL($country,$sandbox),$allySlug,$grandTotal);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/json','accept: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $result = curl_exec($ch);
        $result = json_decode($result);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        if ($httpCode === 200) {
            if (isset($result->minAmount) && isset($result->maxAmount)) {
                return $result;
            }
        }

        if ($country == 'CO') {
            $this->setMinAmount(self::PRICE_RANGE_MIN_CO);
            $this->setMaxAmount(self::PRICE_RANGE_MAX_CO);
        } elseif ($country == 'BR') {
            $this->setMinAmount(self::PRICE_RANGE_MIN_BR);
            $this->setMaxAmount(self::PRICE_RANGE_MAX_BR);
        }

        curl_close($ch);

        return json_decode(
            json_encode(
                array('minAmount'=>$this->getMinAmount(), 'maxAmount'=>$this->getMaxAmount())
            )
        );
    }

    /**
     * @return string|false
     */
    public function getPriceDiscountURL($country,$sandbox)
    {
        if ($country == 'CO') {
            if ($sandbox) {
                return self::URLSANDBOX_PRICES_AND_DISCOUNTS_CO;
            } else {
                return self::URL_PRICES_AND_DISCOUNTS_CO;
            }
        } elseif ($country == 'BR') {
            if ($sandbox) {
                return self::URLSANDBOX_PRICES_AND_DISCOUNTS_BR;
            } else {
                return self::URL_PRICES_AND_DISCOUNTS_BR;
            }
        }

        return false;
    }
}
