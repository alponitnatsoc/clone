<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractHasWorkplace
 *
 * @ORM\Table(name="contract_has_workplace", indexes={@ORM\Index(name="fk_contract_has_workplace_workplace1", columns={"workplace_id_workplace"}), @ORM\Index(name="IDX_62ABA61EFBFF1D01", columns={"contract_id_contract"})})
 * @ORM\Entity
 */
class ContractHasWorkplace
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract_has_workplace", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idContractHasWorkplace;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Contract
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="workplaces")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_id_contract", referencedColumnName="id_contract")
     * })
     */
    private $contractContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Workplace
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Workplace", inversedBy="workplaceHasContracts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workplace_id_workplace", referencedColumnName="id_workplace")
     * })
     */
    private $workplaceWorkplace;

    function __construct($contract, $workplace){
        $this->setContractContract($contract);
        $this->setWorkplaceWorkplace($workplace);
    }

    /**
     * Get idContractHasWorkplace
     *
     * @return integer
     */
    public function getIdContractHasWorkplace()
    {
        return $this->idContractHasWorkplace;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return ContractHasWorkplace
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
     * Set workplaceWorkplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Workplace $workplaceWorkplace
     *
     * @return ContractHasWorkplace
     */
    public function setWorkplaceWorkplace(\RocketSeller\TwoPickBundle\Entity\Workplace $workplaceWorkplace = null)
    {
        $this->workplaceWorkplace = $workplaceWorkplace;

        return $this;
    }

    /**
     * Get workplaceWorkplace
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Workplace
     */
    public function getWorkplaceWorkplace()
    {
        return $this->workplaceWorkplace;
    }
}
