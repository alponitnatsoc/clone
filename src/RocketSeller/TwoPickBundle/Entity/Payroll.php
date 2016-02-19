<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $year;

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
     */
    private $contractContract;

    /**
     * @ORM\OneToMany(targetEntity="PayrollDetail", mappedBy="payrollPayroll", cascade={"persist"})
     */
    private $payrollDetails;

    /**
     * @ORM\OneToMany(targetEntity="Novelty", mappedBy="payrollPayroll", cascade={"persist"})
     */
    private $novelties;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseOrdersDescription", inversedBy="payrollPayroll", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payrollPayroll", referencedColumnName="id_purchase_orders_description")
     * })
     */
    private $purchaseOrdersDescription;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrollDetails = new \Doctrine\Common\Collections\ArrayCollection();
        $this->novelties = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set purchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescription
     *
     * @return Payroll
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

}
