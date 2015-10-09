<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployerHasEmployee
 *
 * @ORM\Table(name="employer_has_employee", indexes={@ORM\Index(name="fk_employer_has_employee_employer1", columns={"employer_id_employer"}), @ORM\Index(name="fk_employer_has_employee_employee1", columns={"employee_id_employee"})})
 * @ORM\Entity
 */
class EmployerHasEmployee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employer_has_employee", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEmployerHasEmployee;

    /**
     * @var \AppBundle\Entity\Employer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Employer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;

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
     * Set idEmployerHasEmployee
     *
     * @param integer $idEmployerHasEmployee
     *
     * @return EmployerHasEmployee
     */
    public function setIdEmployerHasEmployee($idEmployerHasEmployee)
    {
        $this->idEmployerHasEmployee = $idEmployerHasEmployee;

        return $this;
    }

    /**
     * Get idEmployerHasEmployee
     *
     * @return integer
     */
    public function getIdEmployerHasEmployee()
    {
        return $this->idEmployerHasEmployee;
    }

    /**
     * Set employerEmployer
     *
     * @param \AppBundle\Entity\Employer $employerEmployer
     *
     * @return EmployerHasEmployee
     */
    public function setEmployerEmployer(\AppBundle\Entity\Employer $employerEmployer)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \AppBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }

    /**
     * Set employeeEmployee
     *
     * @param \AppBundle\Entity\Employee $employeeEmployee
     *
     * @return EmployerHasEmployee
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
}
