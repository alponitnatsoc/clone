<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * EntityRecord
 *
 * @ORM\Table(name="entity_record")
 * @ORM\Entity
 */
class EntityRecord
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity_record", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEntityRecord;

    /**
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee", inversedBy="entitiesRecords")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_has_employee_id", referencedColumnName="id_employer_has_employee", nullable=false)
     *   })
     */
    private $employerHasEmployeeId;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EntityType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_type_id_entity_type", referencedColumnName="id_entity_type")
     * })
     */
    private $entityTypeEntityType;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;

    /**
     * @ORM\Column(name="coverage_code",type="smallint",nullable=TRUE)
     */
    private $coverageCode;

    /**
     * @ORM\Column(name="date_changes_applied", type="date", nullable=TRUE)
     */
    private $dateChangesApplied;

    /**
     * @ORM\Column(name="date_to_be_applied", type="date", nullable=TRUE)
     */
    private $dateToBeApplied;

    /**
     * 0 - Changes has been applied
     * 1 - Changes are pending
     * @ORM\Column(name="to_be_executed", type="smallint", nullable=TRUE)
     */
    private $toBeExecuted;

    

    /**
     * Get idEntityRecord
     *
     * @return integer
     */
    public function getIdEntityRecord()
    {
        return $this->idEntityRecord;
    }

    /**
     * Set payrollCode
     *
     * @param string $payrollCode
     *
     * @return EntityRecord
     */
    public function setPayrollCode($payrollCode)
    {
        $this->payroll_code = $payrollCode;

        return $this;
    }

    /**
     * Get payrollCode
     *
     * @return string
     */
    public function getPayrollCode()
    {
        return $this->payroll_code;
    }

    /**
     * Set coverageCode
     *
     * @param integer $coverageCode
     *
     * @return EntityRecord
     */
    public function setCoverageCode($coverageCode)
    {
        $this->coverageCode = $coverageCode;

        return $this;
    }

    /**
     * Get coverageCode
     *
     * @return integer
     */
    public function getCoverageCode()
    {
        return $this->coverageCode;
    }

    /**
     * Set dateChangesApplied
     *
     * @param \DateTime $dateChangesApplied
     *
     * @return EntityRecord
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
     * Set dateToBeApplied
     *
     * @param \DateTime $dateToBeApplied
     *
     * @return EntityRecord
     */
    public function setDateToBeApplied($dateToBeApplied)
    {
        $this->dateToBeApplied = $dateToBeApplied;

        return $this;
    }

    /**
     * Get dateToBeApplied
     *
     * @return \DateTime
     */
    public function getDateToBeApplied()
    {
        return $this->dateToBeApplied;
    }

    /**
     * Set toBeExecuted
     *
     * @param integer $toBeExecuted
     *
     * @return EntityRecord
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
     * Set employerHasEmployeeId
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeId
     *
     * @return EntityRecord
     */
    public function setEmployerHasEmployeeId(\RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee $employerHasEmployeeId)
    {
        $this->employerHasEmployeeId = $employerHasEmployeeId;

        return $this;
    }

    /**
     * Get employerHasEmployeeId
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee
     */
    public function getEmployerHasEmployeeId()
    {
        return $this->employerHasEmployeeId;
    }

    /**
     * Set entityTypeEntityType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType
     *
     * @return EntityRecord
     */
    public function setEntityTypeEntityType(\RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType = null)
    {
        $this->entityTypeEntityType = $entityTypeEntityType;

        return $this;
    }

    /**
     * Get entityTypeEntityType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EntityType
     */
    public function getEntityTypeEntityType()
    {
        return $this->entityTypeEntityType;
    }
}
