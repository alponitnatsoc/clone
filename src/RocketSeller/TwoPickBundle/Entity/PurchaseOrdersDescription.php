<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseOrdersDescription
 *
 * @ORM\Table(name="purchase_orders_description", indexes={
 *      @ORM\Index(name="fk_purchase_orders_description_purchase_orders1", columns={"purchase_orders_id_purchase_orders"}), 
 *      @ORM\Index(name="fk_purchase_orders_description_product1", columns={"product_id_product"}), 
 *      @ORM\Index(name="fk_purchase_orders_description_tax1", columns={"tax_id_tax"})      
 *  })
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
     * @var integer
     *
     * @ORM\Column(type="integer", nullable = TRUE)
     */
    private $payMethodId;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="purchaseOrdersDescription")
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
    private $purchaseOrders;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_orders_status_id_purchase_orders_status", referencedColumnName="id_purchase_orders_status")
     * })
     */
    private $purchaseOrdersStatus;

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
     * Constructor
     */
    public function __construct()
    {
        $this->payPay = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set taxTax
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Tax $taxTax
     *
     * @return PurchaseOrdersDescription
     */
    public function setTaxTax(\RocketSeller\TwoPickBundle\Entity\Tax $taxTax = null)
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
     * Set purchaseOrders
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrders
     *
     * @return PurchaseOrdersDescription
     */
    public function setPurchaseOrders(\RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrders = null)
    {
        $this->purchaseOrders = $purchaseOrders;

        return $this;
    }

    /**
     * Get purchaseOrders
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     */
    public function getPurchaseOrders()
    {
        return $this->purchaseOrders;
    }

    /**
     * Set purchaseOrdersStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatus
     *
     * @return PurchaseOrdersDescription
     */
    public function setPurchaseOrdersStatus(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus $purchaseOrdersStatus = null)
    {
        $this->purchaseOrdersStatus = $purchaseOrdersStatus;

        return $this;
    }

    /**
     * Get purchaseOrdersStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus
     */
    public function getPurchaseOrdersStatus()
    {
        return $this->purchaseOrdersStatus;
    }

    /**
     * Set productProduct
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Product $productProduct
     *
     * @return PurchaseOrdersDescription
     */
    public function setProductProduct(\RocketSeller\TwoPickBundle\Entity\Product $productProduct = null)
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
     * Add payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PurchaseOrdersDescription
     */
    public function addPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll)
    {
        $this->payrollPayroll[] = $payrollPayroll;

        return $this;
    }

    /**
     * Remove payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     */
    public function removePayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll)
    {
        $this->payrollPayroll->removeElement($payrollPayroll);
    }


    /**
     * Set payMethodId
     *
     * @param integer $payMethodId
     *
     * @return PurchaseOrders
     */
    public function setPayMethodId($payMethodId)
    {
        $this->payMethodId = $payMethodId;

        return $this;
    }

    /**
     * Get payMethodId
     *
     * @return integer
     */
    public function getPayMethodId()
    {
        return $this->payMethodId;
    }

}
