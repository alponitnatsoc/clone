<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use RocketSeller\TwoPickBundle\RocketSellerTwoPickBundle;

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
     * @var EmployerHasEmployee $employerHasEmployees
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
	
	/** @var Document
	 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
	 * @ORM\JoinColumns(
	 *     @ORM\JoinColumn(name="signature_id",referencedColumnName="id_document")
	 * )
	 */
	private $signature;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $status=null;

    /**
     * -1 - Success
     * -2 - Employer was registered before PilaBot
     * Anything else, active transaction id
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $existentPila;


    /**
     * @ORM\ManyToMany(targetEntity="Transaction", cascade={"persist"}, inversedBy="employers")
     * @ORM\JoinTable(name="employer_has_transactions",
     *      joinColumns={ @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="transaction_id_transaction", referencedColumnName="id_transaction")}
     *      )
     */
    private $transactions;

    /**
     * @ORM\Column(name="info_validated_at",type="datetime", nullable=true)
     */
    private $infoValidatedAt=null;

    /**
     * @ORM\Column(name="info_error_at",type="datetime", nullable=true)
     */
    private $infoErrorAt=null;

    /**
     * @ORM\Column(name="validated_email_sent_at",type="datetime", nullable=true)
     */
    private $validatedEmailSentAt=null;

    /**
     * @ORM\Column(name="error_email_sent_at",type="datetime", nullable=true)
     */
    private $ErrorEmailSentAt=null;

    /**
     * @ORM\Column(name="all_docs_ready_at",type="datetime", nullable=true)
     */
    private $allDocsReadyAt=null;

    /**
     * @ORM\Column(name="dashboard_message",type="datetime", nullable=true)
     */
    private $dashboardMessage=null; 

    /**
     * @var DocumentStatusType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\DocumentStatusType")
     * @ORM\JoinColumn(name="document_status_type_id", referencedColumnName="id_document_status_type",nullable=true)
     */
    private $documentStatus = null;

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
     * @param EntityType $entityType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployerHasEntityByEntityType($entityType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("entityEntity.entityTypeEntityType",$entityType));
        return $this->entities->matching($criteria);
    }

    /**
     * @param ProcedureType $procedureType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRealProcedureByProcedureTypeType($procedureType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("procedureTypeProcedureType",$procedureType));
        return $this->realProcedure->matching($criteria);
    }

    /**
     * @param string $id
     * @return RocketSellerTwoPickBundle:Workplace
     */
    public function getWorkplaceById($id)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('idWorkplace',intval($id)));
        return $this->workplaces->matching($criteria)->first();
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
    
    /**
     * Set infoValidatedAt
     *
     * @param \DateTime $infoValidatedAt
     *
     * @return Employer
     */
    public function setInfoValidatedAt($infoValidatedAt)
    {
        $this->infoValidatedAt = $infoValidatedAt;

        return $this;
    }

    /**
     * Get infoValidatedAt
     *
     * @return \DateTime
     */
    public function getInfoValidatedAt()
    {
        return $this->infoValidatedAt;
    }

    /**
     * Set validatedEmailSentAt
     *
     * @param \DateTime $validatedEmailSentAt
     *
     * @return Employer
     */
    public function setValidatedEmailSentAt($validatedEmailSentAt)
    {
        $this->validatedEmailSentAt = $validatedEmailSentAt;

        return $this;
    }

    /**
     * Get validatedEmailSentAt
     *
     * @return \DateTime
     */
    public function getValidatedEmailSentAt()
    {
        return $this->validatedEmailSentAt;
    }

    /**
     * Set errorEmailSentAt
     *
     * @param \DateTime $errorEmailSentAt
     *
     * @return Employer
     */
    public function setErrorEmailSentAt($errorEmailSentAt)
    {
        $this->ErrorEmailSentAt = $errorEmailSentAt;

        return $this;
    }

    /**
     * Get errorEmailSentAt
     *
     * @return \DateTime
     */
    public function getErrorEmailSentAt()
    {
        return $this->ErrorEmailSentAt;
    }


    /**
     * Set infoErrorAt
     *
     * @param \DateTime $infoErrorAt
     *
     * @return Employer
     */
    public function setInfoErrorAt($infoErrorAt)
    {
        $this->infoErrorAt = $infoErrorAt;

        return $this;
    }

    /**
     * Get infoErrorAt
     *
     * @return \DateTime
     */
    public function getInfoErrorAt()
    {
        return $this->infoErrorAt;
    }

    /**
     * Set documentStatusType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatus
     *
     * @return Employer
     */
    public function setDocumentStatus(\RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatus = null)
    {
        $this->documentStatus= $documentStatus;
        $this->status = $documentStatus->getName();
        return $this;
    }

    /**
     * Get documentStatus
     *
     * @return \RocketSeller\TwoPickBundle\Entity\DocumentStatusType
     */
    public function getDocumentStatus()
    {
        return $this->documentStatus;
    }

    /**
     * Set allDocsReadyAt
     *
     * @param \DateTime $allDocsReadyAt
     *
     * @return Employer
     */
    public function setAllDocsReadyAt($allDocsReadyAt)
    {
        $this->allDocsReadyAt = $allDocsReadyAt;

        return $this;
    }

    /**
     * Get allDocsReadyAt
     *
     * @return \DateTime
     */
    public function getAllDocsReadyAt()
    {
        return $this->allDocsReadyAt;
    }

    /**
     * Set status
     *
     * @param string $status
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
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set dashboardMessage
     *
     * @param \DateTime $dashboardMessage
     *
     * @return Employer
     */
    public function setDashboardMessage($dashboardMessage)
    {
        $this->dashboardMessage = $dashboardMessage;

        return $this;
    }

    /**
     * Get dashboardMessage
     *
     * @return \DateTime
     */
    public function getDashboardMessage()
    {
        return $this->dashboardMessage;
    }

    /**
     * Set signature
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $signature
     *
     * @return Employer
     */
    public function setSignature(\RocketSeller\TwoPickBundle\Entity\Document $signature = null)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
