<?php

namespace NTI\AuthorizeNet\Tests\Service\Customer;

use net\authorize\api\contract\v1\CustomerProfileMaskedType;
use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class ANetCustomerProfileServiceTest
 * @package NTI\AuthorizeNetBundle\Tests\Service\Customer
 */
class ANetCustomerProfileServiceTest extends KernelTestCase
{
    /** @var ContainerInterface $container */
    private $container;

    public function init()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
    }

    public function testGetAllProfiles() {
        $this->init();

        $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();

        $this->assertTrue(is_array($profiles), "The result was not an array.");
    }

    public function testGetProfile() {
        $this->init();

        $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();

        if(count($profiles) <= 0) {
            $this->fail("At least one Customer Profile is required in Authorize.NET in order to test this.");
            return;
        }

        $profile = $this->container->get('nti.authorizenet.customer_profile')->getProfile($profiles[0]);

        $this->assertInstanceOf(CustomerProfileMaskedType::class, $profile, "The result for the profile was not an instance of CustomerProfileMaskedType");

    }

    public function testCreateCustomerProfile() {
        $this->init();

        try {
            $profileId = $this->container->get('nti.authorizenet.customer_profile')->createProfile(array(
                "merchant_account_id" => uniqid(),
                "email" => "bugs@" . uniqid() . ".com",
                "description" => "bugs bunny's company",
                "paymentProfiles" => array(
                    array(
                        "company" => "ACME Corporation",
                        "firstname" => "Bugs",
                        "lastname" => "Bunny",
                        "email" => "bugs@" . uniqid() . ".com",
                        "address" => "71 Pilgrim Avenue Chevy Chase, MD 20815",
                        "city" => "Chevy Chase",
                        "state" => "Maryland",
                        "zip" => "20815",
                        "phone_number" => "18881234567",
                        "cc_number" => "1234567891011121",
                        "cc_expiration" => "2009-08",
                        "cc_code" => "323",
                    )
                )
            ));

            $this->assertNotEmpty($profileId, 'The Customer Profile Id for Authorize.NET was not a valid Id');

        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }
    }

}