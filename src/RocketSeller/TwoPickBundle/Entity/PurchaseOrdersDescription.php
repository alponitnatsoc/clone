<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * PurchaseOrdersDescription
 *
 * @ORM\Table(name="purchase_orders_description", indexes={
 *      @ORM\Index(name="fk_purchase_orders_description_purchase_orders1", columns={"purchase_orders_id_purchase_orders"}),
 *      @ORM\Index(name="fk_purchase_orders_description_product1", columns={"product_id_product"}),
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
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="purchaseOrdersDescription")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     *
     */
    private $payrollPayroll;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", mappedBy="pila")
     */
    private $payrollsPila;

    /**
     * @ORM\OneToMany(targetEntity="Pay", mappedBy="purchaseOrdersDescription", cascade={"persist"})
     * @Exclude
     */
    private $payPay;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrders", inversedBy="purchaseOrderDescriptions", cascade={"persist"})
     * @ORM\JoinColumn(name="purchase_orders_id_purchase_orders", referencedColumnName="id_purchase_orders")
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
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $enlaceOperativoFileName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateToPay;
	
		/**
		 * -1 - Success
		 * -2 - Operations is not ok with this file
		 * Anything else, active transaction id
		 * @ORM\Column(type="integer", nullable=TRUE)
		 */
		private $uploadedFile;
	
		/**
		 * @ORM\ManyToMany(targetEntity="Transaction", cascade={"persist"})
		 * @ORM\JoinTable(name="purchase_orders_description_has_transactions",
		 *      joinColumns={ @ORM\JoinColumn(name="purchase_orders_description_id_purchase_orders_description", referencedColumnName="id_purchase_orders_description")},
		 *      inverseJoinColumns={@ORM\JoinColumn(name="transaction_id_transaction", referencedColumnName="id_transaction")}
		 *      )
		 */
		private $transactions;
	
		/** @var Document
		 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document", cascade={"persist"})
		 * @ORM\JoinColumns({
		 *     @ORM\JoinColumn(name="purchase_orders_description_id_document",referencedColumnName="id_document")
		 * })
		 */
		private $document;
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Prima
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Prima", mappedBy="purchaseOrdersDescriptionPurchaseOrdersDescription", cascade={"persist"})
     */
    private $prima;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Severances
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Severances", mappedBy="purchaseOrdersDescriptionPurchaseOrdersDescription", cascade={"persist"})
     */
    private $severance;

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
     * Add payrollsPila
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollsPila
     *
     * @return PurchaseOrdersDescription
     */
    public function addPayrollsPila(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollsPila)
    {
        $this->payrollsPila[] = $payrollsPila;
        $payrollsPila->setPila($this);
        return $this;
    }

    /**
     * Remove payrollsPila
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollsPila
     */
    public function removePayrollsPila(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollsPila)
    {
        $this->payrollsPila->removeElement($payrollsPila);
    }

    /**
     * Get payrollsPila
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayrollsPila()
    {
        return $this->payrollsPila;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrollsPila = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payPay = new \Doctrine\Common\Collections\ArrayCollection();
	      $this->transactions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set enlaceOperativoFileName
     *
     * @param string $enlaceOperativoFileName
     *
     * @return PurchaseOrdersDescription
     */
    public function setEnlaceOperativoFileName($enlaceOperativoFileName)
    {
        $this->enlaceOperativoFileName = $enlaceOperativoFileName;

        return $this;
    }

    /**
     * Get enlaceOperativoFileName
     *
     * @return string
     */
    public function getEnlaceOperativoFileName()
    {
        return $this->enlaceOperativoFileName;
    }

    /**
     * Set dateToPay
     *
     * @param \DateTime $dateToPay
     *
     * @return PurchaseOrdersDescription
     */
    public function setDateToPay($dateToPay)
    {
        $this->dateToPay = $dateToPay;

        return $this;
    }

    /**
     * Get dateToPay
     *
     * @return \DateTime
     */
    public function getDateToPay()
    {
        return $this->dateToPay;
    }

    /**
     * Get payMethod
     * @return null|PayMethod
     */
    public function getPayMethod()
    {
        if($this->getPayrollPayroll()!=null){
            return $this->getPayrollPayroll()->getContractContract()->getPayMethodPayMethod();
        }
        if($this->getPrima()!=null){
            return $this->getPrima()->getContractContract()->getPayMethodPayMethod();
        }
        return null;
    }/**
     * Get payment Status
     * @return null|int
     */
    public function getPaymentStatus()
    {
        if($this->getPurchaseOrdersStatus()!=null){
            return $this->getPurchaseOrdersStatus()->getIdNovoPay();
        }
        return null;
    }

    /**
     * Add transaction
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Transaction $transaction
     *
     * @return PurchaseOrdersDescription
     */
    public function addTransaction(\RocketSeller\TwoPickBundle\Entity\Transaction $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Transaction $transaction
     */
    public function removeTransaction(\RocketSeller\TwoPickBundle\Entity\Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set uploadedFile
     *
     * @param integer $uploadedFile
     *
     * @return PurchaseOrdersDescription
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }

    /**
     * Get uploadedFile
     *
     * @return integer
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Set document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $document
     *
     * @return PurchaseOrdersDescription
     */
    public function setDocument(\RocketSeller\TwoPickBundle\Entity\Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set prima
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Prima $prima
     *
     * @return PurchaseOrdersDescription
     */
    public function setPrima(\RocketSeller\TwoPickBundle\Entity\Prima $prima = null)
    {
        $this->prima = $prima;

        return $this;
    }

    /**
     * Get prima
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Prima
     */
    public function getPrima()
    {
        return $this->prima;
    }

    /**
     * Set severance
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Severances $severance
     *
     * @return PurchaseOrdersDescription
     */
    public function setSeverance(\RocketSeller\TwoPickBundle\Entity\Severances $severance = null)
    {
        $this->severance = $severance;

        return $this;
    }

    /**
     * Get severance
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Severances
     */
    public function getSeverance()
    {
        return $this->severance;
    }
}
