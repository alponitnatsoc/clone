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
     * @ORM\OneToMany(targetEntity="EmployerHasEmployee", mappedBy="employeeEmployee", cascade={"persist"})
     */
    private $employeeHasEmployers;
    /**
     * @ORM\OneToMany(targetEntity="EmployeeHasBeneficiary", mappedBy="employeeEmployee", cascade={"persist"})
     */
    private $employeeHasBeneficiary;
    /**
     * @var boolean $twoFactorAuthentication Enabled yes/no
     * @ORM\Column(type="boolean")
     */
    private $twoFactorAuthentication = false;
     
    /**
     * @var integer $twoFactorCode Current authentication code
     * @ORM\Column(type="integer", nullable=true)
     */
    private $twoFactorCode;

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
     * Set twoFactorAuthentication
     *
     * @param boolean $twoFactorAuthentication
     *
     * @return Employee
     */
    public function setTwoFactorAuthentication($twoFactorAuthentication)
    {
        $this->twoFactorAuthentication = $twoFactorAuthentication;

        return $this;
    }

    /**
     * Get twoFactorAuthentication
     *
     * @return boolean
     */
    public function getTwoFactorAuthentication()
    {
        return $this->twoFactorAuthentication;
    }

    /**
     * Set twoFactorCode
     *
     * @param integer $twoFactorCode
     *
     * @return Employee
     */
    public function setTwoFactorCode($twoFactorCode)
    {
        $this->twoFactorCode = $twoFactorCode;

        return $this;
    }

    /**
     * Get twoFactorCode
     *
     * @return integer
     */
    public function getTwoFactorCode()
    {
        return $this->twoFactorCode;
    }

    /**
     * Add employeeHasBeneficiary
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary $employeeHasBeneficiary
     *
     * @return Employee
     */
    public function addEmployeeHasBeneficiary(\RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary $employeeHasBeneficiary)
    {
        $this->employeeHasBeneficiary[] = $employeeHasBeneficiary;

        return $this;
    }

    /**
     * Remove employeeHasBeneficiary
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary $employeeHasBeneficiary
     */
    public function removeEmployeeHasBeneficiary(\RocketSeller\TwoPickBundle\Entity\EmployeeHasBeneficiary $employeeHasBeneficiary)
    {
        $this->employeeHasBeneficiary->removeElement($employeeHasBeneficiary);
    }

    /**
     * Get employeeHasBeneficiary
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployeeHasBeneficiary()
    {
        return $this->employeeHasBeneficiary;
    }
}
