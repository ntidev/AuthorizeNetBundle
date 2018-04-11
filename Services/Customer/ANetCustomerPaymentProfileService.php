<?php

namespace NTI\AuthorizeNetBundle\Services\Customer;

use NTI\AuthorizeNetBundle\Exception\Customer\ANetRequestException;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerPaymentProfileExType;
use net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType;
use net\authorize\api\contract\v1\CustomerPaymentProfileType;
use net\authorize\api\contract\v1\DeleteCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\contract\v1\UpdateCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\ValidateCustomerPaymentProfileRequest;
use net\authorize\api\controller\CreateCustomerPaymentProfileController;
use net\authorize\api\controller\DeleteCustomerPaymentProfileController;
use net\authorize\api\controller\GetCustomerPaymentProfileController;
use net\authorize\api\controller\GetCustomerProfileController;
use net\authorize\api\controller\UpdateCustomerPaymentProfileController;
use net\authorize\api\controller\ValidateCustomerPaymentProfileController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ANetCustomerPaymentProfileService
 * @package NTI\AuthorizeNetBundle\Services\Customer
 */
class ANetCustomerPaymentProfileService extends ANetRequestService {

    /**
     * ANetCustomerPaymentProfileService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Get a Customer Payment Profile
     *
     * @param $customerProfileId
     * @param $paymentProfileId
     * @return CustomerPaymentProfileMaskedType
     * @throws ANetRequestException
     */
    public function getProfile($customerProfileId, $paymentProfileId) {

        //request requires customerProfileId and customerPaymentProfileId
        $request = new GetCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($paymentProfileId);
        $request->setUnmaskExpirationDate(true);
        $controller = new GetCustomerPaymentProfileController($request);

        /** @var GetCustomerPaymentProfileResponse $response */
        $response = $controller->executeWithApiResponse($this->endpoint);
        if(($response != null)){
            return $response->getPaymentProfile();
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Creates a new Payment Profile for a Customer
     *
     * @param $customerProfileId
     * @param $data
     * @return string
     * @throws ANetRequestException
     */
    public function createProfile($customerProfileId, $data) {

        $company = $data["company"];
        $address = $data["address"];
        $city = $data["city"];
        $state = $data["state"];
        $zip = $data["zip"];
        $country = $data["country"] ?? "USA";
        $firstname = $data["firstName"];
        $lastname = $data["lastName"];
        $email = $data["email"];
        $phoneNumber = $data["phoneNumber"];
        $faxNumber = $data["fax"] ?? "";
        $ccNumber = $data["cardNumber"];
        $ccExpiration = $data["expirationDate"];
        $ccCode = $data["code"];

        // Set credit card information for payment profile
        $creditCard = new CreditCardType();
        $creditCard->setCardNumber($ccNumber);
        $creditCard->setExpirationDate($ccExpiration);
        $creditCard->setCardCode($ccCode);
        $paymentCreditCard = new PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billTo = new CustomerAddressType();
        $billTo->setFirstName($firstname);
        $billTo->setLastName($lastname);
        $billTo->setEmail($email);
        $billTo->setCompany($company);
        $billTo->setAddress($address);
        $billTo->setCity($city);
        $billTo->setState($state);
        $billTo->setZip($zip);
        $billTo->setCountry($country);
        $billTo->setPhoneNumber($phoneNumber);
        $billTo->setfaxNumber($faxNumber);

        // Create a new Customer Payment Profile object
        $paymentprofile = new CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setBillTo($billTo);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);

        // Assemble the complete transaction request
        $request = new CreateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);

        // Add an existing profile id to the request
        $request->setCustomerProfileId($customerProfileId);
        $request->setPaymentProfile($paymentprofile);


        /** @var CreateCustomerPaymentProfileController $controller */
        $controller = new CreateCustomerPaymentProfileController($request);

        /** @var CreateCustomerPaymentProfileResponse $response */
        $response = $controller->executeWithApiResponse($this->endpoint);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return $response->getCustomerPaymentProfileId();
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Update a Customer Payment Profile
     *
     * @param $customerProfileId
     * @param $paymentProfileId
     * @param $data
     * @return CustomerPaymentProfileExType
     * @throws ANetRequestException
     */
    public function updateProfile($customerProfileId, $paymentProfileId, $data) {
        $paymentProfile = $this->getProfile($customerProfileId, $paymentProfileId);

        //Set profile ids of profile to be updated
        $request = new UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);

        // CreditCard information
        // As per the documentation this needs to be sent despite being changed or not
        $creditCard = new CreditCardType();
        $payment = isset($data["payment"]) ? $data["payment"] : array();
        if(isset($payment["creditCard"]) &&
            isset($payment["creditCard"]["cardNumber"]) &&
            isset($payment["creditCard"]["expirationDate"]) &&
            isset($payment["creditCard"]["code"])) {
            $ccNumber = $payment["creditCard"]["cardNumber"];
            $ccExpiration = $payment["creditCard"]["expirationDate"];
            $ccCode = $payment["creditCard"]["code"];
            $creditCard->setCardNumber($ccNumber);
            $creditCard->setExpirationDate($ccExpiration);
            $creditCard->setCardCode($ccCode);
        } else {
            $creditCard->setCardNumber($paymentProfile->getPayment()->getCreditCard()->getCardNumber());
            $creditCard->setExpirationDate($paymentProfile->getPayment()->getCreditCard()->getExpirationDate());
        }
        $paymentCreditCard = new PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        if(isset($data["billTo"])) {
            $company = $data["billTo"]["company"] ?? "";
            $address = $data["billTo"]["address"] ?? "";
            $city = $data["billTo"]["city"] ?? "";
            $state = $data["billTo"]["state"] ?? "";
            $zip = $data["billTo"]["zip"] ?? "";
            $country = $data["billTo"]["country"] ?? "USA";
            $firstname = $data["billTo"]["firstName"] ?? "";
            $lastname = $data["billTo"]["lastName"] ?? "";
            $email = $data["billTo"]["email"] ?? "";
            $phoneNumber = $data["billTo"]["phoneNumber"] ?? "";
            $faxNumber = $data["billTo"]["fax"] ?? "";

            // Create the Bill To info for new payment type
            $billTo = new CustomerAddressType();
            $billTo->setEmail($email);
            $billTo->setFirstName($firstname);
            $billTo->setLastName($lastname);
            $billTo->setCompany($company);
            $billTo->setAddress($address);
            $billTo->setCity($city);
            $billTo->setState($state);
            $billTo->setZip($zip);
            $billTo->setCountry($country);
            $billTo->setPhoneNumber($phoneNumber);
            $billTo->setfaxNumber($faxNumber);
        } else {
            $billTo = $paymentProfile->getBillTo();
        }

        // Create the Customer Payment Profile object
        $profile = new CustomerPaymentProfileExType();
        $profile->setCustomerPaymentProfileId($paymentProfileId);
        $profile->setBillTo($billTo);
        $profile->setPayment($paymentCreditCard);

        // Submit a UpdatePaymentProfileRequest
        $request->setPaymentProfile($profile);
        $controller = new UpdateCustomerPaymentProfileController($request);

        $response = $controller->executeWithApiResponse($this->endpoint);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return $profile;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Validates a Customer Payment Profile
     *
     * @param $customerProfileId
     * @param $paymentProfileId
     * @param string $validationMode
     * @return bool
     * @throws ANetRequestException
     */
    public function validateProfile($customerProfileId, $paymentProfileId, $validationMode = "testMode") {

        // Use an existing payment profile ID for this Merchant name and Transaction key
        //validationmode tests , does not send an email receipt
        $validationmode = "testMode";
        $request = new ValidateCustomerPaymentProfileRequest();

        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($paymentProfileId);
        $request->setValidationMode($validationmode);

        $controller = new ValidateCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse($this->endpoint);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return true;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Deletes a Customer Payment Profile
     *
     * @param $customerProfileId
     * @param $paymentProfileId
     * @return bool
     * @throws ANetRequestException
     */
    public function deleteProfile($customerProfileId, $paymentProfileId) {
        $request = new DeleteCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $request->setCustomerPaymentProfileId($paymentProfileId);
        $controller = new DeleteCustomerPaymentProfileController($request);
        $response = $controller->executeWithApiResponse($this->endpoint);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return true;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }
}