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
    public const STATUS_PENDING_FINAL_SETTLEMENT="pendingFinalSettlement";
    public const STATUS_PENDING_SETTLEMENT = "pendingSettlement";
    public const STATUS_FAILED_REVIEW = "failedReview";
    public const STATUS_SETTLED_SUCCESSFULLY = "settledSuccessfully";
    public const STATUS_SETTLEMENT_ERROR = "settlementError";
    
    public const STATUS_UNDER_REVIEW = "underReview";
    public const STATUS_VOIDED = "voided";
    public const STATUS_FDS_PENDING_REVIEW = "FDSPendingReview";
    public const STATUS_FDS_AUTHORIZED_PENDING_REVIEW = "FDSAuthorizedPendingReview";
    public const STATUS_RETURNED_ITEM = "returnedItem";
    public const STATUS_RETURNED = "returned";
    public const STATUS_CHARGE_BACK="chargeback";
    public const STATUS_CHARGE_REVERSAL="chargebackReversal";
    public const STATUS_AUTORIZED_PENDING_RELEASE="authorizedPendingRelease";


    public static function getPendingStatuses() {
        return array(
            self::STATUS_AUTHORIZED_PENDING_CAPTURE,    // Authorized pending capture
            self::STATUS_CAPTURED_PENDING_SETTLEMENT,   // Captured pending settlement
            self::STATUS_COMMUNICATION_ERROR,           // Communication error
            self::STATUS_REFUND_PENDING_SETTLEMENT,     // Refund pending settlement
            self::STATUS_APPROVED_REVIEW,               // Approved for review
            self::STATUS_PENDING_SETTLEMENT,            // Pending settlement
        );
    }

    public static function isPending($transactionStatus) {
        return in_array($transactionStatus, self::getPendingStatuses());
    }

    public static function isSettled($transactionStatus) {
        return in_array(strtolower($transactionStatus), array(
            strtolower(self::STATUS_SETTLED_SUCCESSFULLY),          // Settled successfully
        ));
    }

    public static function isFailed($transactionStatus) {
        return in_array(strtolower($transactionStatus), array(
            strtolower(self::STATUS_DECLINED),                       // Declined
            strtolower(self::STATUS_COULD_NOT_VOID),                 // Could not void
            strtolower(self::STATUS_EXPIRED),                        // Expired
            strtolower(self::STATUS_GENERAL_ERROR),                  // General error
            strtolower(self::STATUS_FAILED_REVIEW),                  // Failed review
            strtolower(self::STATUS_SETTLEMENT_ERROR),               // Settlement error
            strtolower(self::STATUS_UNDER_REVIEW),                   // Under review
            strtolower(self::STATUS_VOIDED),                         // Voided
            strtolower(self::STATUS_RETURNED_ITEM),                  // Returned Item
            strtolower(self::STATUS_RETURNED),                       // Returned
        ));
    }

    public static function isUnknown($transactionStatus) {
        return in_array(strtolower($transactionStatus), array(
            strtolower(self::STATUS_PENDING_FINAL_SETTLEMENT),       // Pending Final Settlement
            strtolower(self::STATUS_CHARGE_BACK),                    // Charge Back
            strtolower(self::STATUS_CHARGE_REVERSAL),                // Charge Reversal
        ));
    }

    public static function getList() {
        return array(
            array("code" => self::STATUS_AUTHORIZED_PENDING_CAPTURE,  "description" => "Authorized Pending Capture"),
            array("code" => self::STATUS_CAPTURED_PENDING_SETTLEMENT,  "description" => "Captured and Pending Settlement"),
            array("code" => self::STATUS_COMMUNICATION_ERROR,  "description" => "Communication Error"),
            array("code" => self::STATUS_REFUND_SETTLED_SUCCESSFULLY,  "description" => "Refund Settled Successfully"),
            array("code" => self::STATUS_REFUND_PENDING_SETTLEMENT,  "description" => "Refund Pending Settlement"),
            array("code" => self::STATUS_APPROVED_REVIEW,  "description" => "Approved for Review"),
            array("code" => self::STATUS_DECLINED,  "description" => "Declined"),
            array("code" => self::STATUS_COULD_NOT_VOID,  "description" => "Could not void"),
            array("code" => self::STATUS_EXPIRED,  "description" => "Expired"),
            array("code" => self::STATUS_GENERAL_ERROR,  "description" => "General Error"),
            array("code" => self::STATUS_FAILED_REVIEW,  "description" => "Failed to Review"),
            array("code" => self::STATUS_SETTLED_SUCCESSFULLY,  "description" => "Settled Successfully"),
            array("code" => self::STATUS_PENDING_SETTLEMENT,  "description" => "Pending Settlement"),
            array("code" => self::STATUS_PENDING_FINAL_SETTLEMENT,  "description" => "Pending Final Settlement"),
            array("code" => self::STATUS_SETTLEMENT_ERROR,  "description" => "Settlement Error"),
            array("code" => self::STATUS_UNDER_REVIEW,  "description" => "Under Review"),
            array("code" => self::STATUS_VOIDED,  "description" => "Voided"),
            array("code" => self::STATUS_FDS_PENDING_REVIEW,  "description" => "FDS Pending Review"),
            array("code" => self::STATUS_FDS_AUTHORIZED_PENDING_REVIEW,  "description" => "FDS Authorized Pending Review"),
            array("code" => self::STATUS_RETURNED_ITEM,  "description" => "Returned Item"),
            array("code" => self::STATUS_RETURNED,  "description" => "Returned"),
            array("code" => self::STATUS_CHARGE_BACK,  "description" => "Charge Back"),
            array("code" => self::STATUS_CHARGE_REVERSAL,  "description" => "Charge Reversal"),
            
        );
    }

    public static function getDescription($transactionStatus) {
        $list = self::getList();
        foreach ($list as $status) {
            if($status["code"] == $transactionStatus) {
                return $status["description"];
            }
        }
        return $transactionStatus ?? "Unknown";
    }

}
