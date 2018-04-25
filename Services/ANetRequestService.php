<?php

namespace NTI\AuthorizeNetBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use net\authorize\api\constants\ANetEnvironment;
use NTI\AuthorizeNetBundle\Entity\ANetLog;
use Symfony\Component\DependencyInjection\ContainerInterface;
use net\authorize\api\contract\v1 as AnetAPI;

class ANetRequestService {

    // Error Codes
    const E00039_DUPLICATE_PROFILE = "E00039";


    // Endpoints CraueConfigBundle configuration keys
    const API_LOGIN_ID_KEY = "authorizenet.api.login_id";
    const API_TRANSACTION_KEY_KEY = "authorizenet.api.transaction_key";
    const API_ENVIRONMENT_KEY = "authorizenet.api.environment";

    /** @var ContainerInterface $container */
    protected $container;

    /** @var AnetAPI\MerchantAuthenticationType $merchantAuthentication */
    protected $merchantAuthentication;

    /** @var string $environment */
    protected $endpoint;

    /**
     * ANetRequestService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->merchantAuthentication = new AnetAPI\MerchantAuthenticationType();

        $loginId = $this->container->get('craue_config')->get(self::API_LOGIN_ID_KEY);
        $transactionKey = $this->container->get('craue_config')->get(self::API_TRANSACTION_KEY_KEY);
        $environment = $this->container->get('craue_config')->get(self::API_ENVIRONMENT_KEY);

        $this->merchantAuthentication->setName($loginId);
        $this->merchantAuthentication->setTransactionKey($transactionKey);
        $this->endpoint = ($environment == "production") ? ANetEnvironment::PRODUCTION : ANetEnvironment::SANDBOX;
    }
}