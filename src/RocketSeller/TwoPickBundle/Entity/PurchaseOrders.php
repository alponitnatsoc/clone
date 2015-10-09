<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrders
 *
 * @ORM\Table(name="purchase_orders", indexes={@ORM\Index(name="fk_purchase_orders_purchase_orders_type1", columns={"purchase_orders_type_id_purchase_orders_type"}), @ORM\Index(name="fk_purchase_orders_payroll1", columns={"payroll_id_payroll"})})
 * @ORM\Entity
 */
class PurchaseOrders
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPurchaseOrders;

    /**
     * @var \AppBundle\Entity\PurchaseOrdersType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PurchaseOrdersType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_type_id_purchase_orders_type", referencedColumnName="id_purchase_orders_type")
     * })
     */
    private $purchaseOrdersTypePurchaseOrdersType;

    /**
     * @var \AppBundle\Entity\Payroll
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Payroll")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;



    /**
     * Set idPurchaseOrders
     *
     * @param integer $idPurchaseOrders
     *
     * @return PurchaseOrders
     */
    public function setIdPurchaseOrders($idPurchaseOrders)
    {
        $this->idPurchaseOrders = $idPurchaseOrders;

        return $this;
    }

    /**
     * Get idPurchaseOrders
     *
     * @return integer
     */
    public function getIdPurchaseOrders()
    {
        return $this->idPurchaseOrders;
    }

    /**
     * Set purchaseOrdersTypePurchaseOrdersType
     *
     * @param \AppBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersTypePurchaseOrdersType(\AppBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType)
    {
        $this->purchaseOrdersTypePurchaseOrdersType = $purchaseOrdersTypePurchaseOrdersType;

        return $this;
    }

    /**
     * Get purchaseOrdersTypePurchaseOrdersType
     *
     * @return \AppBundle\Entity\PurchaseOrdersType
     */
    public function getPurchaseOrdersTypePurchaseOrdersType()
    {
        return $this->purchaseOrdersTypePurchaseOrdersType;
    }

    /**
     * Set payrollPayroll
     *
     * @param \AppBundle\Entity\Payroll $payrollPayroll
     *
     * @return PurchaseOrders
     */
    public function setPayrollPayroll(\AppBundle\Entity\Payroll $payrollPayroll)
    {
        $this->payrollPayroll = $payrollPayroll;

        return $this;
    }

    /**
     * Get payrollPayroll
     *
     * @return \AppBundle\Entity\Payroll
     */
    public function getPayrollPayroll()
    {
        return $this->payrollPayroll;
    }
}
