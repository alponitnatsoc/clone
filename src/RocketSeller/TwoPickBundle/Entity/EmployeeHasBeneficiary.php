<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeeHasBeneficiary
 *
 * @ORM\Table(name="employee_has_beneficiary", indexes={@ORM\Index(name="fk_employee_has_beneficiary_employee1", columns={"employee_id_employee"}), @ORM\Index(name="fk_employee_has_beneficiary_beneficiary1", columns={"beneficiary_id_beneficiary"}), @ORM\Index(name="fk_employee_has_beneficiary_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class EmployeeHasBeneficiary
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employee_has_beneficiary", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEmployeeHasBeneficiary;

    /**
     * @var \AppBundle\Entity\Entity
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Entity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \AppBundle\Entity\Employee
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Employee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_id_employee", referencedColumnName="id_employee")
     * })
     */
    private $employeeEmployee;

    /**
     * @var \AppBundle\Entity\Beneficiary
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Beneficiary")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="beneficiary_id_beneficiary", referencedColumnName="id_beneficiary")
     * })
     */
    private $beneficiaryBeneficiary;



    /**
     * Set idEmployeeHasBeneficiary
     *
     * @param integer $idEmployeeHasBeneficiary
     *
     * @return EmployeeHasBeneficiary
     */
    public function setIdEmployeeHasBeneficiary($idEmployeeHasBeneficiary)
    {
        $this->idEmployeeHasBeneficiary = $idEmployeeHasBeneficiary;

        return $this;
    }

    /**
     * Get idEmployeeHasBeneficiary
     *
     * @return integer
     */
    public function getIdEmployeeHasBeneficiary()
    {
        return $this->idEmployeeHasBeneficiary;
    }

    /**
     * Set entityEntity
     *
     * @param \AppBundle\Entity\Entity $entityEntity
     *
     * @return EmployeeHasBeneficiary
     */
    public function setEntityEntity(\AppBundle\Entity\Entity $entityEntity)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \AppBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
    }

    /**
     * Set employeeEmployee
     *
     * @param \AppBundle\Entity\Employee $employeeEmployee
     *
     * @return EmployeeHasBeneficiary
     */
    public function setEmployeeEmployee(\AppBundle\Entity\Employee $employeeEmployee)
    {
        $this->employeeEmployee = $employeeEmployee;

        return $this;
    }

    /**
     * Get employeeEmployee
     *
     * @return \AppBundle\Entity\Employee
     */
    public function getEmployeeEmployee()
    {
        return $this->employeeEmployee;
    }

    /**
     * Set beneficiaryBeneficiary
     *
     * @param \AppBundle\Entity\Beneficiary $beneficiaryBeneficiary
     *
     * @return EmployeeHasBeneficiary
     */
    public function setBeneficiaryBeneficiary(\AppBundle\Entity\Beneficiary $beneficiaryBeneficiary)
    {
        $this->beneficiaryBeneficiary = $beneficiaryBeneficiary;

        return $this;
    }

    /**
     * Get beneficiaryBeneficiary
     *
     * @return \AppBundle\Entity\Beneficiary
     */
    public function getBeneficiaryBeneficiary()
    {
        return $this->beneficiaryBeneficiary;
    }
}
