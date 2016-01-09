<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employer
 *
 * @ORM\Table(name="employer", indexes={@ORM\Index(name="fk_employer_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class Employer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employer", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEmployer;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $employerType;
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $economicalActivity;
    /**
     * @ORM\Column(type="smallint")
     */
    private $registerState;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="employer", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;
    /**
     * @ORM\OneToMany(targetEntity="Workplace", mappedBy="employerEmployer" , cascade={"persist", "remove"})
     */
    private $workplaces;

    /**
     * @ORM\OneToMany(targetEntity="RealProcedure", mappedBy="employerEmployer", cascade={"persist"})
     */
    private $realProcedure;

    /**
     * @ORM\OneToMany(targetEntity="EmployerHasEmployee", mappedBy="employerEmployer", cascade={"persist"})
     */
    private $employerHasEmployees;

    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    private $sameWorkHouse;

    /**
     * @ORM\OneToMany(targetEntity="EmployerHasEntity", mappedBy="employerEmployer", cascade={"persist"})
     */
    private $entities;


    /**
     * Set idEmployer
     *
     * @param integer $idEmployer
     *
     * @return Employer
     */
    public function setIdEmployer($idEmployer)
    {
        $this->idEmployer = $idEmployer;

        return $this;
    }

    /**
     * Get idEmployer
     *
     * @return integer
     */
    public function getIdEmployer()
    {
        return $this->idEmployer;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Employer
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
     * Add workplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Workplace $workplace
     *
     * @return Employer
     */
    public function addWorkplace(\RocketSeller\TwoPickBundle\Entity\Workplace $workplace)
    {
        $workplace->setEmployerEmployer($this);
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



    /**
     * Add realProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure
     *
     * @return Employer
     */
    public function addRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure)
    {
        $this->realProcedure[] = $realProcedure;

        return $this;
    }

    /**
     * Remove realProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure
     */
    public function removeRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure)
    {
        $this->realProcedure->removeElement($realProcedure);
    }

    /**
     * Get realProcedure
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRealProcedure()
    {
        return $this->realProcedure;
    }

    /**
     * Add employerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee
     *
     * @return Employer
     */
    public function addEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee)
    {
        $this->employerHasEmployees[] = $employerHasEmployee;

        return $this;
    }

    /**
     * Remove employerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee
     */
    public function removeEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployee)
    {
        $this->employerHasEmployees->removeElement($employerHasEmployee);
    }

    /**
     * Get employerHasEmployees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployerHasEmployees()
    {
        return $this->employerHasEmployees;
    }

    /**
     * Set employerType
     *
     * @param string $employerType
     *
     * @return Employer
     */
    public function setEmployerType($employerType)
    {
        $this->employerType = $employerType;

        return $this;
    }

    /**
     * Get employerType
     *
     * @return string
     */
    public function getEmployerType()
    {
        return $this->employerType;
    }



    /**
     * Set sameWorkHouse
     *
     * @param boolean $sameWorkHouse
     *
     * @return Employer
     */
    public function setSameWorkHouse($sameWorkHouse)
    {
        $this->sameWorkHouse = $sameWorkHouse;

        return $this;
    }

    /**
     * Get sameWorkHouse
     *
     * @return boolean
     */
    public function getSameWorkHouse()
    {
        return $this->sameWorkHouse;
    }

    /**
     * Set registerState
     *
     * @param integer $registerState
     *
     * @return Employer
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
     * Add entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $entity
     *
     * @return Employer
     */
    public function addEntity(\RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * Remove entity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $entity
     */
    public function removeEntity(\RocketSeller\TwoPickBundle\Entity\EmployerHasEntity $entity)
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
     * Set economicalActivity
     *
     * @param string $economicalActivity
     *
     * @return Employer
     */
    public function setEconomicalActivity($economicalActivity)
    {
        $this->economicalActivity = $economicalActivity;

        return $this;
    }

    /**
     * Get economicalActivity
     *
     * @return string
     */
    public function getEconomicalActivity()
    {
        return $this->economicalActivity;
    }
}
