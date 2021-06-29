<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPaymentProfilePaymentModel
{
    /**
     * @var CustomerPaymentProfileCreditCardModel
     *
     * @JMS\SerializedName("creditCard")
     * @JMS\Type("NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileCreditCardModel")
     */
    private $creditCard;

    /**
     * @var CustomerPaymentProfileBankAccountModel
     *
     * @JMS\SerializedName("bankAccount")
     * @JMS\Type("NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileBankAccountModel")
     */
    private $bankAccount;

    /**
     * @return CustomerPaymentProfileCreditCardModel
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param CustomerPaymentProfileCreditCardModel $creditCard
     * @return CustomerPaymentProfilePaymentModel
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }

    /**
     * @return CustomerPaymentProfileBankAccountModel
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param CustomerPaymentProfileBankAccountModel $bankAccount
     * @return CustomerPaymentProfilePaymentModel
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }



}
