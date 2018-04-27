<?php

namespace NTI\AuthorizeNetBundle\Models\Customer;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerPaymentProfileModel {

    /**
     * @var CustomerPaymentProfileBillToModel
     *
     * @Assert\Valid()
     * @JMS\SerializedName("billTo")
     * @JMS\Type("NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfileBillToModel")
     */
    private $billTo;

    /**
     * @var CustomerPaymentProfilePaymentModel
     *
     * @Assert\Valid()
     * @JMS\SerializedName("payment")
     * @JMS\Type("NTI\AuthorizeNetBundle\Models\Customer\CustomerPaymentProfilePaymentModel")
     */
    private $payment;

    /**
     * @return CustomerPaymentProfileBillToModel
     */
    public function getBillTo()
    {
        return $this->billTo;
    }

    /**
     * @param CustomerPaymentProfileBillToModel $billTo
     * @return CustomerPaymentProfileModel
     */
    public function setBillTo($billTo)
    {
        $this->billTo = $billTo;
        return $this;
    }

    /**
     * @return CustomerPaymentProfilePaymentModel
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param CustomerPaymentProfilePaymentModel $payment
     * @return CustomerPaymentProfileModel
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

}