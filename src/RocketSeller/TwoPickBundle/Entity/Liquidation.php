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
}
