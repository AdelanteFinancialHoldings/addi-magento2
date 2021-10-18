<?php

namespace Addi;

use Addi\Data\Billing;
use Addi\Data\Item;
use Addi\Data\Client;
use Addi\Data\Amount;
use Addi\Data\Shipping;
use Addi\Data\PickUpAddress;
use Exception;

class Addi
{

    const URL_AUTH_SANDBOX = "https://auth.addi-staging.com/";
    const URL_AUTH_DOMAIN_SANDBOX = "https://api.staging.addi.com";
    const URL_AUTH_PRODUCTION = "https://auth.addi.com/";
    const URL_SANDBOX = "https://api.addi-staging.com/";
    const URL_PRODUCTION = "https://api.addi.com/";
    const GRANT_TYPE = "client_credentials";
    const MAX_NUMBER_RETRIES = 10;
    const ID_TYPE = "CC"; // Cedula de Ciudadania

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $sandbox;

    /**
     * @var string
     */
    private $audience;

    /** @var Amount  */
    private $amount = null;

    /**
     * @var Item[]
     */
    private $items = array();

    /** @var Billing */
    private $billingAddress = null;

    /** @var Shipping */
    private $shippingAddress = null;

    /** @var PickUpAddress */
    private $pickUpAddress = null;

    /** @var Client */
    private $client = null;

    /** @var string */
    private $logoURL;

    /** @var string */
    private $callbackURL;

    /** @var string */
    private $redirectionURL;

    /** @var string */
    private $latitude;

    /** @var string */
    private $longitude;

    public function __construct(
        $orderId,
        $description,
        $clientId,
        $clientSecret,
        $sandbox = false,
        $logoURL,
        $callBackURL,
        $redirectionURL,
        $latitude,
        $longitude
    )
    {
        $this->orderId = $orderId;
        $this->description = $description;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->sandbox = $sandbox;

        $this->logoURL = $logoURL;
        $this->callbackURL = $callBackURL;
        $this->redirectionURL = $redirectionURL;
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        if($this->isSandbox()){
            $this->setAudience(self::URL_AUTH_DOMAIN_SANDBOX);
        }else{
            $this->setAudience(self::URL_PRODUCTION);
        }
    }

    /**
     * @return string
     */
    public function getLogoURL(): string
    {
        return $this->logoURL;
    }

    /**
     * @return string
     */
    public function getCallbackURL(): string
    {
        return $this->callbackURL;
    }

    /**
     * @return string
     */
    public function getRedirectionURL(): string
    {
        return $this->redirectionURL;
    }



    /**
     * @return string
     */
    public function getAudience(): string
    {
        return $this->audience;
    }

