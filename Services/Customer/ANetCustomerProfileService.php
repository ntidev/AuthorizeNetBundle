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
use NTI\AuthorizeNetBundle\Exception\Customer\ANetRequestException;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
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
        $response = $controller->executeWithApiResponse($this->endpoint);
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
     */
    public function createProfile($data) {

        // Required parameters
        $merchantAccountId = $data["merchant_account_id"];
        $email = $data["email"];
        $description = $data["description"];
        $customerType = $data["type"] ?? "individual";

        // Prepare the Request
        $refId = uniqid("ref_");
        $request = new CreateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);

        // Setup Customer Profile
        $profile = new CustomerProfileType();
        $profile->setMerchantCustomerId($merchantAccountId);
        $profile->setEmail($email);
        $profile->setDescription($description);

        // Payment Profiles (Optional)
        $paymentProfileTypes = array();
        if(isset($data["payment_profiles"])) {

            $paymentProfiles = $data["payment_profiles"];
            foreach($paymentProfiles as $paymentProfile) {
                $company = $paymentProfile["company"];
                $address = $paymentProfile["address"];
                $city = $paymentProfile["city"];
                $state = $paymentProfile["state"];
                $zip = $paymentProfile["zip"];
                $country = $paymentProfile["country"] ?? "USA";
                $firstname = $paymentProfile["firstname"];
                $lastname = $paymentProfile["lastname"];
                $phoneNumber = $paymentProfile["phone_number"];
                $faxNumber = $paymentProfile["fax"] ?? "";
                $ccNumber = $paymentProfile["cc_number"];
                $ccExpiration = $paymentProfile["cc_expiration"];
                $ccCode = $paymentProfile["cc_code"];

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
                $billTo->setCompany($company);
                $billTo->setAddress($address);
                $billTo->setCity($city);
                $billTo->setState($state);
                $billTo->setZip($zip);
                $billTo->setCountry($country);
                $billTo->setPhoneNumber($phoneNumber);
                $billTo->setfaxNumber($faxNumber);

                // Create a new CustomerPaymentProfile object
                $paymentProfileType = new CustomerPaymentProfileType();
                $paymentProfileType->setCustomerType($customerType);
                $paymentProfileType->setBillTo($billTo);
                $paymentProfileType->setPayment($paymentCreditCard);
                $paymentProfileType->setDefaultpaymentProfile(true);

                $paymentProfileTypes[] = $paymentProfileType;
            }
            $profile->setPaymentProfiles($paymentProfileTypes);
        }

        $request->setProfile($profile);

        /** @var CreateCustomerProfileController $controller */
        $controller = new CreateCustomerProfileController($request);

        /** @var CreateCustomerProfileResponse $response */
        $response = $controller->executeWithApiResponse($this->endpoint);

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return $response->getCustomerProfileId();
        } else {
            $errorMessages = $response->getMessages()->getMessage();
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
     */
    public function updateProfile($profileId, $data) {

        // Parameters
        $email = $data["email"] ?? null;
        $description = $data["description"] ?? null;

        $customerProfile = new CustomerProfileExType();
        $customerProfile->setCustomerProfileId($profileId);
        if($description) {
            $customerProfile->setDescription($description);
        }
        if($email) {
            $customerProfile->setEmail($email);
        }

        $refId = uniqid("ref_");
        $request = new UpdateCustomerProfileRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setRefId($refId);

        /** @var UpdateCustomerProfileController() $controller */
        $controller = new UpdateCustomerProfileController($request);

        /** @var UpdateCustomerProfileResponse $response */
        $response = $controller->executeWithApiResponse($this->endpoint);

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
        $response = $controller->executeWithApiResponse($this->endpoint);
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            return true;
        } else {
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }
}