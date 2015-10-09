<?php

namespace AppBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idContract;

    /**
     * @var \AppBundle\Entity\EmployerHasEmployee
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\EmployerHasEmployee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_has_employee_id_employer_has_employee", referencedColumnName="id_employer_has_employee")
     * })
     */
    private $employerHasEmployeeEmployerHasEmployee;

    /**
     * @var \AppBundle\Entity\ContractType
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_type_id_contract_type", referencedColumnName="id_contract_type")
     * })
     */
    private $contractTypeContractType;



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
     * @param \AppBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee
     *
     * @return Contract
     */
    public function setEmployerHasEmployeeEmployerHasEmployee(\AppBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee)
    {
        $this->employerHasEmployeeEmployerHasEmployee = $employerHasEmployeeEmployerHasEmployee;

        return $this;
    }

    /**
     * Get employerHasEmployeeEmployerHasEmployee
     *
     * @return \AppBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployeeEmployerHasEmployee()
    {
        return $this->employerHasEmployeeEmployerHasEmployee;
    }

    /**
     * Set contractTypeContractType
     *
     * @param \AppBundle\Entity\ContractType $contractTypeContractType
     *
     * @return Contract
     */
    public function setContractTypeContractType(\AppBundle\Entity\ContractType $contractTypeContractType)
    {
        $this->contractTypeContractType = $contractTypeContractType;

        return $this;
    }

    /**
     * Get contractTypeContractType
     *
     * @return \AppBundle\Entity\ContractType
     */
    public function getContractTypeContractType()
    {
        return $this->contractTypeContractType;
    }
}
