<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pay
 *
 * @ORM\Table(name="pay", indexes={@ORM\Index(name="fk_pay_pay_type1", columns={"pay_type_id_pay_type"}), @ORM\Index(name="fk_pay_purchase_orders1", columns={"purchase_orders_id_purchase_orders"}), @ORM\Index(name="fk_pay_pay_method1", columns={"pay_method_id_pay_method"})})
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
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_id_purchase_orders", referencedColumnName="id_purchase_orders")
     * })
     */
    private $purchaseOrdersPurchaseOrders;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_type_id_pay_type", referencedColumnName="id_pay_type")
     * })
     */
    private $payTypePayType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayMethod
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayMethod")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
     * })
     */
    private $payMethodPayMethod;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userIdUser;

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
     * Set purchaseOrdersPurchaseOrders
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders
     *
     * @return Pay
     */
    public function setPurchaseOrdersPurchaseOrders(\RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders)
    {
        $this->purchaseOrdersPurchaseOrders = $purchaseOrdersPurchaseOrders;

        return $this;
    }

    /**
     * Get purchaseOrdersPurchaseOrders
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     */
    public function getPurchaseOrdersPurchaseOrders()
    {
        return $this->purchaseOrdersPurchaseOrders;
    }

    /**
     * Set payTypePayType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType
     *
     * @return Pay
     */
    public function setPayTypePayType(\RocketSeller\TwoPickBundle\Entity\PayType $payTypePayType)
    {
        $this->payTypePayType = $payTypePayType;

        return $this;
    }

    /**
     * Get payTypePayType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayType
     */
    public function getPayTypePayType()
    {
        return $this->payTypePayType;
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
}
