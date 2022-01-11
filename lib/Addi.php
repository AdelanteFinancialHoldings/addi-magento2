<?php

namespace Addi\Payment\lib;

use Addi\Payment\lib\Data\Billing;
use Addi\Payment\lib\Data\Item;
use Addi\Payment\lib\Data\Client;
use Addi\Payment\lib\Data\Amount;
use Addi\Payment\lib\Data\Shipping;
use Addi\Payment\lib\Data\PickUpAddress;
use Exception;

class Addi
{
    //COLOMBIA
    const URL_AUTH_PRODUCTION_CO = "https://auth.addi.com/";
    const URL_PRODUCTION_CO = "https://api.addi.com/";
    const URL_AUTH_SANDBOX_CO = "https://auth.addi-staging.com/";
    const URL_AUTH_DOMAIN_SANDBOX_CO = "https://api.staging.addi.com";
    const URL_SANDBOX_CO = "https://api.addi-staging.com/";

    //BRAZIL
    const URL_PRODUCTION_BR = "https://api.addi.com.br/";
    const URL_AUTH_PRODUCTION_BR = "https://auth.addi.com.br/";
    const URL_AUTH_SANDBOX_BR = "https://auth.addi-staging-br.com/";
    const URL_AUTH_DOMAIN_SANDBOX_BR = "https://api.addi.com.br";
    const URL_SANDBOX_BR = "https://api.addi-staging-br.com/";


    const GRANT_TYPE = "client_credentials";
    const MAX_NUMBER_RETRIES = 10;

    /**
     * @var string
     */
    protected $_orderId;

    /**
     * @var string
     */
    protected $_description;

    /**
     * @var string
     */
    protected $_clientId;

    /**
     * @var string
     */
    protected $_clientSecret;

    /**
     * @var string
     */
    protected $_sandbox;

    /**
     * @var string
     */
    protected $_country;

    /**
     * @var string
     */
    protected $_audience;

    /** @var Amount  */
    protected $_amount = null;

    /**
     * @var Item[]
     */
    protected $_items = array();

    /** @var Billing */
    protected $_billingAddress = null;

    /** @var Shipping */
    protected $_shippingAddress = null;

    /** @var PickUpAddress */
    protected $_pickUpAddress = null;

    /** @var Client */
    protected $_client = null;

    /** @var string */
    protected $_logoURL;

    /** @var string */
    protected $_callbackURL;

    /** @var string */
    protected $_redirectionURL;

    /** @var string */
    protected $_latitude;

    /** @var string */
    protected $_longitude;

    /** @var string */
    protected $_destinationLogFile;

    /** @var bool */
    protected $_debugMode=false;

    /** @var array */
    protected $_debugLog;

    public function __construct(
        $orderId,
        $description,
        $clientId,
        $clientSecret,
        $country,
        $sandbox = false,
        $logoURL,
        $callBackURL,
        $redirectionURL,
        $latitude,
        $longitude
    ) {
        $this->_orderId = $orderId;
        $this->_description = $description;
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_sandbox = $sandbox;
        $this->_country = $country;

        $this->_logoURL = $logoURL;
        $this->_callbackURL = $callBackURL;
        $this->_redirectionURL = $redirectionURL;
        $this->_latitude = $latitude;
        $this->_longitude = $longitude;

        if ($this->isSandbox()) {
            if ($this->getCountry() == 'CO') {
                $this->setAudience(self::URL_AUTH_DOMAIN_SANDBOX_CO);
            } elseif ($this->getCountry() == 'BR') {
                $this->setAudience(self::URL_AUTH_DOMAIN_SANDBOX_BR);
            }
        } else {
            if ($this->getCountry() == 'CO') {
                $this->setAudience(trim(self::URL_PRODUCTION_CO, "/"));
            } elseif ($this->getCountry() == 'BR') {
                $this->setAudience(trim(self::URL_PRODUCTION_BR, "/"));
            }
        }
    }

    /**
     * @return string
     */
    public function getLogoURL(): string
    {
        return $this->_logoURL;
    }

    /**
     * @return string
     */
    public function getCallbackURL(): string
    {
        return $this->_callbackURL;
    }

    /**
     * @return string
     */
    public function getRedirectionURL(): string
    {
        return $this->_redirectionURL;
    }

    /**
     * @return string
     */
    public function getAudience(): string
    {
        return $this->_audience;
    }

    /**
     * @param string $_audience
     */
    public function setAudience(string $_audience): void
    {
        $this->_audience = $_audience;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->_orderId;
    }

