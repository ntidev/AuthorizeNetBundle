<?php

namespace NTI\AuthorizeNetBundle\Services\Customer;

use net\authorize\api\contract\v1\CreateCustomerProfileRequest;
use net\authorize\api\contract\v1\CreateCustomerProfileResponse;
use net\authorize\api\contract\v1\CreditCardType;
use net\authorize\api\contract\v1\CustomerAddressType;
use net\authorize\api\contract\v1\CustomerPaymentProfileType;
use net\authorize\api\contract\v1\CustomerProfileExType;
use net\authorize\api\contract\v1\CustomerProfileType;
use net\authorize\api\contract\v1\DeleteCustomerProfileRequest;
use net\authorize\api\contract\v1\GetCustomerProfileIdsRequest;
use net\authorize\api\contract\v1\GetCustomerProfileIdsResponse;
use net\authorize\api\contract\v1\GetCustomerProfileRequest;
use net\authorize\api\contract\v1\GetCustomerProfileResponse;
use net\authorize\api\contract\v1\PaymentType;
use net\authorize\api\contract\v1\UpdateCustomerProfileRequest;
use net\authorize\api\contract\v1\UpdateCustomerProfileResponse;
use net\authorize\api\controller\CreateCustomerProfileController;
use net\authorize\api\controller\DeleteCustomerProfileController;
use net\authorize\api\controller\GetCustomerProfileController;
use net\authorize\api\controller\GetCustomerProfileIdsController;
use net\authorize\api\controller\UpdateCustomerProfileController;
use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use NTI\AuthorizeNetBundle\Exception\ANetInvalidRequestFormatException;
use NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileModel;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
use NTI\AuthorizeNetBundle\Models\Customer\CustomerProfileModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ANetCustomerProfileService
 * @package NTI\AuthorizeNetBundle\Services\Customer
 */
class ANetCustomerProfileService extends ANetRequestService {

