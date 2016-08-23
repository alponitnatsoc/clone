<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Payroll
 *
 * @ORM\Table(name="payroll", indexes={@ORM\Index(name="fk_payroll_contract1", columns={"contract_id_contract"})})
 * @ORM\Entity
 */
class Payroll
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_payroll", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPayroll;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $period;

    /**
     * 0 not procesed
     * 1 already in purchase order
     * 2 paid
     * @ORM\Column(type="smallint" , nullable=TRUE)
     */
    private $paid=0;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $year;
    /**
     * @ORM\Column(type="boolean",  nullable=TRUE)
     */
    private $daysSent;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $month;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="payrolls")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     *
     */
    private $contractContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription", inversedBy="payrollsPila", cascade={"persist"} )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pila", referencedColumnName="id_purchase_orders_description")
     * })
     */
    private $pila;

    /**
     * @ORM\OneToMany(targetEntity="PayrollDetail", mappedBy="payrollPayroll", cascade={"persist"})
     * @Exclude
     */
    private $payrollDetails;

    /**
     * @ORM\OneToMany(targetEntity="Novelty", mappedBy="payrollPayroll", cascade={"persist"})
     */
    private $novelties;

    /**
     * @ORM\OneToMany(targetEntity="Novelty", mappedBy="sqlPayrollPayroll", cascade={"persist"})
     */
    private $sqlNovelties;

    /**
     * @ORM\OneToMany(targetEntity="PilaDetail", mappedBy="payrollPayroll", cascade={"persist"})
     */
    private $pilaDetails;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseOrdersDescription", mappedBy="payrollPayroll", cascade={"persist"})
     */
    private $purchaseOrdersDescription;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_id_document", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $signature;

    /**
     * Get idPayroll
     *
     * @return integer
     */
    public function getIdPayroll()
    {
        return $this->idPayroll;
    }

    /**
     * Set period
     *
     * @param string $period
     *
     * @return Payroll
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set year
     *
     * @param string $year
     *
     * @return Payroll
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param string $month
     *
     * @return Payroll
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return Payroll
     */
    public function setContractContract(\RocketSeller\TwoPickBundle\Entity\Contract $contractContract = null)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }

    /**
     * Add payrollDetail
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetail
     *
     * @return Payroll
     */
    public function addPayrollDetail(\RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetail)
    {
        $this->payrollDetails[] = $payrollDetail;

        return $this;
    }

    /**
     * Remove payrollDetail
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetail
     */
    public function removePayrollDetail(\RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetail)
    {
        $this->payrollDetails->removeElement($payrollDetail);
    }

    /**
     * Get payrollDetails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayrollDetails()
    {
        return $this->payrollDetails;
    }

    /**
     * Add novelty
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Novelty $novelty
     *
     * @return Payroll
     */
    public function addNovelty(\RocketSeller\TwoPickBundle\Entity\Novelty $novelty)
    {
        $novelty->getPayrollDetailPayrollDetail()->setPayrollPayroll($this);
        $novelty->setPayrollPayroll($this);
        $this->novelties[] = $novelty;

        return $this;
    }

    /**
     * Remove novelty
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Novelty $novelty
     */
    public function removeNovelty(\RocketSeller\TwoPickBundle\Entity\Novelty $novelty)
    {
        $this->novelties->removeElement($novelty);
    }

    /**
     * Get novelties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNovelties()
    {
        return $this->novelties;
    }


    /**
     * Set daysSent
     *
     * @param boolean $daysSent
     *
     * @return Payroll
     */
    public function setDaysSent($daysSent)
    {
        $this->daysSent = $daysSent;

        return $this;
    }

    /**
     * Get daysSent
     *
     * @return boolean
     */
    public function getDaysSent()
    {
        return $this->daysSent;
    }

    /**
     * Set paid
     *
     * @param integer $paid
     *
     * @return Payroll
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return integer
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set pila
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $pila
     *
     * @return Payroll
     */
    public function setPila(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $pila = null)
    {
        $this->pila = $pila;

        return $this;
    }

    /**
     * Get pila
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     */
    public function getPila()
    {
        return $this->pila;
    }

    /**
     * Add purchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription
     *
     * @return Payroll
     */
    public function addPurchaseOrdersDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription)
    {
        $this->purchaseOrdersDescription[] = $purchaseOrdersDescription;

        return $this;
    }

    /**
     * Remove purchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription
     */
    public function removePurchaseOrdersDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription)
    {
        $this->purchaseOrdersDescription->removeElement($purchaseOrdersDescription);
    }

    /**
     * Get purchaseOrdersDescription
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchaseOrdersDescription()
    {
        return $this->purchaseOrdersDescription;
    }

    /**
     * Add sqlNovelty
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Novelty $sqlNovelty
     *
     * @return Payroll
     */
    public function addSqlNovelty(\RocketSeller\TwoPickBundle\Entity\Novelty $sqlNovelty)
    {
        $this->sqlNovelties[] = $sqlNovelty;

        return $this;
    }

    /**
     * Remove sqlNovelty
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Novelty $sqlNovelty
     */
    public function removeSqlNovelty(\RocketSeller\TwoPickBundle\Entity\Novelty $sqlNovelty)
    {
        $this->sqlNovelties->removeElement($sqlNovelty);
    }

    /**
     * Get sqlNovelties
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSqlNovelties()
    {
        return $this->sqlNovelties;
    }



    /**
     * Set signature
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $signature
     *
     * @return Payroll
     */
    public function setSignature(\RocketSeller\TwoPickBundle\Entity\Document $signature = null)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getSignature()
    {
        return $this->signature;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrollDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->novelties = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sqlNovelties = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pilaDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->purchaseOrdersDescription = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Add pilaDetail
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PilaDetail $pilaDetail
     *
     * @return Payroll
     */
    public function addPilaDetail(\RocketSeller\TwoPickBundle\Entity\PilaDetail $pilaDetail)
    {
        $pilaDetail->setPayrollPayroll($this);
        $this->pilaDetails[] = $pilaDetail;

        return $this;
    }

    /**
     * Remove pilaDetail
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PilaDetail $pilaDetail
     */
    public function removePilaDetail(\RocketSeller\TwoPickBundle\Entity\PilaDetail $pilaDetail)
    {
        $this->pilaDetails->removeElement($pilaDetail);
    }

    /**
     * Get pilaDetails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPilaDetails()
    {
        return $this->pilaDetails;
    }
}
