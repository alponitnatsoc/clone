<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
* Endowment
*
* @ORM\Table(name="supply", indexes={@ORM\Index(name="fk_supply_contract", columns={"contract_id_contract"})})
* @ORM\Entity
*/
class Supply
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_supply", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idSupply;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $month;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $year;

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
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\OneToOne(targetEntity="Contract", inversedBy="supplies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     *
     */
    private $contractContract;

    /**
     * Set month
     *
     * @param string $month
     *
     * @return Supply
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
     * Set year
     *
     * @param string $year
     *
     * @return Supply
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
     * Set payslip
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $payslip
     *
     * @return Supply
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
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return Supply
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
     * Get idSupply
     *
     * @return integer
     */
    public function getIdSupply()
    {
        return $this->idSupply;
    }
}