    /**
     * ANetCustomerProfileService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Get all the Customer Profile IDs
     *
     * @return string[]
     * @throws ANetRequestException
     */
    public function getAllProfiles() {
        $request = new GetCustomerProfileIdsRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $controller = new GetCustomerProfileIdsController($request);

        /** @var GetCustomerProfileIdsResponse $response */
        $response = $controller->executeWithApiResponse($this->endpoint);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            $profileIds = $response->getIds();
            return $profileIds;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * @param $profileId
     * @return \net\authorize\api\contract\v1\CustomerProfileMaskedType
     * @throws ANetRequestException
     */
    public function getProfile($profileId) {
        $request = new GetCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($profileId);
        $request->setUnmaskExpirationDate(true);
        $controller = new GetCustomerProfileController($request);

        /** @var GetCustomerProfileResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            $profile = $response->getProfile();
            return $profile;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Creates a new Customer Profile in Authorize.NET
     *
     * @param $data
     * @return string
     * @throws ANetRequestException
     * @throws ANetInvalidRequestFormatException
     */
    public function createProfile($data) {

        /** @var CustomerProfileModel $profile */
        $profile = $this->container->get('jms_serializer')->deserialize(json_encode($data), CustomerProfileModel::class, 'json');

        $validator = $this->container->get('validator');
        $errors = $validator->validate($profile);
        if(count($errors) > 0) {
            throw new ANetInvalidRequestFormatException($errors);
        }

        // Prepare the Request
        $refId = uniqid("ref_");
        $request = new CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);

        // Setup Customer Profile
        $profileType = new CustomerProfileType();
        $profileType->setMerchantCustomerId($profile->getMerchantAccountId());
        $profileType->setEmail($profile->getEmail());
        $profileType->setDescription($profile->getDescription());

        // Payment Profiles (Optional)
        $paymentProfileTypes = array();
        if($profile->getPaymentProfiles()) {

            /** @var CustomerPaymentProfileModel $paymentProfile */
            foreach($profile->getPaymentProfiles() as $paymentProfile) {
                if(!$paymentProfile->getBillTo()) {
                    throw new ANetRequestException("The Contact information is required for new Payment Profiles.");
                }

                if(!$paymentProfile->getPayment()) {
                    throw new ANetRequestException("The Credit Card information is required for new Payment Profiles.");
                }

                // Set credit card information for payment profile
                $creditCard = new CreditCardType();
                $creditCard->setCardNumber($paymentProfile->getPayment()->getCreditCard()->getCardNumber());
                $creditCard->setExpirationDate($paymentProfile->getPayment()->getCreditCard()->getExpirationDate());
                $creditCard->setCardCode($paymentProfile->getPayment()->getCreditCard()->getCode());
                $paymentCreditCard = new PaymentType();
                $paymentCreditCard->setCreditCard($creditCard);

                // Create the Bill To info for new payment type
                $billTo = new CustomerAddressType();
                $billTo->setFirstName($paymentProfile->getBillTo()->getFirstName());
                $billTo->setLastName($paymentProfile->getBillTo()->getLastName());
                $billTo->setPhoneNumber($paymentProfile->getBillTo()->getPhoneNumber());
                $billTo->setCompany($paymentProfile->getBillTo()->getCompany());
                $billTo->setAddress($paymentProfile->getBillTo()->getAddress());
                $billTo->setCity($paymentProfile->getBillTo()->getCity());
                $billTo->setState($paymentProfile->getBillTo()->getState());
                $billTo->setZip($paymentProfile->getBillTo()->getZip());
                $billTo->setCountry($paymentProfile->getBillTo()->getCountry());

                // Create a new CustomerPaymentProfile object
                $paymentProfileType = new CustomerPaymentProfileType();
                $paymentProfileType->setCustomerType($profile->getCustomerType());
                $paymentProfileType->setBillTo($billTo);
                $paymentProfileType->setPayment($paymentCreditCard);

                $paymentProfileTypes[] = $paymentProfileType;
            }
            $profileType->setPaymentProfiles($paymentProfileTypes);
        }

        $request->setProfile($profileType);

        /** @var CreateCustomerProfileController $controller */
        $controller = new CreateCustomerProfileController($request);

        /** @var CreateCustomerProfileResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return $response->getCustomerProfileId();
        } else {
            $errorMessages = $response->getMessages()->getMessage();

            // Extract the Profile ID if trying to create a duplicate.
            if($errorMessages[0]->getCode() == self::E00039_DUPLICATE_PROFILE) {
                preg_match('/ \d+ /', $errorMessages[0]->getText(), $matches);
                if(count($matches) > 0) {
                    return $matches[0];
                }
            }
            
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }

    }

    /**
     * Updates a Customer Profile
     *
     * @param $profileId
     * @param $data
     * @return bool
     * @throws ANetRequestException
     * @throws ANetInvalidRequestFormatException
     */
    public function updateProfile($profileId, $data) {

        /** @var CustomerProfileModel $profile */
        $profile = $this->container->get('jms_serializer')->deserialize(json_encode($data), CustomerProfileModel::class, 'json');
        $validator = $this->container->get('validator');
        $errors = $validator->validate($profile);

        if(count($errors) > 0) {
            throw new ANetInvalidRequestFormatException($errors);
        }

        $customerProfile = new CustomerProfileExType();
        $customerProfile->setCustomerProfileId($profileId);

        $customerProfile->setDescription($profile->getDescription());
        $customerProfile->setEmail($profile->getEmail());

        $refId = uniqid("ref_");
        $request = new UpdateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);

        /** @var UpdateCustomerProfileController() $controller */
        $controller = new UpdateCustomerProfileController($request);

        /** @var UpdateCustomerProfileResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return true;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Deletes a Customer Profile
     *
     * @param $profileId
     * @return bool
     * @throws ANetRequestException
     */
    public function deleteProfile($profileId) {
        // Delete an existing customer profile
        $request = new DeleteCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($profileId);
        $controller = new DeleteCustomerProfileController($request);
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