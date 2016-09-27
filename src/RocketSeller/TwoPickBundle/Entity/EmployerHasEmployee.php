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
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", inversedBy="employerHasEmployees")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     * @Exclude
     */
    private $employerEmployer;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employee
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employee", inversedBy="employeeHasEmployers")
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
     * Columna para los mensajes que se muestran al usuario en dashboard
     * -2 - employee is not payed
     * -1 - all docs are pending
     * 0 - employee docs are pending
     * 1 - employer docs are pending
     * 2 - message docs ready
     * 3 - all documents are in validation
     * 4 - employee documents are in validation
     * 5 - employer documents are in validation
     * 6 - employee docs validated employer docs error
     * 7 - employer docs validated employee docs error
     * 8 - employer documents error
     * 9 - employer documents error
     * 10 - all documents error
     * 11 - all docs validated message
     * 12 - all docs error message
     * 13 - backoffice finished message
     * 14 - backoffice finished
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $documentStatus = -2;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateDocumentsUploaded;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private $dateFinished;

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
        ->where(Criteria::expr()->eq("state",1));
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
     * Set documentStatus
     *
     * @param integer $documentStatus
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
     * @return integer
     */
    public function getDocumentStatus()
    {
        return $this->documentStatus;
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
}
