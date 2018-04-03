<?php

namespace NTI\AuthorizeNet\Tests\Service\Customer;

use net\authorize\api\contract\v1\CustomerPaymentProfileExType;
use net\authorize\api\contract\v1\CustomerPaymentProfileMaskedType;
use net\authorize\api\contract\v1\CustomerProfileMaskedType;
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
        $profiles = $this->container->get('nti.authorize_dot_net_api.customer_profile')->getAllProfiles();
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        $data = array(
            "company" => "ACME Corporation",
            "firstname" => "Bugs",
            "lastname" => "Bunny",
            "email" => "bugs@" . uniqid() . ".com",
            "address" => "71 Pilgrim Avenue Chevy Chase, MD 20815",
            "city" => "Chevy Chase",
            "state" => "Maryland",
            "zip" => "20815",
            "phone_number" => "18881234567",
            "cc_number" => "123456789101".rand(1000, 9999),
            "cc_expiration" => "2009-08",
            "cc_code" => "323",
        );

        $profileId = $this->container->get('nti.authorize_dot_net_api.customer_payment_profile')->createProfile($profiles[0], $data);

        $this->assertTrue($profileId >= 0, "The Profile Id returned was not valid: " . $profileId);

    }

    /** @depends testCreatePaymentProfile */
    public function testUpdateCustomerProfile() {
        $this->init();

        $profiles = $this->container->get('nti.authorize_dot_net_api.customer_profile')->getAllProfiles();
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        /** @var CustomerProfileMaskedType $customerProfile */
        $customerProfile = $this->container->get('nti.authorize_dot_net_api.customer_profile')->getProfile($profiles[0]);
        $paymentProfiles = $customerProfile->getPaymentProfiles();

        if(count($paymentProfiles) <= 0) {
            $this->fail("At least one Payment Profile is required to be able to test the update.");
            return;
        }

        /** @var CustomerPaymentProfileMaskedType $paymentProfile */
        $paymentProfile = $paymentProfiles[0];

        $data = array(
            "bill_to" => array(
                "company" => "ACME Corporation",
                "firstname" => "Bugs",
                "lastname" => "Bunny",
                "email" => "bugs@" . uniqid() . ".com",
                "address" => "71 Pilgrim Avenue Chevy Chase, MD 20815",
                "city" => "Chevy Chase",
                "state" => "Maryland",
                "zip" => "20815",
                "phone_number" => "18881234567",
            ),
            "creditcard" => array(
                "cc_number" => "123456789101".rand(1000, 9999),
                "cc_expiration" => "2009-08",
                "cc_code" => "323",
            ),
        );

        /** @var CustomerPaymentProfileExType $result */
        $result = $this->container->get('nti.authorize_dot_net_api.customer_payment_profile')->updateProfile($customerProfile->getCustomerProfileId(),$paymentProfile->getCustomerPaymentProfileId(), $data);

        $this->assertInstanceOf(CustomerPaymentProfileExType::class, $result, "The result was not an instance of CustomerPaymentProfileExType.");

    }

    /** @depends testUpdateCustomerProfile */
    public function testDeleteCustomerPaymentProfile() {
        $this->init();

        $profiles = $this->container->get('nti.authorize_dot_net_api.customer_profile')->getAllProfiles();
        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        /** @var CustomerProfileMaskedType $customerProfile */
        $customerProfile = $this->container->get('nti.authorize_dot_net_api.customer_profile')->getProfile($profiles[0]);
        $paymentProfiles = $customerProfile->getPaymentProfiles();

        if(count($paymentProfiles) <= 0) {
            $this->fail("At least one Payment Profile is required to be able to test the update.");
            return;
        }

        /** @var CustomerPaymentProfileMaskedType $paymentProfile */
        $paymentProfile = $paymentProfiles[0];

        $result = $this->container->get('nti.authorize_dot_net_api.customer_payment_profile')->deleteProfile($paymentProfile->getCustomerPaymentProfileId());

        $this->assertTrue($result, "Unable to delete the Customer Payment Profile.");

    }

}