    /**
     * @param string $audience
     */
    public function setAudience(string $audience): void
    {
        $this->audience = $audience;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function isSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @return string
     */
    public function getUrlAuth() {
        if ($this->isSandbox()) {
            return self::URL_AUTH_SANDBOX;
        } else {
            return self::URL_AUTH_PRODUCTION;
        }
    }

    /**
     * @return string
     */
    public function getUrl() {
        if ($this->isSandbox()) {
            return self::URL_SANDBOX;
        } else {
            return self::URL_PRODUCTION;
        }
    }


    /**
     * @return array
     */
    public function getAuthRequest()
    {
        $request = [];
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
        $retry = false;
        $timesToRetry = 0;

        do{
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

            $timesToRetry++;
        }while($retry && $timesToRetry <= self::MAX_NUMBER_RETRIES);

        if($retry && $timesToRetry >= self::MAX_NUMBER_RETRIES){
            throw new Exception("The maximum number of authentication attempts has been exceeded");
        }

        $response = json_decode($result,true);

        if($httpcode == 200){
            if(isset($response['access_token'])){
                return $response['access_token'];
            }else{
                throw new Exception("error getting token");
            }
        }elseif($httpcode == 401){
            throw new Exception("credentials error Unauthorized");
        }
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        if ( $this->amount == null ) {
            $this->amount = new Amount();
        }
        return $this->amount;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ( $this->client == null ) {
            $this->client = new Client();
        }
        return $this->client;
    }


    /**
     * @return array
     */
    public function getItems()
    {
        if ( $this->items == null ) {
            $this->items = array();
        }
        return $this->items;
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
        $this->amount = $amount;
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
    public function addNewItem($sku, $name,$quantity,$unitPrice,$tax,$pictureUrl,$category) {
        $item = new Item();

        $item->setSku($sku);
        $item->setName($name);
        $item->setQuantity($quantity);
        $item->setUnitPrice($unitPrice);
        $item->setTax($tax);
        $item->setPictureUrl($pictureUrl);
        $item->setCategory($category);

        $this->items[]  = $item;

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
    public function setCustomerData($idType = self::ID_TYPE,$idNumber,$firstName,$lastName,$email,$cellphone,$cellphoneCountryCode)
    {
        $client = new Client();
        $client->setIdType($idType);
        $client->setIdNumber($idNumber);
        $client->setFirstname($firstName);
        $client->setLastname($lastName);
        $client->setEmail($email);
        $client->setCellphone($cellphone);
        $client->setCellphoneCountryCode($cellphoneCountryCode);

        $this->client = $client;
    }

    /**
     * @return Shipping
     */
    public function getShippingAddress()
    {
        if ( $this->shippingAddress == null ) {
            $this->shippingAddress = new Shipping();
        }
        return $this->shippingAddress;
    }

    /**
     * @return Billing
     */
    public function getBillingAddress()
    {
        if ( $this->billingAddress == null ) {
            $this->billingAddress = new Billing();
        }
        return $this->billingAddress;
    }

    /**
     * @return PickUpAddress
     */
    public function getPickUpAddress()
    {
        if ( $this->pickUpAddress == null ) {
            $this->pickUpAddress = new PickUpAddress();
        }
        return $this->pickUpAddress;
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
    public function setPickUpAddress($street,$city,$country){
        $this->getPickUpAddress()->setLineOne($street);
        $this->getPickUpAddress()->setCity($city);
        $this->getPickUpAddress()->setCountry($country);
        return $this;
    }

    /**
     * @return array
     */
    public function makeJSONArray() {
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

        if($this->getPickUpAddressArray()){
            $json["pickUpAddress"] = $this->getPickUpAddressArray();
        }
        $json["allyUrlRedirection"] = $this->getUrlRedirectionArray();

        return $json;
    }



    /**
     * @return array
     */
    protected function getItemsArray() {
        $items = [];
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
        $retVal = [];
        $retVal["idType"] = self::ID_TYPE;
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
        $retVal = [];
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
        $retVal = [];
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
        $retVal = [];
        $retVal["logoUrl"] = $this->getLogoURL();
        $retVal["callbackUrl"] = $this->getCallbackURL();
        $retVal["redirectionUrl"] = $this->getRedirectionURL();
        return $retVal;
    }



    /**
     * @return array
     */
    public function getPickUpAddressArray()
    {
        if(!$this->pickUpAddress){
            return false;
        }
        $retVal = [];
        $retVal["lineOne"] = $this->getPickUpAddress()->getLineOne();
        $retVal["city"] = $this->getPickUpAddress()->getCity();
        $retVal["country"] = $this->getPickUpAddress()->getCountry();
        return $retVal;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getPayURL(){
        $timesToRetry = 0;
        $retry = false;
        $token = $this->getToken();

        do{
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

            if ($httpcode >=500 && $httpcode <= 524) {
                $retry = true;
            }

            $timesToRetry++;
        }while($retry && $timesToRetry <= self::MAX_NUMBER_RETRIES);

        if($retry && $timesToRetry >= self::MAX_NUMBER_RETRIES){
            throw new Exception("The maximum number attempts has been exceeded");
        }

        if($httpcode == 301){
            $redirectURL = curl_getinfo($ch,CURLINFO_REDIRECT_URL);
            return $redirectURL;
        }elseif($httpcode == 401){
            throw new Exception("credentials error Unauthorized");
        }elseif($httpcode == 400){
            $result = json_decode($result);
            throw new Exception($result->message);
        }elseif($httpcode == 409){
            throw new Exception(__("The Customer already has an Addi Credit. This operation is not supported."));
        }

        curl_close($ch);
    }

}

//$a = new Addi('54654540',
//    'test',
//    '2chvCbxYElHKP0VNuz0Qup2xSMVdsCHy',
//    'Y-OxxcPtKI4auS_kpGAzR4rZnpCOkjl9wXOt-GdBtfv4EhlEYu9zLV1Xff2JsmHL',
//    true,
//    'http://addi.test/logo.png',
//    'http://addi.test/addi/callback/index',
//    'http://addi.test/addi/redirect',
//    '',
//    '');
//
//$a->setAmountDetails(255000.0,50000.0,100000.0,'COP');
//
//$a->addNewItem('MOSOW-545','Camisa manga corta',5,200.50,20,'http://addi.test/imageproducto.png','Camisas');
//$a->addNewItem('MOSOW-800','Camisa manga larga',5,200.50,20,'http://addi.test/imageproducto.png','Camisas');
//$a->setCustomerData('CC','2244551155','DIEGO','VELAZQUEZ','diegovelazquez@wolfsellers.com','5568794075','+52');
//$a->setShippingAddress('rio tiber 39','mexico','MEXICO');
//$a->setBillingAddress('rio tiber 39 FACTURACION','mexico','MEXICO');
//
//echo $a->getToken();