    /**
     * @param string $_orderId
     */
    public function setOrderId(string $_orderId): void
    {
        $this->_orderId = $_orderId;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->_description;
    }

    /**
     * @param string $_description
     */
    public function setDescription(string $_description): void
    {
        $this->_description = $_description;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->_clientId;
    }

    /**
     * @param string $_clientId
     */
    public function setClientId(string $_clientId): void
    {
        $this->_clientId = $_clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->_clientSecret;
    }

    /**
     * @param string $_clientSecret
     */
    public function setClientSecret(string $_clientSecret): void
    {
        $this->_clientSecret = $_clientSecret;
    }

    /**
     * @return string
     */
    public function isSandbox()
    {
        return $this->_sandbox;
    }

    /**
     * @return string
     */
    public function getUrlAuth()
    {
        if ($this->isSandbox()) {
            if ($this->getCountry() == 'CO') {
                return self::URL_AUTH_SANDBOX_CO;
            } elseif ($this->getCountry() == 'BR') {
                return self::URL_AUTH_SANDBOX_BR;
            }
        } else {
            if ($this->getCountry() == 'CO') {
                return self::URL_AUTH_PRODUCTION_CO;
            } elseif ($this->getCountry() == 'BR') {
                return self::URL_AUTH_PRODUCTION_BR;
            }
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->isSandbox()) {
            if ($this->getCountry() == 'CO') {
                return self::URL_SANDBOX_CO;
            } elseif ($this->getCountry() == 'BR') {
                return self::URL_SANDBOX_BR;
            }
        } else {
            if ($this->getCountry() == 'CO') {
                return self::URL_PRODUCTION_CO;
            } elseif ($this->getCountry() == 'CO') {
                return self::URL_PRODUCTION_BR;
            }
        }
    }


