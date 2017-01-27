<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * Prima
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Severances
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_severances", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idSeverances;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;
    /**
     * @ORM\ManyToMany(targetEntity="Contract", cascade={"persist"},inversedBy="severances")
     * @ORM\JoinTable(name="contract_has_severances",
     *      joinColumns={ @ORM\JoinColumn(name="severances_id_severances", referencedColumnName="id_severances")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")}
     *      )
     */
    private $contracts;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription", inversedBy="severance", cascade={"persist"})
     * @ORM\JoinColumn(name="purchase_orders_description_id_purchase_orders_description", referencedColumnName="id_purchase_orders_description")
     */
    private $purchaseOrdersDescriptionPurchaseOrdersDescription;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $month;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payslip_id_payslip", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $payslip;

    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $days;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idSeverances
     *
     * @return integer
     */
    public function getIdSeverances()
    {
        return $this->idSeverances;
    }

    /**
     * Set value
     *
     * @param float $value
     *
     * @return Severances
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
     * Set year
     *
     * @param string $year
     *
     * @return Severances
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
     * @return Severances
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
     * Set days
     *
     * @param integer $days
     *
     * @return Severances
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return integer
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Add contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     *
     * @return Severances
     */
    public function addContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     */
    public function removeContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Set purchaseOrdersDescriptionPurchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescriptionPurchaseOrdersDescription
     *
     * @return Severances
     */
    public function setPurchaseOrdersDescriptionPurchaseOrdersDescription(\RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescriptionPurchaseOrdersDescription = null)
    {
        $this->purchaseOrdersDescriptionPurchaseOrdersDescription = $purchaseOrdersDescriptionPurchaseOrdersDescription;

        return $this;
    }

    /**
     * Get purchaseOrdersDescriptionPurchaseOrdersDescription
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     */
    public function getPurchaseOrdersDescriptionPurchaseOrdersDescription()
    {
        return $this->purchaseOrdersDescriptionPurchaseOrdersDescription;
    }

    /**
     * Set payslip
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $payslip
     *
     * @return Severances
     */
    public function setPayslip(\RocketSeller\TwoPickBundle\Entity\Document $payslip = null)
    {
        $this->payslip = $payslip;

        return $this;
    }

    /**
     * Get payslip
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getPayslip()
    {
        return $this->payslip;
    }
}
