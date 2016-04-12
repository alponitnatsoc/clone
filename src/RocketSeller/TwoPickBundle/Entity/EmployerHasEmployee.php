<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * EmployerHasEmployee
 *
 * @ORM\Table(name="employer_has_employee", indexes={@ORM\Index(name="fk_employer_has_employee_employer1", columns={"employer_id_employer"}), @ORM\Index(name="fk_employer_has_employee_employee1", columns={"employee_id_employee"})})
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
     */
    private $contracts;

    /**
     * @ORM\OneToMany(targetEntity="Liquidation", mappedBy="employerHasEmployee", cascade={"persist"})
     */
    private $liquidations;

    /**
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $state = 1;

    /**
     * @ORM\Column(type="integer", length=1, nullable=TRUE)
     */
    private $isFree = 0;

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

}
