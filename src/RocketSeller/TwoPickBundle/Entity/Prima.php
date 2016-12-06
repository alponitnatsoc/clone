<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prima
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Prima
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_prima", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPrima;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column( type="float")
     */
    private $transportAid;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract" , inversedBy="primas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     *
     */
    private $contractContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription", inversedBy="prima", cascade={"persist"})
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
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $worked;

    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $notWorked;

    /**
     * @ORM\Column(type="date", nullable=TRUE)
     */
    private $dateStart;

    /**
     * @ORM\Column(type="date", nullable=TRUE)
     */
    private $dateEnd;


    /**
     * Get idPrima
     *
     * @return integer
     */
    public function getIdPrima()
    {
        return $this->idPrima;
    }

    /**
     * Set value
     *
     * @param float $value
     *
     * @return Prima
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
     * @return Prima
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
     * @return Prima
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
     * @return Prima
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
     * Set purchaseOrdersDescriptionPurchaseOrdersDescription
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription $purchaseOrdersDescriptionPurchaseOrdersDescription
     *
     * @return Prima
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
     * Set transportAid
     *
     * @param float $transportAid
     *
     * @return Prima
     */
    public function setTransportAid($transportAid)
    {
        $this->transportAid = $transportAid;

        return $this;
    }

    /**
     * Get transportAid
     *
     * @return float
     */
    public function getTransportAid()
    {
        return $this->transportAid;
    }

    /**
     * Set worked
     *
     * @param integer $worked
     *
     * @return Prima
     */
    public function setWorked($worked)
    {
        $this->worked = $worked;

        return $this;
    }

    /**
     * Get worked
     *
     * @return integer
     */
    public function getWorked()
    {
        return $this->worked;
    }

    /**
     * Set notWorked
     *
     * @param integer $notWorked
     *
     * @return Prima
     */
    public function setNotWorked($notWorked)
    {
        $this->notWorked = $notWorked;

        return $this;
    }

    /**
     * Get notWorked
     *
     * @return integer
     */
    public function getNotWorked()
    {
        return $this->notWorked;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Prima
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Prima
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }
}
