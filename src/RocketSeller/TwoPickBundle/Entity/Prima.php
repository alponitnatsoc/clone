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
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payslip_id_payslip", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $payslip;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="signature_id_signature", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $signature;


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
     * Set payslip
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $payslip
     *
     * @return Prima
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

    /**
     * Set signature
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $signature
     *
     * @return Prima
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
}
