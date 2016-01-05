<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractHasBenefits
 *
 * @ORM\Table(name="contract_has_benefits", indexes={@ORM\Index(name="fk_contract_has_benefits_benefits1", columns={"benefits_id_benefits"}), @ORM\Index(name="IDX_62ABA61EFBFF1D01", columns={"contract_id_contract"})})
 * @ORM\Entity
 */
class ContractHasBenefits
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract_has_benefits", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idContractHasBenefits;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="benefits")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     */
    private $contractContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Benefits
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Benefits")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="benefits_id_benefits", referencedColumnName="id_benefits")
     * })
     */
    private $benefitsBenefits;

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $amount;
    /**
     * @ORM\Column( type="integer")
     */
    private $periodicity;


    function __construct($contract=null, $benefit=null){
        if ($contract) {
            $this->setContractContract($contract);
        }
        if ($benefit) {
            $this->setBenefitsBenefits($benefit);
        }
        
    }
    /**
     * Set idContractHasBenefits
     *
     * @param integer $idContractHasBenefits
     *
     * @return ContractHasBenefits
     */
    public function setIdContractHasBenefits($idContractHasBenefits)
    {
        $this->idContractHasBenefits = $idContractHasBenefits;

        return $this;
    }

    /**
     * Get idContractHasBenefits
     *
     * @return integer
     */
    public function getIdContractHasBenefits()
    {
        return $this->idContractHasBenefits;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return ContractHasBenefits
     */
    public function setContractContract(\RocketSeller\TwoPickBundle\Entity\Contract $contractContract)
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
     * Set benefitsBenefits
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Benefits $benefitsBenefits
     *
     * @return ContractHasBenefits
     */
    public function setBenefitsBenefits(\RocketSeller\TwoPickBundle\Entity\Benefits $benefitsBenefits)
    {
        $this->benefitsBenefits = $benefitsBenefits;

        return $this;
    }

    /**
     * Get benefitsBenefits
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Benefits
     */
    public function getBenefitsBenefits()
    {
        return $this->benefitsBenefits;
    }

    /**
     * Set periodicity
     *
     * @param integer $periodicity
     *
     * @return ContractHasBenefits
     */
    public function setPeriodicity($periodicity)
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    /**
     * Get periodicity
     *
     * @return integer
     */
    public function getPeriodicity()
    {
        return $this->periodicity;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return ContractHasBenefits
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
