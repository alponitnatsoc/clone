<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idContractHasBenefits;

    /**
     * @var \AppBundle\Entity\Contract
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Contract")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     */
    private $contractContract;

    /**
     * @var \AppBundle\Entity\Benefits
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Benefits")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="benefits_id_benefits", referencedColumnName="id_benefits")
     * })
     */
    private $benefitsBenefits;



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
     * @param \AppBundle\Entity\Contract $contractContract
     *
     * @return ContractHasBenefits
     */
    public function setContractContract(\AppBundle\Entity\Contract $contractContract)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \AppBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }

    /**
     * Set benefitsBenefits
     *
     * @param \AppBundle\Entity\Benefits $benefitsBenefits
     *
     * @return ContractHasBenefits
     */
    public function setBenefitsBenefits(\AppBundle\Entity\Benefits $benefitsBenefits)
    {
        $this->benefitsBenefits = $benefitsBenefits;

        return $this;
    }

    /**
     * Get benefitsBenefits
     *
     * @return \AppBundle\Entity\Benefits
     */
    public function getBenefitsBenefits()
    {
        return $this->benefitsBenefits;
    }
}
