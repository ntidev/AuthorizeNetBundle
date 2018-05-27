<?php

namespace NTI\AuthorizeNetBundle\Services\Customer;

use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use NTI\AuthorizeNetBundle\Exception\ANetInvalidRequestFormatException;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
use NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileModel;
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
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ANetCustomerPaymentProfileService
 * @package NTI\AuthorizeNetBundle\Services\Customer
 */
class ANetCustomerPaymentProfileService extends ANetRequestService {

    /**
     * ANetCustomerPaymentProfileService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
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
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

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
     * @throws ANetInvalidRequestFormatException
     */
    public function createProfile($customerProfileId, $data) {

        /** @var CustomerPaymentProfileModel $profile */
        $profile = $this->container->get('jms_serializer')->deserialize(json_encode($data), CustomerPaymentProfileModel::class, 'json');
        $validator = $this->container->get('validator');
        $errors = $validator->validate($profile);
        if(count($errors) > 0) {
            throw new ANetInvalidRequestFormatException($errors);
        }

        if(!$profile->getBillTo()) {
            throw new ANetRequestException("The Contact information is required for new Payment Profiles.");
        }

        if(!$profile->getPayment()) {
            throw new ANetRequestException("The Credit Card information is required for new Payment Profiles.");
        }

        // Set credit card information for payment profile
        $creditCard = new CreditCardType();
        $creditCard->setCardNumber($profile->getPayment()->getCreditCard()->getCardNumber());
        $creditCard->setExpirationDate($profile->getPayment()->getCreditCard()->getExpirationDate());
        $creditCard->setCardCode($profile->getPayment()->getCreditCard()->getCode());
        $paymentCreditCard = new PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billTo = new CustomerAddressType();
        $billTo->setFirstName($profile->getBillTo()->getFirstName());
        $billTo->setLastName($profile->getBillTo()->getLastName());
        $billTo->setPhoneNumber($profile->getBillTo()->getPhoneNumber());
        $billTo->setCompany($profile->getBillTo()->getCompany());
        $billTo->setAddress($profile->getBillTo()->getAddress());
        $billTo->setCity($profile->getBillTo()->getCity());
        $billTo->setState($profile->getBillTo()->getState());
        $billTo->setZip($profile->getBillTo()->getZip());
        $billTo->setCountry($profile->getBillTo()->getCountry());

        // Create a new Customer Payment Profile object
        $paymentprofile = new CustomerPaymentProfileType();
        $paymentprofile->setCustomerType('individual');
        $paymentprofile->setBillTo($billTo);
        $paymentprofile->setPayment($paymentCreditCard);
        $paymentprofile->setDefaultPaymentProfile(true);

        // Assemble the complete transaction request
        $request = new CreateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setValidationMode($this->validationMode);

        // Add an existing profile id to the request
        $request->setCustomerProfileId($customerProfileId);
        $request->setPaymentProfile($paymentprofile);
        $request->setValidationMode("testMode");


        /** @var CreateCustomerPaymentProfileController $controller */
        $controller = new CreateCustomerPaymentProfileController($request);

        /** @var CreateCustomerPaymentProfileResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

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
     * @throws ANetInvalidRequestFormatException
     */
    public function updateProfile($customerProfileId, $paymentProfileId, $data) {
        $paymentProfile = $this->getProfile($customerProfileId, $paymentProfileId);

        /** @var CustomerPaymentProfileModel $profile */
        $profile = $this->container->get('jms_serializer')->deserialize(json_encode($data), CustomerPaymentProfileModel::class, 'json');
        $validator = $this->container->get('validator');
        $errors = $validator->validate($profile);
        if(count($errors) > 0) {
            throw new ANetInvalidRequestFormatException($errors);
        }

        //Set profile ids of profile to be updated
        $request = new UpdateCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $request->setValidationMode($this->validationMode);

        // CreditCard information
        // As per the documentation this needs to be sent despite being changed or not
        $creditCard = new CreditCardType();
        if($profile->getPayment()) {
            $creditCard->setCardNumber($profile->getPayment()->getCreditCard()->getCardNumber());
            $creditCard->setExpirationDate($profile->getPayment()->getCreditCard()->getExpirationDate());
            $creditCard->setCardCode($profile->getPayment()->getCreditCard()->getCode());
        } else {
            $creditCard->setCardNumber($paymentProfile->getPayment()->getCreditCard()->getCardNumber());
            $creditCard->setExpirationDate($paymentProfile->getPayment()->getCreditCard()->getExpirationDate());
        }
        $paymentCreditCard = new PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        if($profile->getBillTo()) {
            $billTo = new CustomerAddressType();
            $billTo->setFirstName($profile->getBillTo()->getFirstName());
            $billTo->setLastName($profile->getBillTo()->getLastName());
            $billTo->setPhoneNumber($profile->getBillTo()->getPhoneNumber());
            $billTo->setCompany($profile->getBillTo()->getCompany());
            $billTo->setAddress($profile->getBillTo()->getAddress());
            $billTo->setCity($profile->getBillTo()->getCity());
            $billTo->setState($profile->getBillTo()->getState());
            $billTo->setZip($profile->getBillTo()->getZip());
            $billTo->setCountry($profile->getBillTo()->getCountry());
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
        $request->setValidationMode("testMode");
        $controller = new UpdateCustomerPaymentProfileController($request);

        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }


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
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }


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
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return true;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }
}