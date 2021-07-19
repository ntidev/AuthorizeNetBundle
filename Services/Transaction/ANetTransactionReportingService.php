<?php

namespace NTI\AuthorizeNetBundle\Services\Transaction;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use NTI\AuthorizeNetBundle\Exception\ANetRequestException;
use NTI\AuthorizeNetBundle\Services\ANetRequestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ANetTransactionReportingService
 * @package NTI\AuthorizeNetBundle\Services\Transaction
 */
class ANetTransactionReportingService extends ANetRequestService
{
    /**
     * ANetTransactionReportingService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * Get the details of a given transaction id
     *
     * @param $transactionId
     * @return array|AnetAPI\TransactionDetailsType
     * @throws ANetRequestException
     */
    public function getTransactionDetails($transactionId) {

        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setTransId($transactionId);
        $controller = new AnetController\GetTransactionDetailsController($request);

        /** @var AnetAPI\GetTransactionDetailsResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getTransaction()) ? $response->getTransaction() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Get the list of Transactions for a given Customer
     *
     * @param $customerProfileId
     * @return array|AnetAPI\TransactionSummaryType[]
     * @throws ANetRequestException
     */
    public function getCustomerTransactionList($customerProfileId) {

        $request = new AnetAPI\GetTransactionListForCustomerRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setCustomerProfileId($customerProfileId);
        $controller = new AnetController\GetTransactionListForCustomerController($request);

        /** @var AnetAPI\GetTransactionListResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getTransactions()) ? $response->getTransactions() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }

    }

    /**
     * Get the list of unsettled transactions
     *
     * @return array|AnetAPI\TransactionSummaryType[]
     * @throws ANetRequestException
     */
    public function getUnsettledTransactionList()
    {

        $request = new AnetAPI\GetUnsettledTransactionListRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);

        $controller = new AnetController\GetUnsettledTransactionListController($request);

        /** @var AnetAPI\GetUnsettledTransactionListResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getTransactions()) ? $response->getTransactions() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Get the list of batches of transactions
     *
     * @return array|AnetAPI\TransactionSummaryType[]
     * @throws ANetRequestException
     */
    public function getSettledBatchList($firstSettlementDate, $lastSettlementDate)
    {

        $request = new AnetAPI\GetSettledBatchListRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setIncludeStatistics(true);

        // Both the first and last dates must be in the same time zone
        // The time between first and last dates, inclusively, cannot exceed 31 days.
        $request->setFirstSettlementDate($firstSettlementDate);
        $request->setLastSettlementDate($lastSettlementDate);
        $controller = new AnetController\GetSettledBatchListController ($request);

        /** @var AnetAPI\GetSettledBatchListResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }


        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getBatchList()) ? $response->getBatchList() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Get the list of transactions for a given Batch ID
     *
     * @param $batchId
     * @return array|AnetAPI\TransactionSummaryType[]
     * @throws ANetRequestException
     */
    public function getTransactionList($batchId)
    {

        //Setting a valid batch Id for the Merchant
        $request = new AnetAPI\GetTransactionListRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setBatchId($batchId);
        $controller = new AnetController\GetTransactionListController($request);
        //Retrieving transaction list for the given Batch Id

        /** @var AnetAPI\GetTransactionListResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getTransactions()) ? $response->getTransactions() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Get the Statistics for a given Batch
     *
     * @param $batchId
     * @return array|AnetAPI\BatchStatisticType[]
     * @throws ANetRequestException
     */
    public function getBatchStatistics($batchId) {

        // Creating a request
        $request = new AnetAPI\GetBatchStatisticsRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setBatchId($batchId);

        //Creating the controller
        $controller = new AnetController\GetBatchStatisticsController($request);

        /** @var AnetAPI\GetBatchStatisticsResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }


        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getBatch()->getStatistics()) ? $response->getBatch()->getStatistics() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Get the list of transactions that are currently being held
     *
     * @return array|AnetAPI\TransactionSummaryType[]
     * @throws ANetRequestException
     */
    public function getHeldTransactionList() {
        $request = new AnetAPI\GetUnsettledTransactionListRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setStatus("pendingApproval");
        $controller = new AnetController\GetUnsettledTransactionListController($request);

        /** @var AnetAPI\GetUnsettledTransactionListResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            return ($response->getTransactions()) ? $response->getTransactions() : array();
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }
    }

    /**
     * Approve or Decline a Held transaction
     *
     * @param $transactionId
     * @param string $action
     * @return AnetAPI\TransactionResponseType
     * @throws ANetRequestException
     */
    public function approveOrDeclineHeldTransaction($transactionId, $action = "decline") {
        //create a transaction
        $transactionRequestType = new AnetAPI\HeldTransactionRequestType();
        $transactionRequestType->setAction($action); // approve or decline
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\UpdateHeldTransactionRequest();
        $request->setMerchantAuthentication($this->merchantAuthentication);
        $request->setHeldTransactionRequest($transactionRequestType);
        $controller = new AnetController\UpdateHeldTransactionController($request);

        /** @var AnetAPI\UpdateHeldTransactionResponse $response */
        try {
            $response = $controller->executeWithApiResponse($this->endpoint);
        } catch (\Exception $ex) {
            $this->container->get('logger')->log("ERROR", $ex->getMessage());
            throw new ANetRequestException($ex->getMessage());
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            $tresponse = $response->getTransactionResponse();
            if ($tresponse != null && $tresponse->getMessages() != null) {
                return $tresponse;
            } else {
                $this->container->get('logger')->log("ERROR", json_encode($response));
                $errorMessages = $tresponse->getMessages()->getMessage();
                throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
            }
        } else {
            $this->container->get('logger')->log("ERROR", json_encode($response));
            $errorMessages = $response->getMessages()->getMessage();
            throw new ANetRequestException("Error " . $errorMessages[0]->getCode() . ": " . $errorMessages[0]->getText());
        }

    }
}
