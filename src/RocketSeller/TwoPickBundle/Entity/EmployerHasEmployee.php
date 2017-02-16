<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;


/**
 * EmployerHasEmployee
 *
 * @ORM\Table(name="employer_has_employee",
 *     indexes={@ORM\Index(name="fk_employer_has_employee_employer1",columns={"employer_id_employer"}),
 *              @ORM\Index(name="fk_employer_has_employee_employee1",columns={"employee_id_employee"}),
 *              @ORM\Index(name="fk_document_status_type1",columns={"document_status_type_id_document_status_type"})},
 *     uniqueConstraints={@UniqueConstraint(name="cartaUnique", columns={"auth_id_document", "id_employer_has_employee"})}
 * )
 * @ORM\Entity
 */
class EmployerHasEmployee
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_employer_has_employee", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEmployerHasEmployee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", inversedBy="employerHasEmployees", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     * @Exclude
     */
    private $employerEmployer;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employee
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employee", inversedBy="employeeHasEmployers", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_id_employee", referencedColumnName="id_employee")
     * })
     */
    private $employeeEmployee;

    /**
     * @ORM\OneToMany(targetEntity="Contract", mappedBy="employerHasEmployeeEmployerHasEmployee", cascade={"persist"})
     * @Exclude
     */
    private $contracts;

    /** @var Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="auth_id_document",referencedColumnName="id_document")
     * )
     */
    private $authDocument;

    /**
     * @ORM\OneToMany(targetEntity="Liquidation", mappedBy="employerHasEmployee", cascade={"persist"})
     * @Exclude
     */
    private $liquidations;

    /**
     * -2 - error
     * -1 - contractEnd
     * 0 - unActivated
     * 1 - active
     * 2 - Twilio Verification
     * 3 - symplifica paid
     * 4 - backoffice completed
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $state = 1;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $existentSQL = 0;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $existentHighTec = 0;

    /**
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $isFree = 0;

    /**
     * Columna para saber si es existente o no igual a la legal flag, pero ahora por empleado
     * 0 No ha iniciado labores
     * 1 ya inicio labores
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $legalFF = -1;

    /**
     * @var DocumentStatusType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\DocumentStatusType")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="document_status_type_id_document_status_type", referencedColumnName="id_document_status_type")
     * })
     */
    private $documentStatusType;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    private $documentStatus = null;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateDocumentsUploaded=null;

    /**
     * @ORM\Column(name="all_employee_docs_ready_at",type="datetime", nullable=TRUE)
     */
    private $allEmployeeDocsReadyAt=null;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateRegisterToSQL;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateTryToRegisterToSQL;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateFinished;

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
     * @ORM\Column(name="all_docs_ready_message_at",type="datetime", nullable=true)
     */
    private $allDocsReadyMessageAt=null;

    /**
     * @ORM\Column(name="all_docs_validated_message_at",type="datetime", nullable=true)
     */
    private $allDocsValidatedMessageAt=null;

    /**
     * @ORM\Column(name="backoffice_finish_message_at",type="datetime", nullable=true)
     */
    private $backofficeFinishMessageAt=null;

    /**
     * @ORM\Column(name="doc_error_message_at",type="datetime", nullable=true)
     */
    private $docErrorMessageAt=null;

    /**
     * @ORM\Column(name="is_post_register",type="boolean", nullable=TRUE)
     */
    private $isPostRegister;

    /**
     * @ORM\Column(name="error_email_sent_at",type="datetime", nullable=true)
     */
    private $ErrorEmailSentAt=null;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityRecord", mappedBy="employerHasEmployeeId")
     */
    private $entitiesRecords;

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
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer
     *
     * @return EmployerHasEmployee
     */
    public function setEmployerEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }

    /**
     * Set employeeEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employee $employeeEmployee
     *
     * @return EmployerHasEmployee
     */
    public function setEmployeeEmployee(\RocketSeller\TwoPickBundle\Entity\Employee $employeeEmployee)
    {
        $this->employeeEmployee = $employeeEmployee;

        return $this;
    }

    /**
     * Get employeeEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employee
     */
    public function getEmployeeEmployee()
    {
        return $this->employeeEmployee;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     *
     * @return EmployerHasEmployee
     */
    public function addContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;
        $contract->setEmployerHasEmployeeEmployerHasEmployee($this);
        return $this;
    }

    /**
     * Remove contract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contract
     */
    public function removeContract(\RocketSeller\TwoPickBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Get active contract
     * @return Contract
     */
    public function getActiveContract(){
        $criteria = Criteria::create()
        ->where(Criteria::expr()->eq("state",true));
        return $this->contracts->matching($criteria)->first();
    }

    /**
     * Add liquidation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation
     *
     * @return EmployerHasEmployee
     */
    public function addLiquidation(\RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation)
    {
        $this->liquidations[] = $liquidation;

        return $this;
    }

    /**
     * Remove liquidation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation
     */
    public function removeLiquidation(\RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation)
    {
        $this->liquidations->removeElement($liquidation);
    }

    /**
     * Get liquidations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLiquidations()
    {
        return $this->liquidations;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return EmployerHasEmployee
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    public function getContractByState($state)
    {

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq("state", $state));

        return $this->contracts->matching($criteria);
    }

    /**
     * Set isFree
     *
     * @param integer $isFree
     *
     * @return EmployerHasEmployee
     */
    public function setIsFree($isFree)
    {
        $this->isFree = $isFree;

        return $this;
    }

    /**
     * Get isFree
     *
     * @return integer
     */
    public function getIsFree()
    {
        return $this->isFree;
    }


    /**
     * Set existentSQL
     *
     * @param integer $existentSQL
     *
     * @return EmployerHasEmployee
     */
    public function setExistentSQL($existentSQL)
    {
        $this->existentSQL = $existentSQL;

        return $this;
    }

    /**
     * Get existentSQL
     *
     * @return integer
     */
    public function getExistentSQL()
    {
        return $this->existentSQL;
    }

    /**
     * Set legalFF
     *
     * @param integer $legalFF
     *
     * @return EmployerHasEmployee
     */
    public function setLegalFF($legalFF)
    {
        $this->legalFF = $legalFF;

        return $this;
    }

    /**
     * Get legalFF
     *
     * @return integer
     */
    public function getLegalFF()
    {
        return $this->legalFF;
    }

    /**
     * Set existentHighTec
     *
     * @param integer $existentHighTec
     *
     * @return EmployerHasEmployee
     */
    public function setExistentHighTec($existentHighTec)
    {
        $this->existentHighTec = $existentHighTec;

        return $this;
    }

    /**
     * Get existentHighTec
     *
     * @return integer
     */
    public function getExistentHighTec()
    {
        return $this->existentHighTec;
    }

    /**
     * Set cartaDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $authDocument
     *
     * @return EmployerHasEmployee
     */
    public function setAuthDocument(\RocketSeller\TwoPickBundle\Entity\Document $authDocument = null)
    {
        $this->authDocument = $authDocument;

        return $this;
    }

    /**
     * Get cartaDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getAuthDocument()
    {
        return $this->authDocument;
    }

    /**
     * Set dateDocumentsUploaded
     *
     * @param \DateTime $dateDocumentsUploaded
     *
     * @return EmployerHasEmployee
     */
    public function setDateDocumentsUploaded($dateDocumentsUploaded)
    {
        $this->dateDocumentsUploaded = $dateDocumentsUploaded;

        return $this;
    }

    /**
     * Get dateDocumentsUploaded
     *
     * @return \DateTime
     */
    public function getDateDocumentsUploaded()
    {
        return $this->dateDocumentsUploaded;
    }

    /**
     * Set dateFinished
     *
     * @param \DateTime $dateFinished
     *
     * @return EmployerHasEmployee
     */
    public function setDateFinished($dateFinished)
    {
        $this->dateFinished = $dateFinished;

        return $this;
    }

    /**
     * Get dateFinished
     *
     * @return \DateTime
     */
    public function getDateFinished()
    {
        return $this->dateFinished;
    }

    /**
     * Set documentStatusType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatusType
     *
     * @return EmployerHasEmployee
     */
    public function setDocumentStatusType(\RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatusType = null)
    {
        $this->documentStatusType = $documentStatusType;
        $this->documentStatus = $documentStatusType->getName();
        return $this;
    }

    /**
     * Get documentStatusType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\DocumentStatusType
     */
    public function getDocumentStatusType()
    {
        return $this->documentStatusType;
    }

    /**
     * Set dateRegisterToSQL
     *
     * @param \DateTime $dateRegisterToSQL
     *
     * @return EmployerHasEmployee
     */
    public function setDateRegisterToSQL($dateRegisterToSQL)
    {
        $this->dateRegisterToSQL = $dateRegisterToSQL;

        return $this;
    }

    /**
     * Get dateRegisterToSQL
     *
     * @return \DateTime
     */
    public function getDateRegisterToSQL()
    {
        return $this->dateRegisterToSQL;
    }

    /**
     * Set dateTryToRegisterToSQL
     *
     * @param \DateTime $dateTryToRegisterToSQL
     *
     * @return EmployerHasEmployee
     */
    public function setDateTryToRegisterToSQL($dateTryToRegisterToSQL)
    {
        $this->dateTryToRegisterToSQL = $dateTryToRegisterToSQL;

        return $this;
    }

    /**
     * Get dateTryToRegisterToSQL
     *
     * @return \DateTime
     */
    public function getDateTryToRegisterToSQL()
    {
        return $this->dateTryToRegisterToSQL;
    }

    /**
     * Set infoValidatedAt
     *
     * @param \DateTime $infoValidatedAt
     *
     * @return EmployerHasEmployee
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
     * @return EmployerHasEmployee
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
     * @return EmployerHasEmployee
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
     * @return EmployerHasEmployee
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
     * Set documentStatus
     *
     * @param string $documentStatus
     *
     * @return EmployerHasEmployee
     */
    public function setDocumentStatus($documentStatus)
    {
        $this->documentStatus = $documentStatus;

        return $this;
    }

    /**
     * Get documentStatus
     *
     * @return string
     */
    public function getDocumentStatus()
    {
        return $this->documentStatus;
    }

    /**
     * Set allEmployeeDocsReadyAt
     *
     * @param \DateTime $allEmployeeDocsReadyAt
     *
     * @return EmployerHasEmployee
     */
    public function setAllEmployeeDocsReadyAt($allEmployeeDocsReadyAt)
    {
        $this->allEmployeeDocsReadyAt = $allEmployeeDocsReadyAt;

        return $this;
    }

    /**
     * Get allEmployeeDocsReadyAt
     *
     * @return \DateTime
     */
    public function getAllEmployeeDocsReadyAt()
    {
        return $this->allEmployeeDocsReadyAt;
    }

    /**
     * Set allDocsReadyMessageAt
     *
     * @param \DateTime $allDocsReadyMessageAt
     *
     * @return EmployerHasEmployee
     */
    public function setAllDocsReadyMessageAt($allDocsReadyMessageAt)
    {
        $this->allDocsReadyMessageAt = $allDocsReadyMessageAt;

        return $this;
    }

    /**
     * Get allDocsReadyMessageAt
     *
     * @return \DateTime
     */
    public function getAllDocsReadyMessageAt()
    {
        return $this->allDocsReadyMessageAt;
    }

    /**
     * Set allDocsValidatedMessageAt
     *
     * @param \DateTime $allDocsValidatedMessageAt
     *
     * @return EmployerHasEmployee
     */
    public function setAllDocsValidatedMessageAt($allDocsValidatedMessageAt)
    {
        $this->allDocsValidatedMessageAt = $allDocsValidatedMessageAt;

        return $this;
    }

    /**
     * Get allDocsValidatedMessageAt
     *
     * @return \DateTime
     */
    public function getAllDocsValidatedMessageAt()
    {
        return $this->allDocsValidatedMessageAt;
    }

    /**
     * Set backofficeFinishMessageAt
     *
     * @param \DateTime $backofficeFinishMessageAt
     *
     * @return EmployerHasEmployee
     */
    public function setBackofficeFinishMessageAt($backofficeFinishMessageAt)
    {
        $this->backofficeFinishMessageAt = $backofficeFinishMessageAt;

        return $this;
    }

    /**
     * Get backofficeFinishMessageAt
     *
     * @return \DateTime
     */
    public function getBackofficeFinishMessageAt()
    {
        return $this->backofficeFinishMessageAt;
    }

    /**
     * Set docErrorMessageAt
     *
     * @param \DateTime $docErrorMessageAt
     *
     * @return EmployerHasEmployee
     */
    public function setDocErrorMessageAt($docErrorMessageAt)
    {
        $this->docErrorMessageAt = $docErrorMessageAt;

        return $this;
    }

    /**
     * Get docErrorMessageAt
     *
     * @return \DateTime
     */
    public function getDocErrorMessageAt()
    {
        return $this->docErrorMessageAt;
    }

    /**
     * Set isPostRegister
     *
     * @param boolean $isPostRegister
     *
     * @return EmployerHasEmployee
     */
    public function setIsPostRegister($isPostRegister)
    {
        $this->isPostRegister = $isPostRegister;

        return $this;
    }

    /**
     * Get isPostRegister
     *
     * @return boolean
     */
    public function getIsPostRegister()
    {
        return $this->isPostRegister;
    }

    /**
     * Add entitiesRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityRecord $entitiesRecord
     *
     * @return EmployerHasEmployee
     */
    public function addEntitiesRecord(\RocketSeller\TwoPickBundle\Entity\EntityRecord $entitiesRecord)
    {
        $this->entitiesRecords[] = $entitiesRecord;

        return $this;
    }

    /**
     * Remove entitiesRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityRecord $entitiesRecord
     */
    public function removeEntitiesRecord(\RocketSeller\TwoPickBundle\Entity\EntityRecord $entitiesRecord)
    {
        $this->entitiesRecords->removeElement($entitiesRecord);
    }

    /**
     * Get entitiesRecords
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntitiesRecords()
    {
        return $this->entitiesRecords;
    }
}