    /**
     * @return array
     */
    public function getAuthRequest()
    {
        $request = array();
        $request["audience"]        = $this->getAudience();
        $request["grant_type"]      = self::GRANT_TYPE;
        $request["client_id"]       = $this->getClientId();
        $request["client_secret"]   = $this->getClientSecret();

        return $request;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getToken()
    {
        try{
            $retry = false;
            $timesToRetry = 0;

            do {
                $ch = curl_init();
                $url = $this->getUrlAuth() . "oauth/token";
                $request = json_encode($this->getAuthRequest());

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/json','accept: application/json'));
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

                curl_close($ch);

                if ($httpcode >=500 && $httpcode <= 524) {
                    $retry = true;
                }

                $this->setDebugLog(
                    'REQUEST: endpoint: '.$url.
                    ' request body: '.$request.
                    ' RESPONSE: '.$result.
                    ' HTTP CODE: '.$httpcode."\n"
                );

                $timesToRetry++;
            } while ($retry && $timesToRetry <= self::MAX_NUMBER_RETRIES);

            if ($retry && $timesToRetry >= self::MAX_NUMBER_RETRIES) {
                throw new Exception("The maximum number of authentication attempts has been exceeded");
            }

            $response = json_decode($result,true);

            if ($httpcode == 200) {
                if (isset($response['access_token'])) {
                    return $response['access_token'];
                } else {
                    throw new Exception("error getting token");
                }
            } elseif ($httpcode == 401) {
                throw new Exception("credentials error Unauthorized");
            }
        } catch (\Throwable $error){
            $this->setDebugLog("ERROR getToken method: ".$error->getMessage());
        }
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        if ($this->_amount == null) {
            $this->_amount = new Amount();
        }

        return $this->_amount;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->_client == null) {
            $this->_client = new Client();
        }

        return $this->_client;
    }


    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->_items == null) {
            $this->_items = array();
        }

        return $this->_items;
    }

    /**
     * @param $totalAmount
     * @param float $shippingAmount
     * @param float $totalTaxesAmount
     * @param string $currency
     * @return $this
     */
    public function setAmountDetails($totalAmount, $shippingAmount = 0.00, $totalTaxesAmount = 0.00, $currency = "COP")
    {
        $amount = $this->getAmount();
        $amount->setCurrency($currency);
        $amount->setTotalAmount($totalAmount);
        $amount->setShippingAmount($shippingAmount);
        $amount->setTotalTaxesAmount($totalTaxesAmount);
        $this->_amount = $amount;
        return $this;
    }

    /**
     * @param $sku
     * @param $name
     * @param $quantity
     * @param $unitPrice
     * @param $tax
     * @param $pictureUrl
     * @param $category
     * @return $thissss
     */
    public function addNewItem($sku, $name,$quantity,$unitPrice,$tax,$pictureUrl,$category)
    {
        $item = new Item();

        $item->setSku($sku);
        $item->setName($name);
        $item->setQuantity($quantity);
        $item->setUnitPrice($unitPrice);
        $item->setTax($tax);
        $item->setPictureUrl($pictureUrl);
        $item->setCategory($category);

        $this->_items[]  = $item;

        return $this;
    }

    /**
     * @param string $idType
     * @param $idNumber
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $cellphone
     * @param $cellphoneCountryCode
     */
    public function setCustomerData($idType,$idNumber,$firstName,$lastName,$email,$cellphone,$cellphoneCountryCode)
    {
        $client = new Client();
        $client->setIdType($idType);
        $client->setIdNumber($idNumber);
        $client->setFirstname($firstName);
        $client->setLastname($lastName);
        $client->setEmail($email);
        $client->setCellphone($cellphone);
        $client->setCellphoneCountryCode($cellphoneCountryCode);

        $this->_client = $client;
    }

    /**
     * @return Shipping
     */
    public function getShippingAddress()
    {
        if ($this->_shippingAddress == null) {
            $this->_shippingAddress = new Shipping();
        }

        return $this->_shippingAddress;
    }

    /**
     * @return Billing
     */
    public function getBillingAddress()
    {
        if ($this->_billingAddress == null) {
            $this->_billingAddress = new Billing();
        }

        return $this->_billingAddress;
    }

    /**
     * @return PickUpAddress
     */
    public function getPickUpAddress()
    {
        if ($this->_pickUpAddress == null) {
            $this->_pickUpAddress = new PickUpAddress();
        }

        return $this->_pickUpAddress;
    }

    /**
     * @param $street
     * @param $city
     * @param $country
     * @return Addi
     */
    public function setShippingAddress($street,$city,$country)
    {
        $this->getShippingAddress()->setLineOne($street);
        $this->getShippingAddress()->setCity($city);
        $this->getShippingAddress()->setCountry($country);
        return $this;
    }

    /**
     * @param $street
     * @param $city
     * @param $country
     * @return Addi
     */
    public function setBillingAddress($street,$city,$country)
    {
        $this->getBillingAddress()->setLineOne($street);
        $this->getBillingAddress()->setCity($city);
        $this->getBillingAddress()->setCountry($country);
        return $this;
    }

    /**
     * @param $street
     * @param $city
     * @param $country
     * @return Addi
     */
    public function setPickUpAddress($street,$city,$country)
    {
        $this->getPickUpAddress()->setLineOne($street);
        $this->getPickUpAddress()->setCity($city);
        $this->getPickUpAddress()->setCountry($country);
        return $this;
    }

    /**
     * @return array
     */
    public function makeJSONArray()
    {
        $json = array();
        $json["description"] = $this->getDescription();
        $json["orderId"] = $this->getOrderId();
        $json["totalAmount"] = $this->getAmount()->getTotalAmount();
        $json["shippingAmount"] = $this->getAmount()->getShippingAmount();
        $json["totalTaxesAmount"] = $this->getAmount()->getTotalTaxesAmount();
        $json["currency"] = $this->getAmount()->getCurrency();
        $json["items"] = $this->getItemsArray();
        $json["client"] = $this->getClientArray();
        $json["shippingAddress"] = $this->getShippingAddressArray();
        $json["billingAddress"] = $this->getBillingAddressArray();

        if ($this->getPickUpAddressArray()) {
            $json["pickUpAddress"] = $this->getPickUpAddressArray();
        }

        $json["allyUrlRedirection"] = $this->getUrlRedirectionArray();

        return $json;
    }

    /**
     * @return array
     */
    protected function getItemsArray()
    {
        $items = array();
        $count = 0;
        /** @var Item $item */
        foreach ($this->getItems() as $item) {
            $items[$count]["sku"] = $item->getSku();
            $items[$count]["name"] = $item->getName();
            $items[$count]["quantity"] = $item->getQuantity();
            $items[$count]["unitPrice"] = $item->getUnitPrice();
            $items[$count]["tax"] = $item->getTax();
            $items[$count]["pictureUrl"] = $item->getPictureUrl();
            $items[$count]["category"] = $item->getCategory();
            $count++;
        }

        return $items;
    }

    /**
     * @return array
     */
    public function getClientArray()
    {
        $retVal = array();
        $retVal["idType"] = $this->getClient()->getIdType();
        $retVal["idNumber"] = $this->getClient()->getIdNumber();
        $retVal["firstName"] = $this->getClient()->getFirstname();
        $retVal["lastName"] = $this->getClient()->getLastname();
        $retVal["email"] = $this->getClient()->getEmail();
        $retVal["cellphone"] = $this->getClient()->getCellphone();
        $retVal["cellphoneCountryCode"] = $this->getClient()->getCellphoneCountryCode();
        $retVal["address"] = $this->getShippingAddressArray();
        return $retVal;
    }

    /**
     * @return array
     */
    public function getShippingAddressArray()
    {
        $retVal = array();
        $retVal["lineOne"] = $this->getShippingAddress()->getLineOne();
        $retVal["city"] = $this->getShippingAddress()->getCity();
        $retVal["country"] = $this->getShippingAddress()->getCountry();
        return $retVal;
    }

    /**
     * @return array
     */
    public function getBillingAddressArray()
    {
        $retVal = array();
        $retVal["lineOne"] = $this->getBillingAddress()->getLineOne();
        $retVal["city"] = $this->getBillingAddress()->getCity();
        $retVal["country"] = $this->getBillingAddress()->getCountry();
        return $retVal;
    }

    /**
     * @return array
     */
    public function getUrlRedirectionArray()
    {
        $retVal = array();
        $retVal["logoUrl"] = $this->getLogoURL();
        $retVal["callbackUrl"] = $this->getCallbackURL();
        $retVal["redirectionUrl"] = $this->getRedirectionURL();
        return $retVal;
    }

    /**
     * @return array|false
     */
    public function getPickUpAddressArray()
    {
        if (!$this->_pickUpAddress) {
            return false;
        }

        $retVal = array();
        $retVal["lineOne"] = $this->getPickUpAddress()->getLineOne();
        $retVal["city"] = $this->getPickUpAddress()->getCity();
        $retVal["country"] = $this->getPickUpAddress()->getCountry();
        return $retVal;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getPayURL()
    {
        try{
            $timesToRetry = 0;
            $retry = false;
            $token = $this->getToken();

            do {
                $ch = curl_init();
                $url = $this->getUrl() . "v1/online-applications";
                $request = json_encode($this->makeJSONArray());

                curl_setopt($ch, CURLOPT_URL, $url);
                //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: application/json',
                                                                  'authorization: bearer '.$token));

                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $this->setDebugLog(
                    'REQUEST: endpoint: '.$url.
                    ' REQUEST BODY: '.$request.
                    ' RESPONSE: '.curl_getinfo($ch,CURLINFO_REDIRECT_URL).
                    ' HTTP CODE: '.$httpcode."\n"
                );

                if ($httpcode >=500 && $httpcode <= 524) {
                    $retry = true;
                }

                $timesToRetry++;
            } while($retry && $timesToRetry <= self::MAX_NUMBER_RETRIES);

            if ($retry && $timesToRetry >= self::MAX_NUMBER_RETRIES) {
                throw new Exception("The maximum number attempts has been exceeded");
            }

            if ($httpcode == 301) {
                $redirectURL = curl_getinfo($ch,CURLINFO_REDIRECT_URL);
                return $redirectURL;
            } elseif ($httpcode == 401) {
                throw new Exception("credentials error Unauthorized");
            } elseif ($httpcode == 400) {
                $result = json_decode($result);
                throw new Exception($result->message);
            } elseif ($httpcode == 409) {
                throw new Exception(__("The Customer already has an Addi Credit. This operation is not supported."));
            }

            curl_close($ch);
        } catch (\Throwable $error){
            $this->setDebugLog("ERROR getPayURL method: ".$error->getMessage());
        }
    }

    /**
     * @return false|string
     */
    public function getDestinationLogFile()
    {
        return ($this->_destinationLogFile)?$this->_destinationLogFile:false;
    }

    public function setDestinationLogFile($file)
    {
        $this->_destinationLogFile = $file;
    }

    /**
     * @param $active
     */
    public function setDebugMode($active)
    {
        $this->_debugMode = $active;
    }

    /**
     * @return bool
     */
    public function isActiveDebugMode()
    {
        return $this->_debugMode;
    }

    /**
     * @param $error
     */
    public function setDebugLog($error)
    {
        if ($this->isActiveDebugMode()) {
            $this->_debugLog[] = $error;
        }
    }

    public function getDebugLog(){
        return $this->_debugLog;

    }

}
