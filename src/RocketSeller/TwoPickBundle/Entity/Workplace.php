<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workplace
 *
 * @ORM\Table(name="workplace", indexes={@ORM\Index(name="fk_workplace_contract1", columns={"contract_id_contract"}), @ORM\Index(name="fk_workplace_employee1", columns={"employee_id_employee"})})
 * @ORM\Entity
 */
class Workplace
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_workplace", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idWorkplace;

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
     * Set idWorkplace
     *
     * @param integer $idWorkplace
     *
     * @return Workplace
     */
    public function setIdWorkplace($idWorkplace)
    {
        $this->idWorkplace = $idWorkplace;

        return $this;
    }

    /**
     * Get idWorkplace
     *
     * @return integer
     */
    public function getIdWorkplace()
    {
        return $this->idWorkplace;
    }

    /**
     * Set employeeEmployee
     *
     * @param \AppBundle\Entity\Employee $employeeEmployee
     *
     * @return Workplace
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
     * Set contractContract
     *
     * @param \AppBundle\Entity\Contract $contractContract
     *
     * @return Workplace
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
}
