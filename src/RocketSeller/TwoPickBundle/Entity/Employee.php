<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employee
 *
 * @ORM\Table(name="employee", indexes={@ORM\Index(name="fk_employee_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class Employee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employee", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEmployee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="employee", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $civilStatus;

    /**
     * @ORM\OneToMany(targetEntity="EmployerHasEmployee", mappedBy="employeeEmployee", cascade={"persist"})
     */
    private $employeeHasEmployers;
    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $birthAddress;
    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_birth_department", referencedColumnName="id_department")
     * })
     */
    private $birthDepartment;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="id_birth_city", referencedColumnName="id_city")
     */
    private $birthCity;


    /**
     * Set idEmployee
     *
     * @param integer $idEmployee
     *
     * @return Employee
     */
    public function setIdEmployee($idEmployee)
    {
        $this->idEmployee = $idEmployee;

        return $this;
    }

    /**
     * Get idEmployee
     *
     * @return integer
     */
    public function getIdEmployee()
    {
        return $this->idEmployee;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Employee
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson)
    {
        $this->personPerson = $personPerson;

        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Person
     */
    public function getPersonPerson()
    {
        return $this->personPerson;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * Set economicalTier
     *
     * @param string $economicalTier
     *
     * @return Employee
     */
    public function setEconomicalTier($economicalTier)
    {
        $this->economicalTier = $economicalTier;

        return $this;
    }

    /**
     * Get economicalTier
     *
     * @return string
     */
    public function getEconomicalTier()
    {
        return $this->economicalTier;
    }

    /**
     * Add employeeHasEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employeeHasEmployer
     *
     * @return Employee
     */
    public function addEmployeeHasEmployer(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employeeHasEmployer)
    {
        $this->employeeHasEmployers[] = $employeeHasEmployer;

        return $this;
    }

    /**
     * Remove employeeHasEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employeeHasEmployer
     */
    public function removeEmployeeHasEmployer(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employeeHasEmployer)
    {
        $this->employeeHasEmployers->removeElement($employeeHasEmployer);
    }

    /**
     * Get employeeHasEmployers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployeeHasEmployers()
    {
        return $this->employeeHasEmployers;
    }

    /**
     * Set civilStatus
     *
     * @param string $civilStatus
     *
     * @return Employee
     */
    public function setCivilStatus($civilStatus)
    {
        $this->civilStatus = $civilStatus;

        return $this;
    }

    /**
     * Get civilStatus
     *
     * @return string
     */
    public function getCivilStatus()
    {
        return $this->civilStatus;
    }

    /**
     * Set birthAddress
     *
     * @param string $birthAddress
     *
     * @return Employee
     */
    public function setBirthAddress($birthAddress)
    {
        $this->birthAddress = $birthAddress;

        return $this;
    }

    /**
     * Get birthAddress
     *
     * @return string
     */
    public function getBirthAddress()
    {
        return $this->birthAddress;
    }

    /**
     * Set birthDepartment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $birthDepartment
     *
     * @return Employee
     */
    public function setBirthDepartment(\RocketSeller\TwoPickBundle\Entity\Department $birthDepartment = null)
    {
        $this->birthDepartment = $birthDepartment;

        return $this;
    }

    /**
     * Get birthDepartment
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Department
     */
    public function getBirthDepartment()
    {
        return $this->birthDepartment;
    }

    /**
     * Set birthCity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $birthCity
     *
     * @return Employee
     */
    public function setBirthCity(\RocketSeller\TwoPickBundle\Entity\City $birthCity = null)
    {
        $this->birthCity = $birthCity;

        return $this;
    }

    /**
     * Get birthCity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\City
     */
    public function getBirthCity()
    {
        return $this->birthCity;
    }
}
