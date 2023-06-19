<?php

namespace NTI\AuthorizeNetBundle\Services\Payment;

use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

/**
 * Class ANetPaymentService
 * @package NTI\AuthorizeNetBundle\Services\Payment
 */
class ANetPaymentService extends ANetRequestService
{
    /**
     * ANetPaymentService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }


    /**
     * Charges a Payment Profile
     *
     * @param $customerProfileId
     * @param $paymentProfileId
     * @param $amount
     * @return AnetAPI\TransactionResponseType
     * @throws ANetRequestException
     */
    public function chargePaymentProfile($customerProfileId, $paymentProfileId, $amount) {

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($customerProfileId);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);

        $profileToCharge->setPaymentProfile($paymentProfile);
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setTransactionRequest( $transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);

        /** @var AnetAPI\CreateTransactionResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            $tresponse = $response->getTransactionResponse();
            if ($tresponse != null && $tresponse->getMessages() != null)
            {
                return $tresponse;
            } else {
                $this->container->get('logger')->log("ERROR", json_encode($response));
                throw new ANetRequestException($tresponse->getErrors()[0]->getErrorText(),$tresponse->getErrors()[0]->getErrorCode());
            }
        } else {

            if($response != null && $response->getTransactionResponse() != null) {
                $this->container->get('logger')->log("ERROR", json_encode($response));
                $tresponse = $response->getTransactionResponse();
                $ErrorCode=$tresponse->getErrors() != null ? $tresponse->getErrors()[0]->getErrorCode() : $response->getMessages()->getMessage()[0]->getCode();
                $ErrorText=$tresponse->getErrors() != null ? $tresponse->getErrors()[0]->getErrorText() : $response->getMessages()->getMessage()[0]->getText();
                throw new ANetRequestException($ErrorText,$ErrorCode);
            }
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException($errorMessages[0]->getText(),$errorMessages[0]->getCode());
        }
    }

}
