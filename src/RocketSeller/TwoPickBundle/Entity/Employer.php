<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Employer
 *
 * @ORM\Table(name="employer", indexes={@ORM\Index(name="fk_employer_person1", columns={"person_id_person"})},
 *     uniqueConstraints={@UniqueConstraint(name="mandatoryUnique", columns={"mandatory_id_document", "id_employer"})}
 *     )
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
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idSqlSociety;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idHighTech;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $existentNovo;

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
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $registerExpress;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="employer", cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     * @Exclude
     */
    private $personPerson;
    /**
     * @ORM\OneToMany(targetEntity="Workplace", mappedBy="employerEmployer" , cascade={"persist", "remove"})
     * @Exclude
     */
    private $workplaces;

    /**
     * @ORM\OneToMany(targetEntity="RealProcedure", mappedBy="employerEmployer", cascade={"persist"})
     * @Exclude
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

    /** @var Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="mandatory_id_document",referencedColumnName="id_document")
     * )
     */
    private $mandatoryDocument;

    /** @var integer
     * 0 - new
     * 1 - started
     * 2 - error
     * 3 - corrected
     * 4 - finished
     * 5 - contractValidated
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status=0;

		/**
		 * -1 - Success
		 * -2 - Employer was registered before PilaBot
		 * Anything else, active transaction id
		 * @ORM\Column(type="integer", nullable=TRUE)
		 */
		private $existentPila;


		/**
		 * @ORM\ManyToMany(targetEntity="Transaction", cascade={"persist"})
		 * @ORM\JoinTable(name="employer_has_transactions",
		 *      joinColumns={ @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")},
		 *      inverseJoinColumns={@ORM\JoinColumn(name="transaction_id_transaction", referencedColumnName="id_transaction")}
		 *      )
		 */
		private $transactions;

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
        $realProcedure->setEmployerEmployer($this);
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
        $entity->setEmployerEmployer($this);
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

    /**
     * Set registerExpress
     *
     * @param integer $registerExpress
     *
     * @return Employer
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

    /**
     * Set idSqlSociety
     *
     * @param integer $idSqlSociety
     *
     * @return Employer
     */
    public function setIdSqlSociety($idSqlSociety)
    {
        $this->idSqlSociety = $idSqlSociety;

        return $this;
    }

    /**
     * Get idSqlSociety
     *
     * @return integer
     */
    public function getIdSqlSociety()
    {
        return $this->idSqlSociety;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
        $this->realProcedure = new \Doctrine\Common\Collections\ArrayCollection();
        $this->employerHasEmployees = new \Doctrine\Common\Collections\ArrayCollection();
        $this->entities = new \Doctrine\Common\Collections\ArrayCollection();
	      $this->transactions = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set idHighTech
     *
     * @param integer $idHighTech
     *
     * @return Employer
     */
    public function setIdHighTech($idHighTech)
    {
        $this->idHighTech = $idHighTech;

        return $this;
    }

    /**
     * Get idHighTech
     *
     * @return integer
     */
    public function getIdHighTech()
    {
        return $this->idHighTech;
    }

    /**
     * Set mandatoryDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $mandatoryDocument
     *
     * @return Employer
     */
    public function setMandatoryDocument(\RocketSeller\TwoPickBundle\Entity\Document $mandatoryDocument = null)
    {
        $this->mandatoryDocument = $mandatoryDocument;

        return $this;
    }

    /**
     * Get mandatoryDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getMandatoryDocument()
    {
        return $this->mandatoryDocument;
    }


    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Employer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get ActiveEmployerHasEmployees
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActiveEmployerHasEmployees()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte("state",2));
        return $this->employerHasEmployees->matching($criteria);
    }

    /**
     * Set existentPila
     *
     * @param integer $existentPila
     *
     * @return Employer
     */
    public function setExistentPila($existentPila)
    {
        $this->existentPila = $existentPila;

        return $this;
    }

    /**
     * Get existentPila
     *
     * @return integer
     */
    public function getExistentPila()
    {
        return $this->existentPila;
    }

    /**
     * Add transaction
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Transaction $transaction
     *
     * @return Employer
     */
    public function addTransaction(\RocketSeller\TwoPickBundle\Entity\Transaction $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Transaction $transaction
     */
    public function removeTransaction(\RocketSeller\TwoPickBundle\Entity\Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set existentNovo
     *
     * @param integer $existentNovo
     *
     * @return Employer
     */
    public function setExistentNovo($existentNovo)
    {
        $this->existentNovo = $existentNovo;

        return $this;
    }

    /**
     * Get existentNovo
     *
     * @return integer
     */
    public function getExistentNovo()
    {
        return $this->existentNovo;
    }
}
