<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liquidation
 *
 * @ORM\Table(name="liquidation")
 * @ORM\Entity
 */
class Liquidation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrders
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrders", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_purchase_order", referencedColumnName="id_purchase_orders")
     * })
     */
    private $idPurchaseOrder;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="liquidations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract", referencedColumnName="id_contract")
     * })
     */
    private $contract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\LiquidationType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\LiquidationType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="liquidation_type", referencedColumnName="id")
     * })
     */
    private $liquidationType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee", inversedBy="liquidations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_has_employee", referencedColumnName="id_employer_has_employee")
     * })
     */
    private $employerHasEmployee;

    /**
     * @ORM\OneToMany(targetEntity="LiquidationHasLiquidationReason", mappedBy="liquidation", cascade={"persist"})
     */
    private $liquidationReasons;

    /**
     * @ORM\Column(type="date", length=20, nullable=TRUE, name="last_work_day")
     */
    private $lastWorkDay;

    /**
     * @ORM\Column(type="float", nullable=TRUE)
     */
    private $cost;

    /**
     * @var integer
     *
     * @ORM\Column(name="days_to_liquidate", type="integer", nullable=TRUE)
     */
    private $daysToLiquidate;

    /**
     * @ORM\Column(name="detail_liquidation", type="text", nullable=TRUE)
     */
    private $detailLiquidation;

    /**
     * @var integer
     * @ORM\Column(name="period", type="integer", nullable=TRUE, length=100)
     */
    private $period;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idPurchaseOrder
     *
     * @param integer $idPurchaseOrder
     *
     * @return Liquidation
     */
    public function setIdPurchaseOrder($idPurchaseOrder)
    {
        $this->idPurchaseOrder = $idPurchaseOrder;

        return $this;
    }

    /**
     * Get idPurchaseOrder
     *
     * @return integer
     */
    public function getIdPurchaseOrder()
    {
        return $this->idPurchaseOrder;
    }

    /**
     * Set daysToLiquidate
     *
     * @param integer $daysToLiquidate
     *
     * @return Liquidation
     */
    public function setDaysToLiquidate($daysToLiquidate)
    {
        $this->daysToLiquidate = $daysToLiquidate;

        return $this;
    }

    /**
     * Get daysToLiquidate
     *
     * @return integer
     */
    public function getDaysToLiquidate()
    {
        return $this->daysToLiquidate;
    }

    /**
     * Set contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     *
     * @return Liquidation
     */
    public function setContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set liquidationType
     *
     * @param integer $liquidationType
     *
     * @return Liquidation
     */
    public function setLiquidationType($liquidationType)
    {
        $this->liquidationType = $liquidationType;

        return $this;
    }

    /**
     * Get liquidationType
     *
     * @return integer
     */
    public function getLiquidationType()
    {
        return $this->liquidationType;
    }

    /**
     * Set employerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee
     *
     * @return Liquidation
     */
    public function setEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee = null)
    {
        $this->employerHasEmployee = $employerHasEmployee;

        return $this;
    }

    /**
     * Get employerHasEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployee()
    {
        return $this->employerHasEmployee;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->liquidationReasons = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add liquidationReason
     *
     * @param \RocketSeller\TwoPickBundle\Entity\LiquidationHasLiquidationReason $liquidationReason
     *
     * @return Liquidation
     */
    public function addLiquidationReason(\RocketSeller\TwoPickBundle\Entity\LiquidationHasLiquidationReason $liquidationReason)
    {
        $this->liquidationReasons[] = $liquidationReason;

        return $this;
    }

    /**
     * Remove liquidationReason
     *
     * @param \RocketSeller\TwoPickBundle\Entity\LiquidationHasLiquidationReason $liquidationReason
     */
    public function removeLiquidationReason(\RocketSeller\TwoPickBundle\Entity\LiquidationHasLiquidationReason $liquidationReason)
    {
        $this->liquidationReasons->removeElement($liquidationReason);
    }

    /**
     * Get liquidationReasons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLiquidationReasons()
    {
        return $this->liquidationReasons;
    }

    /**
     * Set lastWorkDay
     *
     * @param \DateTime $lastWorkDay
     *
     * @return Liquidation
     */
    public function setLastWorkDay($lastWorkDay)
    {
        $this->lastWorkDay = $lastWorkDay;

        return $this;
    }

    /**
     * Get lastWorkDay
     *
     * @return \DateTime
     */
    public function getLastWorkDay()
    {
        return $this->lastWorkDay;
    }

    /**
     * Set cost
     *
     * @param float $cost
     *
     * @return Liquidation
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set detailLiquidation
     *
     * @param string $detailLiquidation
     *
     * @return Liquidation
     */
    public function setDetailLiquidation($detailLiquidation)
    {
        $this->detailLiquidation = $detailLiquidation;

        return $this;
    }

    /**
     * Get detailLiquidation
     *
     * @return string
     */
    public function getDetailLiquidation()
    {
        return $this->detailLiquidation;
    }

    /**
     * Set period
     *
     * @param integer $period
     *
     * @return Liquidation
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period
     *
     * @return integer
     */
    public function getPeriod()
    {
        return $this->period;
    }
}
