<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerProfileModel {

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Account Number is required for the billing information.")
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "The Account Number cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("merchant_account_id")
     * @JMS\Type("string")
     */
    private $merchantAccountId;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Email is required for the billing information.")
     * @Assert\Email(message="The Email address is not valid.")
     * @JMS\SerializedName("email")
     * @JMS\Type("string")
     */
    private $email;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Description is required for the billing information.")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "The Account Number cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("description")
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @var string
     *
     * @JMS\SerializedName("type")
     * @JMS\Type("string")
     */
    private $customerType;

    /**
     * @var array
     *
     * @Assert\Valid()
     * @JMS\SerializedName("payment_profiles")
     * @JMS\Type("array<NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileModel>")
     */
    private $paymentProfiles;

    /**
     * CustomerProfileModel constructor.
     */
    public function __construct() {
        $this->customerType = "individual";
    }


    /**
     * @return string
     */
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @param string $merchantAccountId
     * @return CustomerProfileModel
     */
    public function setMerchantAccountId($merchantAccountId)
    {
        $this->merchantAccountId = $merchantAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return CustomerProfileModel
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return CustomerProfileModel
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customerType;
    }

    /**
     * @param string $customerType
     * @return CustomerProfileModel
     */
    public function setCustomerType($customerType)
    {
        $this->customerType = $customerType;
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentProfiles()
    {
        return $this->paymentProfiles;
    }

    /**
     * @param array $paymentProfiles
     * @return CustomerProfileModel
     */
    public function setPaymentProfiles($paymentProfiles)
    {
        $this->paymentProfiles = $paymentProfiles;
        return $this;
    }

}