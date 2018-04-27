<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\AccessType("public_method")
 */
class CustomerPaymentProfileCreditCardModel
{

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Credit Card Number is required for the billing information.")
     * @Assert\Length(
     *      max = 16,
     *      maxMessage = "The Credit Card Number cannot be longer than {{ limit }} characters and should only contain numbers"
     * )
     * @JMS\SerializedName("cardNumber")
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getCardNumber")
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Credit Card Expiration Date is required for the billing information.")
     * @JMS\SerializedName("expirationDate")
     * @JMS\Type("string")
     * @Assert\Regex(
     *     pattern="/^([0-9]{4})\-(0[1-9]|1[0-2])$/",
     *     message="The Expiration Date is not valid. Valid format: YYYY-MM"
     * )
     */
    private $expirationDate;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Credit Card CVV/CVC is required for the billing information.")
     * @JMS\SerializedName("code")
     * @JMS\Type("string")
     * @Assert\Regex(
     *     pattern="/\d{4}|\d{3}/",
     *     message="The CVV/CVC cannot be longer than 4 digits"
     * )
     */
    private $code;

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     * @return CustomerPaymentProfileCreditCardModel
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param string $expirationDate
     * @return CustomerPaymentProfileCreditCardModel
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return CustomerPaymentProfileCreditCardModel
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

}