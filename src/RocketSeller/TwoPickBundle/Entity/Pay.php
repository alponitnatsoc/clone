<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPay;

    /**
     * @var \AppBundle\Entity\PurchaseOrders
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PurchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_id_purchase_orders", referencedColumnName="id_purchase_orders")
     * })
     */
    private $purchaseOrdersPurchaseOrders;

    /**
     * @var \AppBundle\Entity\PayType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PayType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_type_id_pay_type", referencedColumnName="id_pay_type")
     * })
     */
    private $payTypePayType;

    /**
     * @var \AppBundle\Entity\PayMethod
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PayMethod")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
     * })
     */
    private $payMethodPayMethod;



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
     * @param \AppBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders
     *
     * @return Pay
     */
    public function setPurchaseOrdersPurchaseOrders(\AppBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders)
    {
        $this->purchaseOrdersPurchaseOrders = $purchaseOrdersPurchaseOrders;

        return $this;
    }

    /**
     * Get purchaseOrdersPurchaseOrders
     *
     * @return \AppBundle\Entity\PurchaseOrders
     */
    public function getPurchaseOrdersPurchaseOrders()
    {
        return $this->purchaseOrdersPurchaseOrders;
    }

    /**
     * Set payTypePayType
     *
     * @param \AppBundle\Entity\PayType $payTypePayType
     *
     * @return Pay
     */
    public function setPayTypePayType(\AppBundle\Entity\PayType $payTypePayType)
    {
        $this->payTypePayType = $payTypePayType;

        return $this;
    }

    /**
     * Get payTypePayType
     *
     * @return \AppBundle\Entity\PayType
     */
    public function getPayTypePayType()
    {
        return $this->payTypePayType;
    }

    /**
     * Set payMethodPayMethod
     *
     * @param \AppBundle\Entity\PayMethod $payMethodPayMethod
     *
     * @return Pay
     */
    public function setPayMethodPayMethod(\AppBundle\Entity\PayMethod $payMethodPayMethod)
    {
        $this->payMethodPayMethod = $payMethodPayMethod;

        return $this;
    }

    /**
     * Get payMethodPayMethod
     *
     * @return \AppBundle\Entity\PayMethod
     */
    public function getPayMethodPayMethod()
    {
        return $this->payMethodPayMethod;
    }
}
