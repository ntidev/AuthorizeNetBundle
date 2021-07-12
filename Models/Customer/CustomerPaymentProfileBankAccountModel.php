<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\AccessType("public_method")
 */
class CustomerPaymentProfileBankAccountModel
{

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The account type is required for the billing information.")
     * @Assert\Choice({"checking", "savings", "businessChecking"})
     * @JMS\SerializedName("accountType")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getAccountType")
     */
    private $accountType;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The routing number is required for the bank account information.")
     * @JMS\SerializedName("routingNumber")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getRoutingNumber")
     */
    private $routingNumber;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The account number is required for the bank account information.")
     * @JMS\SerializedName("accountNumber")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getAccountNumber")
     */
    private $accountNumber;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The name on account is required for the bank account information.")
     * @JMS\SerializedName("nameOnAccount")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getNameOnAccount")
     */
    private $nameOnAccount;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The bank name is required for the bank account information.")
     * @JMS\SerializedName("bankName")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getBankName")
     */
    private $bankName;

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routingNumber;
    }

    /**
     * @param string $routingNumber
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getNameOnAccount()
    {
        return $this->nameOnAccount;
    }

    /**
     * @param string $nameOnAccount
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function setNameOnAccount($nameOnAccount)
    {
        $this->nameOnAccount = $nameOnAccount;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
        return $this;
    }


}
