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
     * @var integer
     *
     * @ORM\Column(name="days_to_liquidate", type="integer")
     */
    private $daysToLiquidate;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_contract", referencedColumnName="id_contract")
     * })
     */
    private $idContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\LiquidationType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\LiquidationType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="liquidation_type", referencedColumnName="id")
     * })
     */
    private $liquidationType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_employer", referencedColumnName="id_employer")
     * })
     */
    private $idEmployer;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employee
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employee", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_employee", referencedColumnName="id_employee")
     * })
     */
    private $idEmployee;


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
     * Set idContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $idContract
     *
     * @return Liquidation
     */
    public function setIdContract(\RocketSeller\TwoPickBundle\Entity\Contract $idContract = null)
    {
        $this->idContract = $idContract;

        return $this;
    }

    /**
     * Get idContract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getIdContract()
    {
        return $this->idContract;
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
     * Set idEmployer
     *
     * @param integer $idEmployer
     *
     * @return Liquidation
     */
    public function setIdEmployer($idEmployer)
    {
        $this->idEmployer = $idEmployer;

        return $this;
    }

    /**
     * Get idEmployer
     *
     * @return integer
     */
    public function getIdEmployer()
    {
        return $this->idEmployer;
    }

    /**
     * Set idEmployee
     *
     * @param integer $idEmployee
     *
     * @return Liquidation
     */
    public function setIdEmployee($idEmployee)
    {
        $this->idEmployee = $idEmployee;

        return $this;
    }

    /**
     * Get idEmployee
     *
     * @return integer
     */
    public function getIdEmployee()
    {
        return $this->idEmployee;
    }
}
