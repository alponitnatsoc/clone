<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Position
 *
 * @ORM\Table(name="position")
 * @ORM\Entity
 */
class Position
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_position", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPosition;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_coverage_code;


    /**
     * @ORM\OneToMany(targetEntity="CalculatorConstraints", mappedBy="positionPosition")
     */
    private $constraints;



    /**
     * Get idPosition
     *
     * @return integer
     */
    public function getIdPosition()
    {
        return $this->idPosition;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Position
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->constraints = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add constraint
     *
     * @param \RocketSeller\TwoPickBundle\Entity\CalculatorConstraints $constraint
     *
     * @return Position
     */
    public function addConstraint(\RocketSeller\TwoPickBundle\Entity\CalculatorConstraints $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * Remove constraint
     *
     * @param \RocketSeller\TwoPickBundle\Entity\CalculatorConstraints $constraint
     */
    public function removeConstraint(\RocketSeller\TwoPickBundle\Entity\CalculatorConstraints $constraint)
    {
        $this->constraints->removeElement($constraint);
    }

    /**
     * Get constraints
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Set payrollCoverageCode
     *
     * @param string $payrollCoverageCode
     *
     * @return Position
     */
    public function setPayrollCoverageCode($payrollCoverageCode)
    {
        $this->payroll_coverage_code = $payrollCoverageCode;

        return $this;
    }

    /**
     * Get payrollCoverageCode
     *
     * @return string
     */
    public function getPayrollCoverageCode()
    {
        return $this->payroll_coverage_code;
    }
}
