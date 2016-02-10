<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersDescription
 *
 * @ORM\Table(name="purchase_orders_description", indexes={@ORM\Index(name="fk_purchase_orders_description_purchase_orders1", columns={"purchase_orders_id_purchase_orders"}), @ORM\Index(name="fk_purchase_orders_description_product1", columns={"product_id_product"}), @ORM\Index(name="fk_purchase_orders_description_tax1", columns={"tax_id_tax"})})
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescriptionRepository")
 */
class PurchaseOrdersDescription
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_purchase_orders_description", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPurchaseOrdersDescription;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="purchaseOrdersDescription")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     *
     */
    private $payrollPayroll;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Tax
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Tax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tax_id_tax", referencedColumnName="id_tax")
     * })
     */
    private $taxTax;

    /**
     * @ORM\OneToMany(targetEntity="Pay", mappedBy="purchaseOrdersDescription", cascade={"persist"})
     */
    private $payPay;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrders", inversedBy="purchaseOrderDescriptions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_id_purchase_orders", referencedColumnName="id_purchase_orders")
     * })
     */
    private $purchaseOrdersPurchaseOrders;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatusPurchaseOrdersStatus;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Product
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id_product", referencedColumnName="id_product")
     * })
     */
    private $productProduct;

    /**
     * @ORM\Column(type="text", nullable=TRUE)
     */
    private $description;
    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $value;

    /**
     * Set idPurchaseOrdersDescription
     *
     * @param integer $idPurchaseOrdersDescription
     *
     * @return PurchaseOrdersDescription
     */
    public function setIdPurchaseOrdersDescription($idPurchaseOrdersDescription)
    {
        $this->idPurchaseOrdersDescription = $idPurchaseOrdersDescription;

        return $this;
    }

    /**
     * Get idPurchaseOrdersDescription
     *
     * @return integer
     */
    public function getIdPurchaseOrdersDescription()
    {
        return $this->idPurchaseOrdersDescription;
    }

    /**
     * Set taxTax
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Tax $taxTax
     *
     * @return PurchaseOrdersDescription
     */
    public function setTaxTax(\RocketSeller\TwoPickBundle\Entity\Tax $taxTax)
    {
        $this->taxTax = $taxTax;

        return $this;
    }

    /**
     * Get taxTax
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Tax
     */
    public function getTaxTax()
    {
        return $this->taxTax;
    }

    /**
     * Set purchaseOrdersPurchaseOrders
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrdersPurchaseOrders
     *
     * @return PurchaseOrdersDescription
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
     * Set productProduct
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Product $productProduct
     *
     * @return PurchaseOrdersDescription
     */
    public function setProductProduct(\RocketSeller\TwoPickBundle\Entity\Product $productProduct)
    {
        $this->productProduct = $productProduct;

        return $this;
    }

    /**
     * Get productProduct
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Product
     */
    public function getProductProduct()
    {
        return $this->productProduct;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return PurchaseOrdersDescription
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payPay = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add payPay
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Pay $payPay
     *
     * @return PurchaseOrdersDescription
     */
    public function addPayPay(\RocketSeller\TwoPickBundle\Entity\Pay $payPay)
    {
        $this->payPay[] = $payPay;

        return $this;
    }

    /**
     * Remove payPay
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Pay $payPay
     */
    public function removePayPay(\RocketSeller\TwoPickBundle\Entity\Pay $payPay)
    {
        $this->payPay->removeElement($payPay);
    }

    /**
     * Get payPay
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayPay()
    {
        return $this->payPay;
    }

    /**
     * Set purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatusPurchaseOrdersStatus
     *
     * @return PurchaseOrdersDescription
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
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PurchaseOrdersDescription
     */
    public function setPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll = null)
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
     * Set value
     *
     * @param float $value
     *
     * @return PurchaseOrdersDescription
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
