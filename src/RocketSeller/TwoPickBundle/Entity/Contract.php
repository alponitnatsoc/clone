<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Contract
 *
 * @ORM\Table(name="contract",
 *     indexes={@ORM\Index(name="fk_contract_employer_has_employee1", columns={"employer_has_employee_id_employer_has_employee"}),
 *              @ORM\Index(name="fk_contract_contract_type1", columns={"contract_type_id_contract_type"}),
 *              @ORM\Index(name="fk_contract_document1", columns={"document_id_document"}),
 *              @ORM\Index(name="fk_contract_document_status_type1", columns={"contract_document_status_type_id_contract_document_status_type"})})
 * @ORM\Entity
 */
class Contract
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idContract;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Frequency
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Frequency")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frequency_id_frequency", referencedColumnName="id_frequency", nullable=TRUE)
     * })
     */
    private $frequencyFrequency;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee", inversedBy="contracts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_has_employee_id_employer_has_employee", referencedColumnName="id_employer_has_employee")
     * })
     */
    private $employerHasEmployeeEmployerHasEmployee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\ContractType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="contract_type_id_contract_type", referencedColumnName="id_contract_type")
     * })
     */
    private $contractTypeContractType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Workplace
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Workplace",  inversedBy="contracts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workplace_id_workplace", referencedColumnName="id_workplace")
     * })
     */
    private $workplaceWorkplace;

    /**
     * @ORM\OneToMany(targetEntity="Payroll", mappedBy="contractContract", cascade={"persist"})
     * @Exclude
     */
    private $payrolls;

    /**
     * @ORM\OneToMany(targetEntity="Prima", mappedBy="contractContract", cascade={"persist"})
     * @Exclude
     */
    private $primas;
    /**
     * @ORM\ManyToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Severances", mappedBy="contracts", cascade={"persist"})
     * @Exclude
     */
    private $severances;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", cascade={"persist"})
     * @ORM\JoinColumn(name="active_payroll", referencedColumnName="id_payroll")
     * @Exclude
     */
    private $activePayroll;

    /**
     * @ORM\OneToMany(targetEntity="ContractHasBenefits", mappedBy="contractContract", cascade={"persist"})
     * @Exclude
     */
    private $benefits;


    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    private $state = 1;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $sisben;

    /**
     * 1 = yes
     * 0 = No
     * @ORM\Column(type="smallint", nullable=TRUE)
     * 0 no definido
     * 1 si trabaja los sabados
     * -1 no trabaja los sabados
     * aplica solo para tiempo completo
     */
    private $worksSaturday = 0;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $transportAid; // 1 vive donde trabaja - 0 externo

    /**
     * @ORM\Column(type="text", length=500, nullable=TRUE)
     */
    private $benefitsConditions;

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $salary;

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $holidayDebt;

    /**
     * @ORM\Column(type="date",nullable=TRUE)
     */
    private $holidayDebtDate;

    /**
     * @ORM\OneToMany(targetEntity="WeekWorkableDays", mappedBy="contractContract", cascade={"persist"})
     */
    private $weekWorkableDays;

    /**
     * @var ContractDocumentStatusType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="contract_document_status_type_id_contract_document_status_type", referencedColumnName="id_contract_document_status_type")
     * })
     */
    private $contractDocumentStatusType;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    private $backStatus=null;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EmployeeContractType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployeeContractType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_contract_type_id_employee_contract_type", referencedColumnName="id_employee_contract_type")
     * })
     */
    private $employeeContractTypeEmployeeContractType;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\TimeCommitment
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\TimeCommitment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="time_commitment_id_time_commitment", referencedColumnName="id_time_commitment")
     * })
     */
    private $timeCommitmentTimeCommitment;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Position
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Position")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="position_id_position", referencedColumnName="id_position")
     * })
     */
    private $positionPosition;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayMethod
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayMethod", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pay_method_id_pay_method", referencedColumnName="id_pay_method")
     * })
     */
    private $payMethodPayMethod;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_id_document", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $documentDocument;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="finish_contract_letter_document", referencedColumnName="id_document")
     * })
     * @Exclude
     */
    private $finishContractLetterDocument;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Liquidation
     * @ORM\OneToMany(targetEntity="Liquidation", mappedBy="contract", cascade={"persist", "remove"})
     * @Exclude
     */
    private $liquidations;

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
     * @ORM\Column(type="integer", nullable=TRUE)
     */
    private $workableDaysMonth;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PlanillaType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PlanillaType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="planilla_type_id_planilla_type", referencedColumnName="id_planilla_type")
     * })
     */
    private $planillaTypePlanillaType;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Supply", mappedBy="contractContract", cascade={"persist"})
     * @Exclude
     */
    private $supplies;

    /**
     * @ORM\Column(name="to_liquidate",type="smallint",length=1,nullable=true)
     */

    private $toLiquidate;

    /**
     * @ORM\Column(type="date", nullable=TRUE)
     */
    private $dateToEnd;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\ContractRecord", mappedBy="contractContract")
     */
    private $contractRecords;

    /**
     * Get idContract
     *
     * @return integer
     */
    public function getIdContract()
    {
        return $this->idContract;
    }

    /**
     * Set state
     *
     * @param boolean $state
     *
     * @return Contract
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return boolean
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set benefitsConditions
     *
     * @param string $benefitsConditions
     *
     * @return Contract
     */
    public function setBenefitsConditions($benefitsConditions)
    {
        $this->benefitsConditions = $benefitsConditions;

        return $this;
    }

    /**
     * Get benefitsConditions
     *
     * @return string
     */
    public function getBenefitsConditions()
    {
        return $this->benefitsConditions;
    }

    /**
     * Set salary
     *
     * @param float $salary
     *
     * @return Contract
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
     * Set employerHasEmployeeEmployerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee
     *
     * @return Contract
     */
    public function setEmployerHasEmployeeEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee = null)
    {
        $this->employerHasEmployeeEmployerHasEmployee = $employerHasEmployeeEmployerHasEmployee;

        return $this;
    }

    /**
     * Get employerHasEmployeeEmployerHasEmployee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployeeEmployerHasEmployee()
    {
        return $this->employerHasEmployeeEmployerHasEmployee;
    }

    /**
     * Set contractTypeContractType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType
     *
     * @return Contract
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
     * Set workplaceWorkplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WorkPlace $workplaceWorkplace
     *
     * @return Contract
     */
    public function setWorkplaceWorkplace(\RocketSeller\TwoPickBundle\Entity\WorkPlace $workplaceWorkplace = null)
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
     * Add payroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payroll
     *
     * @return Contract
     */
    public function addPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payroll)
    {
        $this->payrolls[] = $payroll;
        $payroll->setContractContract($this);
        return $this;
    }

    /**
     * Remove payroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payroll
     */
    public function removePayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payroll)
    {
        $this->payrolls->removeElement($payroll);
    }

    /**
     * Get payrolls
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayrolls()
    {
        return $this->payrolls;
    }

    /**
     * Add benefit
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasBenefits $benefit
     *
     * @return Contract
     */
    public function addBenefit(\RocketSeller\TwoPickBundle\Entity\ContractHasBenefits $benefit)
    {
        $benefit->setContractContract($this);
        $this->benefits[] = $benefit;

        return $this;
    }

    /**
     * Remove benefit
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasBenefits $benefit
     */
    public function removeBenefit(\RocketSeller\TwoPickBundle\Entity\ContractHasBenefits $benefit)
    {
        $this->benefits->removeElement($benefit);
    }

    /**
     * Get benefits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBenefits()
    {
        return $this->benefits;
    }

    /**
     * Set employeeContractTypeEmployeeContractType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployeeContractType $employeeContractTypeEmployeeContractType
     *
     * @return Contract
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
     * Set timeCommitmentTimeCommitment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\TimeCommitment $timeCommitmentTimeCommitment
     *
     * @return Contract
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
     * Set positionPosition
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Position $positionPosition
     *
     * @return Contract
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
     * @return Contract
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
     * Set documentDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $documentDocument
     *
     * @return Contract
     */
    public function setDocumentDocument(\RocketSeller\TwoPickBundle\Entity\Document $documentDocument = null)
    {
        $this->documentDocument = $documentDocument;

        return $this;
    }

    /**
     * Get documentDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocumentDocument()
    {
        return $this->documentDocument;
    }

    /**
     * Add liquidation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation
     *
     * @return Contract
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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Contract
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
     * @return Contract
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
     * Set workTimeStart
     *
     * @param \DateTime $workTimeStart
     *
     * @return Contract
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
     * @return Contract
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
     * Set workableDaysMonth
     *
     * @param integer $workableDaysMonth
     *
     * @return Contract
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
     * Set activePayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $activePayroll
     *
     * @return Contract
     */
    public function setActivePayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $activePayroll = null)
    {
        $this->activePayroll = $activePayroll;

        return $this;
    }

    /**
     * Get activePayroll
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Payroll
     */
    public function getActivePayroll()
    {
        return $this->activePayroll;
    }

    /**
     * Add weekWorkableDay
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay
     *
     * @return Contract
     */
    public function addWeekWorkableDay(\RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay)
    {
        $weekWorkableDay->setContractContract($this);
        $this->weekWorkableDays[] = $weekWorkableDay;

        return $this;
    }

    /**
     * Remove weekWorkableDay
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay
     */
    public function removeWeekWorkableDay(\RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay)
    {
        $weekWorkableDay->setContractContract(null);
        $this->weekWorkableDays->removeElement($weekWorkableDay);
    }

    /**
     * Get weekWorkableDays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWeekWorkableDays()
    {
        return $this->weekWorkableDays;
    }

    /**
     * Set frequencyFrequency
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Frequency $frequencyFrequency
     *
     * @return PayMethod
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
     * Set sisben
     *
     * @param integer $sisben
     *
     * @return Contract
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
     * @return Contract
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
     * Set testPeriod
     *
     * @param \DateTime $testPeriod
     *
     * @return Contract
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
     * Set holidayDebt
     *
     * @param float $holidayDebt
     *
     * @return Contract
     */
    public function setHolidayDebt($holidayDebt)
    {
        $this->holidayDebt = $holidayDebt;
        $this->holidayDebtDate = new \DateTime();
        return $this;
    }

    /**
     * Get holidayDebtDate
     *
     * @return \DateTime
     */
    public function getHolidayDebtDate()
    {
        return $this->holidayDebtDate;
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
     * Set planillaTypePlanillaType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PlanillaType $planillaTypePlanillaType
     *
     * @return Contract
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
     * Set backStatus
     *
     * @param integer $backStatus
     *
     * @return Contract
     */
    public function setBackStatus($backStatus)
    {
        $this->backStatus = $backStatus;

        return $this;
    }

    /**
     * Get backStatus
     *
     * @return integer
     */
    public function getBackStatus()
    {
        return $this->backStatus;
    }

    /**
     * Set contractDocumentStatusType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType $contractDocumentStatusType
     *
     * @return Contract
     */
    public function setContractDocumentStatusType(\RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType $contractDocumentStatusType = null)
    {
        $this->backStatus = $contractDocumentStatusType->getName();
        $this->contractDocumentStatusType = $contractDocumentStatusType;
        return $this;
    }

    /**
     * Get contractDocumentStatusType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType
     */
    public function getContractDocumentStatusType()
    {
        return $this->contractDocumentStatusType;
    }

    /**
     * Set holidayDebtDate
     *
     * @param \DateTime $holidayDebtDate
     *
     * @return Contract
     */
    public function setHolidayDebtDate($holidayDebtDate)
    {
        $this->holidayDebtDate = $holidayDebtDate;

        return $this;
    }


    /**
     * Add prima
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Prima $prima
     *
     * @return Contract
     */
    public function addPrima(\RocketSeller\TwoPickBundle\Entity\Prima $prima)
    {
        $prima->setContractContract($this);
        $this->primas[] = $prima;

        return $this;
    }

    /**
     * Remove prima
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Prima $prima
     */
    public function removePrima(\RocketSeller\TwoPickBundle\Entity\Prima $prima)
    {
        $this->primas->removeElement($prima);
    }

    /**
     * Get primas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrimas()
    {
        return $this->primas;
    }

    /**
     * Add supply
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Supply $supply
     *
     * @return Contract
     */
    public function addSupply(\RocketSeller\TwoPickBundle\Entity\Supply $supply)
    {
        $supply->setContractContract($this);
        $this->supplies[] = $supply;

        return $this;
    }

    /**
     * Remove supply
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Supply $supply
     */
    public function removeSupply(\RocketSeller\TwoPickBundle\Entity\Supply $supply)
    {
        $this->supplies->removeElement($supply);
    }


    /**
     * Get supplies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupplies()
    {
        return $this->supplies;
    }

    /**
     * Add contractRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecord
     *
     * @return Contract
     */
    public function addContractRecord(\RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecord)
    {
        $this->contractRecords[] = $contractRecord;

        return $this;
    }

    /**
     * Remove contractRecord
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecord
     */
    public function removeContractRecord(\RocketSeller\TwoPickBundle\Entity\ContractRecord $contractRecord)
    {
        $this->contractRecords->removeElement($contractRecord);
    }

    /**
     * Get contractRecords
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContractRecords()
    {
        return $this->contractRecords;
    }

    /**
     * Set toLiquidate
     *
     * @param integer $toLiquidate
     *
     * @return Contract
     */
    public function setToLiquidate($toLiquidate)
    {
        $this->toLiquidate = $toLiquidate;

        return $this;
    }

    /**
     * Get toLiquidate
     *
     * @return integer
     */
    public function getToLiquidate()
    {
        return $this->toLiquidate;
    }

    /**
     * Set dateToEnd
     *
     * @param \DateTime $dateToEnd
     *
     * @return Contract
     */
    public function setDateToEnd($dateToEnd)
    {
        $this->dateToEnd = $dateToEnd;

        return $this;
    }

    /**
     * Get dateToEnd
     *
     * @return \DateTime
     */
    public function getDateToEnd()
    {
        return $this->dateToEnd;
    }

    /**
     * Set finishContractLetterDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $finishContractLetterDocument
     *
     * @return Contract
     */
    public function setFinishContractLetterDocument(\RocketSeller\TwoPickBundle\Entity\Document $finishContractLetterDocument = null)
    {
        $this->finishContractLetterDocument = $finishContractLetterDocument;

        return $this;
    }

    /**
     * Get finishContractLetterDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getFinishContractLetterDocument()
    {
        return $this->finishContractLetterDocument;
    }

    /**
     * Get WeekWorkableDay By Day Number
     *
     * @return \RocketSeller\TwoPickBundle\Entity\WeekWorkableDays
     */
    public function getWorkableDayByDayNumber($dayNumber)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('dayNumber',$dayNumber));
        return $this->weekWorkableDays->matching($criteria)->first();
    }

    /**
     * Set worksSaturday
     *
     * @param integer $worksSaturday
     *
     * @return Contract
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payrolls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->primas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->severances = new \Doctrine\Common\Collections\ArrayCollection();
        $this->benefits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->weekWorkableDays = new \Doctrine\Common\Collections\ArrayCollection();
        $this->liquidations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->supplies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contractRecords = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add severance
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Severances $severance
     *
     * @return Contract
     */
    public function addSeverance(\RocketSeller\TwoPickBundle\Entity\Severances $severance)
    {
        $this->severances[] = $severance;

        return $this;
    }

    /**
     * Remove severance
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Severances $severance
     */
    public function removeSeverance(\RocketSeller\TwoPickBundle\Entity\Severances $severance)
    {
        $this->severances->removeElement($severance);
    }

    /**
     * Get severances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeverances()
    {
        return $this->severances;
    }
}
