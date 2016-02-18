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
         * Set payroll code
         *
         * @param string $payroll_code
         *
         * @return PayType
         */
        public function setPayrollCoverageCode($payroll_code)
        {
            $this->payroll_code = $payroll_code;

            return $this;
        }

        /**
         * Get payroll_code
         *
         * @return string
         */
        public function getPayrollCoverageCode()
        {
            return $this->payroll_code;
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
}
