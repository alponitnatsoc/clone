<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Pay
 *
 * @ORM\Table(name="pay", indexes={
 *  @ORM\Index(name="fk_pay_id_dispercion_novo", columns={"id_dispercion_novo"})
 * })
 * @ORM\Entity
 */
class Pay
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_pay", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPay;

    /**
     * @ORM\Column(name="id_dispercion_novo", type="integer", nullable=true)
     */
    private $idDispercionNovo;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayMethod
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayMethod")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
     * })
     * @Exclude
     */
    private $payMethodPayMethod;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     * @Exclude
     */
    private $userIdUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription", cascade={"persist"}, inversedBy="payPay")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_id_purchase_orders_description", referencedColumnName="id_purchase_orders_description")
     * })
     */
    private $purchaseOrdersDescription;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatusPurchaseOrdersStatus;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $message;

    /**
     * Set idPay
     *
     * @param integer $idPay
     *
     * @return Pay
     */
    public function setIdPay($idPay)
    {
        $this->idPay = $idPay;

        return $this;
    }

    /**
     * Get idPay
     *
     * @return integer
     */
    public function getIdPay()
    {
        return $this->idPay;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Pay
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Set payMethodPayMethod
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod
     *
     * @return Pay
     */
    public function setPayMethodPayMethod(\RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod)
    {
        $this->payMethodPayMethod = $payMethodPayMethod;

        return $this;
    }

    /**
     * Get payMethodPayMethod
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayMethod
     */
    public function getPayMethodPayMethod()
    {
        return $this->payMethodPayMethod;
    }

    /**
     * Set userIdUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userIdUser
     *
     * @return Pay
     */
    public function setUserIdUser(\RocketSeller\TwoPickBundle\Entity\User $userIdUser = null)
    {
        $this->userIdUser = $userIdUser;

        return $this;
    }

    /**
     * Get userIdUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getUserIdUser()
    {
        return $this->userIdUser;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Pay
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return Pay
     */
    public function setPurchaseOrdersStatusPurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus = null)
    {
        $this->purchaseOrdersStatusPurchaseOrdersStatus = $purchaseOrdersStatusPurchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersStatusPurchaseOrdersStatus()
    {
        return $this->purchaseOrdersStatusPurchaseOrdersStatus;
    }

    /**
     * Set purchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription
     *
     * @return Pay
     */
    public function setPurchaseOrdersDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription = null)
    {
        $this->purchaseOrdersDescription = $purchaseOrdersDescription;

        return $this;
    }

    /**
     * Get purchaseOrdersDescription
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     */
    public function getPurchaseOrdersDescription()
    {
        return $this->purchaseOrdersDescription;
    }

    /**
     * Set idDispercionNovo
     *
     * @param integer $idDispercionNovo
     *
     * @return Pay
     */
    public function setIdDispercionNovo($idDispercionNovo)
    {
        $this->idDispercionNovo = $idDispercionNovo;

        return $this;
    }

    /**
     * Get idDispercionNovo
     *
     * @return integer
     */
    public function getIdDispercionNovo()
    {
        return $this->idDispercionNovo;
    }

}
