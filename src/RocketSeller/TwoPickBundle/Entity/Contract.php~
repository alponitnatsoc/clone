<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contract
 *
 * @ORM\Table(name="contract", indexes={@ORM\Index(name="fk_contract_employer_has_employee1", columns={"employer_has_employee_id_employer_has_employee"}), @ORM\Index(name="fk_contract_contract_type1", columns={"contract_type_id_contract_type"})})
 * @ORM\Entity
 */
class Contract
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_has_employee_id_employer_has_employee", referencedColumnName="id_employer_has_employee")
     * })
     */
    private $employerHasEmployeeEmployerHasEmployee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ContractType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_type_id_contract_type", referencedColumnName="id_contract_type")
     * })
     */
    private $contractTypeContractType;

    /**
     * @ORM\OneToMany(targetEntity="Workplace", mappedBy="contractContract")
     */
    private $workplaces;


    /**
     * Set idContract
     *
     * @param integer $idContract
     *
     * @return Contract
     */
    public function setIdContract($idContract)
    {
        $this->idContract = $idContract;

        return $this;
    }

    /**
     * Get idContract
     *
     * @return integer
     */
    public function getIdContract()
    {
        return $this->idContract;
    }

    /**
     * Set employerHasEmployeeEmployerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee
     *
     * @return Contract
     */
    public function setEmployerHasEmployeeEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee)
    {
        $this->employerHasEmployeeEmployerHasEmployee = $employerHasEmployeeEmployerHasEmployee;

        return $this;
    }

    /**
     * Get employerHasEmployeeEmployerHasEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployeeEmployerHasEmployee()
    {
        return $this->employerHasEmployeeEmployerHasEmployee;
    }

    /**
     * Set contractTypeContractType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType
     *
     * @return Contract
     */
    public function setContractTypeContractType(\RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType)
    {
        $this->contractTypeContractType = $contractTypeContractType;

        return $this;
    }

    /**
     * Get contractTypeContractType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ContractType
     */
    public function getContractTypeContractType()
    {
        return $this->contractTypeContractType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add workplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Workplace $workplace
     *
     * @return Contract
     */
    public function addWorkplace(\RocketSeller\TwoPickBundle\Entity\Workplace $workplace)
    {
        $this->workplaces[] = $workplace;

        return $this;
    }

    /**
     * Remove workplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Workplace $workplace
     */
    public function removeWorkplace(\RocketSeller\TwoPickBundle\Entity\Workplace $workplace)
    {
        $this->workplaces->removeElement($workplace);
    }

    /**
     * Get workplaces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkplaces()
    {
        return $this->workplaces;
    }
}
