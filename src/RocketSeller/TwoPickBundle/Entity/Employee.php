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
     * @ORM\Column(type="smallint")
     */
    private $registerState;
    /**
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $registerExpress;

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
     * @ORM\OneToMany(targetEntity="EmployeeHasEntity", mappedBy="employeeEmployee", cascade={"persist"})
     */
    private $entities;

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
     * @var boolean $askBeneficiary 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $askBeneficiary ;
     
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
        $personPerson->setEmployee($this);
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

    /**
     * Add entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity
     *
     * @return Employee
     */
    public function addEntity(\RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * Remove entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity
     */
    public function removeEntity(\RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity $entity)
    {
        $this->entities->removeElement($entity);
    }

    /**
     * Get entities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }



    /**
     * Set askBeneficiary
     *
     * @param integer $askBeneficiary
     *
     * @return Employee
     */
    public function setAskBeneficiary($askBeneficiary)
    {
        $this->askBeneficiary = $askBeneficiary;

        return $this;
    }

    /**
     * Get askBeneficiary
     *
     * @return integer
     */
    public function getAskBeneficiary()
    {
        return $this->askBeneficiary;
    }

    /**
     * Set registerState
     *
     * @param integer $registerState
     *
     * @return Employee
     */
    public function setRegisterState($registerState)
    {
        $this->registerState = $registerState;

        return $this;
    }

    /**
     * Get registerState
     *
     * @return integer
     */
    public function getRegisterState()
    {
        return $this->registerState;
    }

    /**
     * Set registerExpress
     *
     * @param integer $registerExpress
     *
     * @return Employee
     */
    public function setRegisterExpress($registerExpress)
    {
        $this->registerExpress = $registerExpress;

        return $this;
    }

    /**
     * Get registerExpress
     *
     * @return integer
     */
    public function getRegisterExpress()
    {
        return $this->registerExpress;
    }
}
