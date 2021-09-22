<?php

namespace Addi\Data;

class Client
{
    /**
     * @var string
     */
    private $idType;

    /**
     * @var string
     */
    private $idNumber;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $cellphone;

    /**
     * @var string
     */
    private $cellphoneCountryCode;

    /**
     * @return string
     */
    public function getIdType(): string
    {
        return $this->idType;
    }

    /**
     * @param string $idType
     */
    public function setIdType(string $idType): void
    {
        $this->idType = $idType;
    }

    /**
     * @return string
     */
    public function getIdNumber(): string
    {
        return $this->idNumber;
    }

    /**
     * @param string $idNumber
     */
    public function setIdNumber(string $idNumber): void
    {
        $this->idNumber = $idNumber;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getCellphone(): string
    {
        return $this->cellphone;
    }

    /**
     * @param string $cellphone
     */
    public function setCellphone(string $cellphone): void
    {
        $this->cellphone = $cellphone;
    }

    /**
     * @return string
     */
    public function getCellphoneCountryCode(): string
    {
        return $this->cellphoneCountryCode;
    }

    /**
     * @param string $cellphoneCountryCode
     */
    public function setCellphoneCountryCode(string $cellphoneCountryCode): void
    {
        $this->cellphoneCountryCode = $cellphoneCountryCode;
    }

}
