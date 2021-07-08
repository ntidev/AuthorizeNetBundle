<?php

namespace NTI\AuthorizeNetBundle\Constants;


class TransactionStatus {

    public const STATUS_AUTHORIZED_PENDING_CAPTURE = "authorizedPendingCapture";
    public const STATUS_CAPTURED_PENDING_SETTLEMENT = "capturedPendingSettlement";
    public const STATUS_COMMUNICATION_ERROR = "communicationError";
    public const STATUS_REFUND_SETTLED_SUCCESSFULLY = "refundSettledSuccessfully";
    public const STATUS_REFUND_PENDING_SETTLEMENT = "refundPendingSettlement";
    public const STATUS_APPROVED_REVIEW = "approvedReview";
    public const STATUS_DECLINED = "declined";
    public const STATUS_COULD_NOT_VOID = "couldNotVoid";
    public const STATUS_EXPIRED = "expired";
    public const STATUS_GENERAL_ERROR = "generalError";
    public const STATUS_FAILED_REVIEW = "failedReview";
    public const STATUS_SETTLED_SUCCESSFULLY = "settledSuccessfully";
    public const STATUS_SETTLEMENT_ERROR = "settlementError";
    public const STATUS_UNDER_REVIEW = "underReview";
    public const STATUS_VOIDED = "voided";
    public const STATUS_FDS_PENDING_REVIEW = "FDSPendingReview";
    public const STATUS_FDS_AUTHORIZED_PENDING_REVIEW = "FDSAuthorizedPendingReview";
    public const STATUS_RETURNED_ITEM = "returnedItem";

    public static function getList() {
        return array(
            array("code" => "authorizedPendingCapture",  "description" => ""),
            array("code" => "capturedPendingSettlement",  "description" => ""),
            array("code" => "communicationError",  "description" => ""),
            array("code" => "refundSettledSuccessfully",  "description" => ""),
            array("code" => "refundPendingSettlement",  "description" => ""),
            array("code" => "approvedReview",  "description" => ""),
            array("code" => "declined",  "description" => ""),
            array("code" => "couldNotVoid",  "description" => ""),
            array("code" => "expired",  "description" => ""),
            array("code" => "generalError",  "description" => ""),
            array("code" => "failedReview",  "description" => ""),
            array("code" => "settledSuccessfully",  "description" => ""),
            array("code" => "settlementError",  "description" => ""),
            array("code" => "underReview",  "description" => ""),
            array("code" => "voided",  "description" => ""),
            array("code" => "FDSPendingReview",  "description" => ""),
            array("code" => "FDSAuthorizedPendingReview",  "description" => ""),
            array("code" => "returnedItem",  "description" => ""),
        );
    }

}
