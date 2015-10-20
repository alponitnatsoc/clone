<?php

namespace RocketSeller\TwoPickBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPurchaseOrders;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_type_id_purchase_orders_type", referencedColumnName="id_purchase_orders_type")
     * })
     */
    private $purchaseOrdersTypePurchaseOrdersType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll")
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
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersTypePurchaseOrdersType(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType $purchaseOrdersTypePurchaseOrdersType)
    {
        $this->purchaseOrdersTypePurchaseOrdersType = $purchaseOrdersTypePurchaseOrdersType;

        return $this;
    }

    /**
     * Get purchaseOrdersTypePurchaseOrdersType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersType
     */
    public function getPurchaseOrdersTypePurchaseOrdersType()
    {
        return $this->purchaseOrdersTypePurchaseOrdersType;
    }

    /**
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PurchaseOrders
     */
    public function setPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll)
    {
        $this->payrollPayroll = $payrollPayroll;

        return $this;
    }

    /**
     * Get payrollPayroll
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Payroll
     */
    public function getPayrollPayroll()
    {
        return $this->payrollPayroll;
    }
}
