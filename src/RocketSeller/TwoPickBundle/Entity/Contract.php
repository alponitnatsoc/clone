<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\OneToMany(targetEntity="ContractHasWorkplace", mappedBy="contractContract", cascade={"persist"})
     */
    private $workplaces;

    /**
     * @ORM\OneToMany(targetEntity="Payroll", mappedBy="contractContract", cascade={"persist"})
     */
    private $payrolls;
    /**
     * @ORM\OneToMany(targetEntity="ContractHasBenefits", mappedBy="contractContract", cascade={"persist"})
     */
    private $benefits;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $state;
    /**
     * @ORM\Column(type="text", length=200, nullable=TRUE)
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
     * Set idContract
     *
     * @param integer $idContract
     *
     * @return Contract
     */
    public function setIdContract($idContract)
    {
        $this->idContract = $idContract;

        return $this;
    }

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
     * Set employerHasEmployeeEmployerHasEmployee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee
     *
     * @return Contract
     */
    public function setEmployerHasEmployeeEmployerHasEmployee(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeEmployerHasEmployee)
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
    public function setContractTypeContractType(\RocketSeller\TwoPickBundle\Entity\ContractType $contractTypeContractType)
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
     * Constructor
     */
    public function __construct()
    {
        $this->workplaces = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set state
     *
     * @param string $state
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
     * @return string
     */
    public function getState()
    {
        return $this->state;
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
     * Add workplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplace
     *
     * @return Contract
     */
    public function addWorkplace(\RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplace)
    {
        $workplace->setContractContract($this);
        $this->workplaces[] = $workplace;

        return $this;
    }

    /**
     * Remove workplace
     *
     * @param \RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplace
     */
    public function removeWorkplace(\RocketSeller\TwoPickBundle\Entity\ContractHasWorkplace $workplace)
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
}
