<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * ContractRecord
 *
 * @ORM\Table(name="contract_record",
 *     indexes={@ORM\Index(name="fk_contract_record_contract_type", columns={"contract_type_id_contract_type"}),
 *              @ORM\Index(name="fk_document_amendment", columns={"document_id_document"}),
 *              @ORM\Index(name="fk_contract_record_document_status_type", columns={"document_status_type_id"})})
 * @ORM\Entity
 */
class ContractRecord
{
	
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id_contract_record", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $idContractRecord;

    /**
	 * @var \RocketSeller\TwoPickBundle\Entity\PayMethod
	 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayMethod", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
	 * })
	 */
	private $payMethodPayMethod;

    /**
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee", cascade={"persist"})
     * @ORM\JoinColumn(name="employer_has_employee_id_employer_has_employee", referencedColumnName="id_employer_has_employee")
     */
    private $employerHasEmployeeEmployeeHasEmployee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ContractType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_type_id_contract_type", referencedColumnName="id_contract_type")
     * })
     */
    private $contractTypeContractType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Frequency
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Frequency")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frequency_id_frequency", referencedColumnName="id_frequency", nullable=TRUE)
     * })
     */
    private $frequencyFrequency;

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $salary;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Workplace
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Workplace",  inversedBy="contractRecords")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workplace_id_workplace", referencedColumnName="id_workplace")
     * })
     */
    private $workplaceWorkplace;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord", mappedBy="contractRecordContractRecord", cascade={"persist", "remove"})
     */
    private $weekWorkableDaysRecord;

    /**
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $workableDaysMonth;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\TimeCommitment
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\TimeCommitment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="time_commitment_id_time_commitment", referencedColumnName="id_time_commitment")
     * })
     */
    private $timeCommitmentTimeCommitment;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $sisben;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $transportAid; // 1 vive donde trabaja - 0 externo

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $holidayDebt;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EmployeeContractType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployeeContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_contract_type_id_employee_contract_type", referencedColumnName="id_employee_contract_type")
     * })
     */
    private $employeeContractTypeEmployeeContractType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Position
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Position")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="position_id_position", referencedColumnName="id_position")
     * })
     */
    private $positionPosition;

    /**
	 * @var \RocketSeller\TwoPickBundle\Entity\Document
	 * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="document_id_document", referencedColumnName="id_document")
	 * })
	 * @Exclude
	 */
	private $documentAmendment;

	/**
	 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\DocumentStatusType")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="document_status_type_id", referencedColumnName="id_document_status_type", nullable=true)
	 *   })
	 */
	private $documentStatus;

	/**
	 * @ORM\Column(name="validation_date", type="date", nullable=true)
	 */
	private $validationDate;

	/**
	 * @ORM\Column(name="error_date", type="date", nullable=true)
	 */
	private $errorDate;

	/**
	 * @ORM\Column(type="date", nullable=TRUE)
	 */
	private $startDate;

	/**
	 * @ORM\Column(type="date", nullable=TRUE)
	 */
	private $endDate;

	/**
	 * @ORM\Column(type="date", nullable=TRUE)
	 */
	private $testPeriod;

	/**
	 * @ORM\Column(type="time", nullable=TRUE)
	 */
	private $workTimeStart;

	/**
	 * @ORM\Column(type="time", nullable=TRUE)
	 */
	private $workTimeEnd;

	/**
	 * @var \RocketSeller\TwoPickBundle\Entity\PlanillaType
	 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PlanillaType")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="planilla_type_id_planilla_type", referencedColumnName="id_planilla_type")
	 * })
	 */
	private $planillaTypePlanillaType;

	/**
	 * @ORM\Column(name="date_changes_applied", type="date", nullable=TRUE)
	 */
	private $dateChangesApplied;

    /**
     * @ORM\Column(name="date_to_be_aplied", type="date", nullable=TRUE)
     */
    private $dateToBeAplied;

	/**
	 * @var Contract
	 * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Contract", inversedBy="contractRecords")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="contract_id", referencedColumnName="id_contract", nullable=false)
	 *   })
	 */
	private $contractContract;

	/**
	 * @ORM\Column(name="to_be_executed", type="smallint", nullable=TRUE)
     *
     * 0 - false
     * 1 - true
     *
     */
	private $toBeExecuted;

	/**
	 * @ORM\Column(type="date", nullable=TRUE)
	 */
	private $autoRenewalEndDate;

    /**
     * 0 no definido
     * 1 si trabaja los sabados
     * -1 no trabaja los sabados
     * aplica solo para tiempo completo
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $worksSaturday = 0;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->weekWorkableDaysRecord = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idContractRecord
     *
     * @return integer
     */
    public function getIdContractRecord()
    {
        return $this->idContractRecord;
    }

    /**
     * Set salary
     *
     * @param float $salary
     *
     * @return ContractRecord
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;

        return $this;
    }

    /**
     * Get salary
     *
     * @return float
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * Set workableDaysMonth
     *
     * @param integer $workableDaysMonth
     *
     * @return ContractRecord
     */
    public function setWorkableDaysMonth($workableDaysMonth)
    {
        $this->workableDaysMonth = $workableDaysMonth;

        return $this;
    }

    /**
     * Get workableDaysMonth
     *
     * @return integer
     */
    public function getWorkableDaysMonth()
    {
        return $this->workableDaysMonth;
    }

    /**
     * Set sisben
     *
     * @param integer $sisben
     *
     * @return ContractRecord
     */
    public function setSisben($sisben)
    {
        $this->sisben = $sisben;

        return $this;
    }

    /**
     * Get sisben
     *
     * @return integer
     */
    public function getSisben()
    {
        return $this->sisben;
    }

    /**
     * Set transportAid
     *
     * @param integer $transportAid
     *
     * @return ContractRecord
     */
    public function setTransportAid($transportAid)
    {
        $this->transportAid = $transportAid;

        return $this;
    }

    /**
     * Get transportAid
     *
     * @return integer
     */
    public function getTransportAid()
    {
        return $this->transportAid;
    }

    /**
     * Set holidayDebt
     *
     * @param float $holidayDebt
     *
     * @return ContractRecord
     */
    public function setHolidayDebt($holidayDebt)
    {
        $this->holidayDebt = $holidayDebt;

        return $this;
    }

    /**
     * Get holidayDebt
     *
     * @return float
     */
    public function getHolidayDebt()
    {
        return $this->holidayDebt;
    }

    /**
     * Set validationDate
     *
     * @param \DateTime $validationDate
     *
     * @return ContractRecord
     */
    public function setValidationDate($validationDate)
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    /**
     * Get validationDate
     *
     * @return \DateTime
     */
    public function getValidationDate()
    {
        return $this->validationDate;
    }

    /**
     * Set errorDate
     *
     * @param \DateTime $errorDate
     *
     * @return ContractRecord
     */
    public function setErrorDate($errorDate)
    {
        $this->errorDate = $errorDate;

        return $this;
    }

    /**
     * Get errorDate
     *
     * @return \DateTime
     */
    public function getErrorDate()
    {
        return $this->errorDate;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return ContractRecord
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return ContractRecord
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set testPeriod
     *
     * @param \DateTime $testPeriod
     *
     * @return ContractRecord
     */
    public function setTestPeriod($testPeriod)
    {
        $this->testPeriod = $testPeriod;

        return $this;
    }

    /**
     * Get testPeriod
     *
     * @return \DateTime
     */
    public function getTestPeriod()
    {
        return $this->testPeriod;
    }

    /**
     * Set workTimeStart
     *
     * @param \DateTime $workTimeStart
     *
     * @return ContractRecord
     */
    public function setWorkTimeStart($workTimeStart)
    {
        $this->workTimeStart = $workTimeStart;

        return $this;
    }

    /**
     * Get workTimeStart
     *
     * @return \DateTime
     */
    public function getWorkTimeStart()
    {
        return $this->workTimeStart;
    }

    /**
     * Set workTimeEnd
     *
     * @param \DateTime $workTimeEnd
     *
     * @return ContractRecord
     */
    public function setWorkTimeEnd($workTimeEnd)
    {
        $this->workTimeEnd = $workTimeEnd;

        return $this;
    }

    /**
     * Get workTimeEnd
     *
     * @return \DateTime
     */
    public function getWorkTimeEnd()
    {
        return $this->workTimeEnd;
    }

    /**
     * Set dateChangesApplied
     *
     * @param \DateTime $dateChangesApplied
     *
     * @return ContractRecord
     */
    public function setDateChangesApplied($dateChangesApplied)
    {
        $this->dateChangesApplied = $dateChangesApplied;

        return $this;
    }

    /**
     * Get dateChangesApplied
     *
     * @return \DateTime
     */
    public function getDateChangesApplied()
    {
        return $this->dateChangesApplied;
    }

    /**
     * Set toBeExecuted
     *
     * @param integer $toBeExecuted
     *
     * @return ContractRecord
     */
    public function setToBeExecuted($toBeExecuted)
    {
        $this->toBeExecuted = $toBeExecuted;

        return $this;
    }

    /**
     * Get toBeExecuted
     *
     * @return integer
     */
    public function getToBeExecuted()
    {
        return $this->toBeExecuted;
    }

    /**
     * Set autoRenewalEndDate
     *
     * @param \DateTime $autoRenewalEndDate
     *
     * @return ContractRecord
     */
    public function setAutoRenewalEndDate($autoRenewalEndDate)
    {
        $this->autoRenewalEndDate = $autoRenewalEndDate;

        return $this;
    }

    /**
     * Get autoRenewalEndDate
     *
     * @return \DateTime
     */
    public function getAutoRenewalEndDate()
    {
        return $this->autoRenewalEndDate;
    }

    /**
     * Set employerHasEmployeeEmployeeHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployeeHasEmployee
     *
     * @return ContractRecord
     */
    public function setEmployerHasEmployeeEmployeeHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployeeHasEmployee = null)
    {
        $this->employerHasEmployeeEmployeeHasEmployee = $employerHasEmployeeEmployeeHasEmployee;

        return $this;
    }

    /**
     * Get employerHasEmployeeEmployeeHasEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployeeEmployeeHasEmployee()
    {
        return $this->employerHasEmployeeEmployeeHasEmployee;
    }

    /**
     * Set contractTypeContractType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType
     *
     * @return ContractRecord
     */
    public function setContractTypeContractType(\RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType = null)
    {
        $this->contractTypeContractType = $contractTypeContractType;

        return $this;
    }

    /**
     * Get contractTypeContractType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ContractType
     */
    public function getContractTypeContractType()
    {
        return $this->contractTypeContractType;
    }

    /**
     * Set frequencyFrequency
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Frequency $frequencyFrequency
     *
     * @return ContractRecord
     */
    public function setFrequencyFrequency(\RocketSeller\TwoPickBundle\Entity\Frequency $frequencyFrequency = null)
    {
        $this->frequencyFrequency = $frequencyFrequency;

        return $this;
    }

    /**
     * Get frequencyFrequency
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Frequency
     */
    public function getFrequencyFrequency()
    {
        return $this->frequencyFrequency;
    }

    /**
     * Set workplaceWorkplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Workplace $workplaceWorkplace
     *
     * @return ContractRecord
     */
    public function setWorkplaceWorkplace(\RocketSeller\TwoPickBundle\Entity\Workplace $workplaceWorkplace = null)
    {
        $this->workplaceWorkplace = $workplaceWorkplace;

        return $this;
    }

    /**
     * Get workplaceWorkplace
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Workplace
     */
    public function getWorkplaceWorkplace()
    {
        return $this->workplaceWorkplace;
    }

    /**
     * Add weekWorkableDaysRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord $weekWorkableDaysRecord
     *
     * @return ContractRecord
     */
    public function addWeekWorkableDaysRecord(\RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord $weekWorkableDaysRecord)
    {
        $this->weekWorkableDaysRecord[] = $weekWorkableDaysRecord;

        return $this;
    }

    /**
     * Remove weekWorkableDaysRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord $weekWorkableDaysRecord
     */
    public function removeWeekWorkableDaysRecord(\RocketSeller\TwoPickBundle\Entity\WeekWorkableDaysRecord $weekWorkableDaysRecord)
    {
        $this->weekWorkableDaysRecord->removeElement($weekWorkableDaysRecord);
    }

    /**
     * Get weekWorkableDaysRecord
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWeekWorkableDaysRecord()
    {
        return $this->weekWorkableDaysRecord;
    }

    /**
     * Set timeCommitmentTimeCommitment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TimeCommitment $timeCommitmentTimeCommitment
     *
     * @return ContractRecord
     */
    public function setTimeCommitmentTimeCommitment(\RocketSeller\TwoPickBundle\Entity\TimeCommitment $timeCommitmentTimeCommitment = null)
    {
        $this->timeCommitmentTimeCommitment = $timeCommitmentTimeCommitment;

        return $this;
    }

    /**
     * Get timeCommitmentTimeCommitment
     *
     * @return \RocketSeller\TwoPickBundle\Entity\TimeCommitment
     */
    public function getTimeCommitmentTimeCommitment()
    {
        return $this->timeCommitmentTimeCommitment;
    }

    /**
     * Set employeeContractTypeEmployeeContractType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeContractType $employeeContractTypeEmployeeContractType
     *
     * @return ContractRecord
     */
    public function setEmployeeContractTypeEmployeeContractType(\RocketSeller\TwoPickBundle\Entity\EmployeeContractType $employeeContractTypeEmployeeContractType = null)
    {
        $this->employeeContractTypeEmployeeContractType = $employeeContractTypeEmployeeContractType;

        return $this;
    }

    /**
     * Get employeeContractTypeEmployeeContractType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployeeContractType
     */
    public function getEmployeeContractTypeEmployeeContractType()
    {
        return $this->employeeContractTypeEmployeeContractType;
    }

    /**
     * Set positionPosition
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Position $positionPosition
     *
     * @return ContractRecord
     */
    public function setPositionPosition(\RocketSeller\TwoPickBundle\Entity\Position $positionPosition = null)
    {
        $this->positionPosition = $positionPosition;

        return $this;
    }

    /**
     * Get positionPosition
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Position
     */
    public function getPositionPosition()
    {
        return $this->positionPosition;
    }

    /**
     * Set payMethodPayMethod
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod
     *
     * @return ContractRecord
     */
    public function setPayMethodPayMethod(\RocketSeller\TwoPickBundle\Entity\PayMethod $payMethodPayMethod = null)
    {
        $this->payMethodPayMethod = $payMethodPayMethod;

        return $this;
    }

    /**
     * Get payMethodPayMethod
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayMethod
     */
    public function getPayMethodPayMethod()
    {
        return $this->payMethodPayMethod;
    }

    /**
     * Set documentAmendment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $documentAmendment
     *
     * @return ContractRecord
     */
    public function setDocumentAmendment(\RocketSeller\TwoPickBundle\Entity\Document $documentAmendment = null)
    {
        $this->documentAmendment = $documentAmendment;

        return $this;
    }

    /**
     * Get documentAmendment
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocumentAmendment()
    {
        return $this->documentAmendment;
    }

    /**
     * Set documentStatus
     *
     * @param \RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatus
     *
     * @return ContractRecord
     */
    public function setDocumentStatus(\RocketSeller\TwoPickBundle\Entity\DocumentStatusType $documentStatus = null)
    {
        $this->documentStatus = $documentStatus;

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
     * Set planillaTypePlanillaType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PlanillaType $planillaTypePlanillaType
     *
     * @return ContractRecord
     */
    public function setPlanillaTypePlanillaType(\RocketSeller\TwoPickBundle\Entity\PlanillaType $planillaTypePlanillaType = null)
    {
        $this->planillaTypePlanillaType = $planillaTypePlanillaType;

        return $this;
    }

    /**
     * Get planillaTypePlanillaType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PlanillaType
     */
    public function getPlanillaTypePlanillaType()
    {
        return $this->planillaTypePlanillaType;
    }

    /**
     * Set contractContract
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Contract $contractContract
     *
     * @return ContractRecord
     */
    public function setContractContract(\RocketSeller\TwoPickBundle\Entity\Contract $contractContract)
    {
        $this->contractContract = $contractContract;

        return $this;
    }

    /**
     * Get contractContract
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Contract
     */
    public function getContractContract()
    {
        return $this->contractContract;
    }

    /**
     * Set dateToBeAplied
     *
     * @param \DateTime $dateToBeAplied
     *
     * @return ContractRecord
     */
    public function setDateToBeAplied($dateToBeAplied)
    {
        $this->dateToBeAplied = $dateToBeAplied;

        return $this;
    }

    /**
     * Get dateToBeAplied
     *
     * @return \DateTime
     */
    public function getDateToBeAplied()
    {
        return $this->dateToBeAplied;
    }

    /**
     * Set worksSaturday
     *
     * @param integer $worksSaturday
     *
     * @return ContractRecord
     */
    public function setWorksSaturday($worksSaturday)
    {
        $this->worksSaturday = $worksSaturday;

        return $this;
    }

    /**
     * Get worksSaturday
     *
     * @return integer
     */
    public function getWorksSaturday()
    {
        return $this->worksSaturday;
    }
}
