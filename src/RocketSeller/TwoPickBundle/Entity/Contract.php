<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RocketSeller\TwoPickBundle\RocketSellerTwoPickBundle;

/**
 * Contract
 *
 * @ORM\Table(name="contract", indexes={@ORM\Index(name="fk_contract_employer_has_employee1", columns={"employer_has_employee_id_employer_has_employee"}), @ORM\Index(name="fk_contract_contract_type1", columns={"contract_type_id_contract_type"}), @ORM\Index(name="fk_contract_document1", columns={"document_id_document"})})
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
     */
    private $payrolls;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="active_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $activePayroll;

    /**
     * @ORM\OneToMany(targetEntity="ContractHasBenefits", mappedBy="contractContract", cascade={"persist"})
     */
    private $benefits;

    /**
     * @ORM\OneToMany(targetEntity="WeekWorkableDays", mappedBy="contractContract", cascade={"persist"})
     */
    private $weekWorkableDays;

    /**
     * @ORM\Column(type="boolean", nullable=TRUE)
     */
    private $state = 1;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $transportAid;

    /**
     * @ORM\Column(type="text", length=500, nullable=TRUE)
     */
    private $benefitsConditions;

    /**
     * @ORM\Column(type="float",  nullable=TRUE)
     */
    private $salary;

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
     */
    private $documentDocument;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Liquidation
     * @ORM\OneToMany(targetEntity="Liquidation", mappedBy="contract", cascade={"persist", "remove"})
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
     * Set transportAid
     *
     * @param string $transportAid
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
     * @return string
     */
    public function getTransportAid()
    {
        return $this->transportAid;
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
     * Add weekWorkableDay
     *
     * @param \RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay
     *
     * @return Contract
     */
    public function addWeekWorkableDay(\RocketSeller\TwoPickBundle\Entity\WeekWorkableDays $weekWorkableDay)
    {
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
     * Constructor
     */
    public function __construct()
    {
        $this->payrolls = new \Doctrine\Common\Collections\ArrayCollection();
        $this->benefits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->weekWorkableDays = new \Doctrine\Common\Collections\ArrayCollection();
        $this->liquidations = new \Doctrine\Common\Collections\ArrayCollection();
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

}
