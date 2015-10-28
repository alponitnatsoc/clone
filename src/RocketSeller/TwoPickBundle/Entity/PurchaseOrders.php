<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrders
 *
 * @ORM\Table(name="purchase_orders", indexes={@ORM\Index(name="fk_purchase_orders_purchase_orders_type1", columns={"purchase_orders_type_id_purchase_orders_type"}), @ORM\Index(name="fk_purchase_orders_payroll1", columns={"payroll_id_payroll"})})
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersRepository")
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
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="purchaseOrders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;


    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatusPurchaseOrdersStatus;

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

    /**
     * Set purchaseOrdersTypePurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersTypePurchaseOrdersStatus
     *
     * @return PurchaseOrders
     */
    public function setPurchaseOrdersTypePurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersTypePurchaseOrdersStatus = null)
    {
        $this->purchaseOrdersTypePurchaseOrdersStatus = $purchaseOrdersTypePurchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersTypePurchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersTypePurchaseOrdersStatus()
    {
        return $this->purchaseOrdersTypePurchaseOrdersStatus;
    }

    /**
     * Set purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return PurchaseOrders
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
}
