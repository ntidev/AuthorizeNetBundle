<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPaymentProfileBillToModel {

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Firstname is required for the billing information.")
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "The Firstname cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("firstName")
     * @JMS\Type("string")
     */
    private $firstName;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Lastname is required for the billing information.")
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "The Lastname cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("lastName")
     * @JMS\Type("string")
     */
    private $lastName;

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "The Company cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("company")
     * @JMS\Type("string")
     */
    private $company;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Phone is required for the billing information.")
     * @Assert\Length(
     *      max = 25,
     *      maxMessage = "The Country cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("phoneNumber")
     * @JMS\Type("string")
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Address is required for the billing information.")
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "The Address cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("address")
     * @JMS\Type("string")
     */
    private $address;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The City is required for the billing information.")
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "The City cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("city")
     * @JMS\Type("string")
     */
    private $city;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The State is required for the billing information.")
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "The State cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("state")
     * @JMS\Type("string")
     */
    private $state;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Zipcode is required for the billing information.")
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "The Zipcode cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("zip")
     * @JMS\Type("string")
     */
    private $zip;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="The Country is required for the billing information.")
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "The Country cannot be longer than {{ limit }} characters"
     * )
     * @JMS\SerializedName("country")
     * @JMS\Type("string")
     */
    private $country;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return CustomerPaymentProfileBillToModel
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return CustomerPaymentProfileBillToModel
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return CustomerPaymentProfileBillToModel
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return CustomerPaymentProfileBillToModel
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return CustomerPaymentProfileBillToModel
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return CustomerPaymentProfileBillToModel
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return CustomerPaymentProfileBillToModel
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     * @return CustomerPaymentProfileBillToModel
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return CustomerPaymentProfileBillToModel
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


}