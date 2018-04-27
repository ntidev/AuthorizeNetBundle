<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPaymentProfilePaymentModel
{
    /**
     * @var CustomerPaymentProfileCreditCardModel
     *
     * @Assert\Valid()
     * @Assert\NotNull(message="The Credit Card information is required.")
     * @JMS\SerializedName("creditCard")
     * @JMS\Type("NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileCreditCardModel")
     */
    private $creditCard;

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



}