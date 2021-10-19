<?php

namespace Addi\Payment\lib\Data;

class Client
{
    /**
     * @var string
     */
    protected $_idType;

    /**
     * @var string
     */
    protected $_idNumber;

    /**
     * @var string
     */
    protected $_firstname;

    /**
     * @var string
     */
    protected $_lastname;

    /**
     * @var string
     */
    protected $_email;

    /**
     * @var string
     */
    protected $_cellphone;

    /**
     * @var string
     */
    protected $_cellphoneCountryCode;

    /**
     * @return string
     */
    public function getIdType(): string
    {
        return $this->_idType;
    }

    /**
     * @param string $_idType
     */
    public function setIdType(string $_idType): void
    {
        $this->_idType = $_idType;
    }

    /**
     * @return string
     */
    public function getIdNumber(): string
    {
        return $this->_idNumber;
    }

    /**
     * @param string $_idNumber
     */
    public function setIdNumber(string $_idNumber): void
    {
        $this->_idNumber = $_idNumber;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->_firstname;
    }

    /**
     * @param string $_firstname
     */
    public function setFirstname(string $_firstname): void
    {
        $this->_firstname = $_firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->_lastname;
    }

    /**
     * @param string $_lastname
     */
    public function setLastname(string $_lastname): void
    {
        $this->_lastname = $_lastname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->_email;
    }

    /**
     * @param string $_email
     */
    public function setEmail(string $_email): void
    {
        $this->_email = $_email;
    }

    /**
     * @return string
     */
    public function getCellphone(): string
    {
        return $this->_cellphone;
    }

    /**
     * @param string $_cellphone
     */
    public function setCellphone(string $_cellphone): void
    {
        $this->_cellphone = $_cellphone;
    }

    /**
     * @return string
     */
    public function getCellphoneCountryCode(): string
    {
        return $this->_cellphoneCountryCode;
    }

    /**
     * @param string $_cellphoneCountryCode
     */
    public function setCellphoneCountryCode(string $_cellphoneCountryCode): void
    {
        $this->_cellphoneCountryCode = $_cellphoneCountryCode;
    }

}
