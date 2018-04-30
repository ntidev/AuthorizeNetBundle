<?php

namespace NTI\AuthorizeNet\Tests\Service\Customer;

use net\authorize\api\contract\v1\CustomerProfileMaskedType;
use NTI\AuthorizeNetBundle\Exception\ANetInvalidRequestFormatException;
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

        try {
            $profiles = $this->container->get('nti.authorizenet.customer_profile')->getAllProfiles();
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue(is_array($profiles), "The result was not an array.");
    }

    public function testGetProfile() {
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

        try {
            $profile = $this->container->get('nti.authorizenet.customer_profile')->getProfile($profiles[0]);
        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertInstanceOf(CustomerProfileMaskedType::class, $profile, "The result for the profile was not an instance of CustomerProfileMaskedType");

    }

    public function testCreateCustomerProfile() {
        $this->init();

        try {
            $data = array(
                "merchant_account_id" => uniqid(),
                "email" => "bugs@" . uniqid() . ".com",
                "description" => "bugs bunny's company",
                "paymentProfiles" => array(
                    array(
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
                                "cardNumber" => "1234567891011121",
                                "expirationDate" => "2009-08",
                                "code" => "323",
                            )
                        )
                    )
                )
            );

            $profileId = $this->container->get('nti.authorizenet.customer_profile')->createProfile($data);

            $this->assertNotEmpty($profileId, 'The Customer Profile Id for Authorize.NET was not a valid Id');

        } catch (ANetRequestException $e) {
            $this->fail($e->getMessage());
        } catch (ANetInvalidRequestFormatException $e) {
            $this->fail($e->getMessage());
        }
    }

}