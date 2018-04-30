<?php

namespace NTI\AuthorizeNet\Tests\Service\Customer;

use net\authorize\api\contract\v1\CustomerPaymentProfileExType;
use net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType;
use net\authorize\api\contract\v1\CustomerProfileMaskedType;
use NTI\AuthorizeNetBundle\Exception\ANetInvalidRequestFormatException;
use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ANetCustomerPaymentProfileServiceTest extends KernelTestCase {

    /** @var ContainerInterface $container */
    private $container;

    public function init()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
    }

    public function testCreatePaymentProfile() {
        $this->init();
        try {
            $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        $data = array(
            "billTo" => array(
                "company" => "ACME Corporation",
                "firstName" => "Bugs",
                "lastName" => "Bunny",
                "address" => "71 Pilgrim Avenue Chevy Chase, MD 20815",
                "city" => "Chevy Chase",
                "state" => "Maryland",
                "country" => "US",
                "zip" => "20815",
                "phoneNumber" => "18881234567",
            ),
            "payment" => array(
                "creditCard" => array(
                    "cardNumber" => "123456789101".rand(1000,9999),
                    "expirationDate" => "2009-08",
                    "code" => "323",
                )
            )
        );

        try {
            $profileId = $this->container->get('nti.authorizenet.customer_payment_profile')->createProfile($profiles[0], $data);
        } catch (ANetInvalidRequestFormatException $e) {
            $this->fail($e->getMessage());
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue($profileId >= 0, "The Profile Id returned was not valid: " . $profileId);

    }

    /** @depends testCreatePaymentProfile */
    public function testUpdateCustomerProfile() {
        $this->init();

        try {
            $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        /** @var CustomerProfileMaskedType $customerProfile */
        try {
            $customerProfile = $this->container->get('nti.authorizenet.customer_profile')->getProfile($profiles[0]);
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }
        $paymentProfiles = $customerProfile->getPaymentProfiles();

        if(count($paymentProfiles) <= 0) {
            $this->fail("At least one Payment Profile is required to be able to test the update.");
            return;
        }

        /** @var CustomerPaymentProfileMaskedType $paymentProfile */
        $paymentProfile = $paymentProfiles[0];

        $data = array(
            "billTo" => array(
                "company" => "ACME Corporation",
                "firstName" => "Bugs",
                "lastName" => "Bunny",
                "address" => "71 Pilgrim Avenue Chevy Chase, MD 20815",
                "city" => "Chevy Chase",
                "state" => "Maryland",
                "country" => "US",
                "zip" => "20815",
                "phoneNumber" => "18881234567",
            ),
            "payment" => array(
                "creditCard" => array(
                    "cardNumber" => "123456789101".rand(1000, 9999),
                    "expirationDate" => "2009-08",
                    "code" => "323",
                )
            ),
        );

        /** @var CustomerPaymentProfileExType $result */
        try {
            $result = $this->container->get('nti.authorizenet.customer_payment_profile')->updateProfile($customerProfile->getCustomerProfileId(), $paymentProfile->getCustomerPaymentProfileId(), $data);
        } catch (ANetInvalidRequestFormatException $e) {
            $this->fail($e->getMessage());
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertInstanceOf(CustomerPaymentProfileExType::class, $result, "The result was not an instance of CustomerPaymentProfileExType.");

    }

    /** @depends testUpdateCustomerProfile */
    public function testDeleteCustomerPaymentProfile() {
        $this->init();

        $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        /** @var CustomerProfileMaskedType $customerProfile */
        $customerProfile = $this->container->get('nti.authorizenet.customer_profile')->getProfile($profiles[0]);
        $paymentProfiles = $customerProfile->getPaymentProfiles();

        if(count($paymentProfiles) <= 0) {
            $this->fail("At least one Payment Profile is required to be able to test the update.");
            return;
        }

        /** @var CustomerPaymentProfileMaskedType $paymentProfile */
        $paymentProfile = $paymentProfiles[0];

        try {
            $result = $this->container->get('nti.authorizenet.customer_payment_profile')->deleteProfile($customerProfile->getCustomerProfileId(), $paymentProfile->getCustomerPaymentProfileId());
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue($result, "Unable to delete the Customer Payment Profile.");

    }

}