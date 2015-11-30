<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeeContractType
 *
 * @ORM\Table(name="employee_contract_type")
 * @ORM\Entity
 */
class EmployeeContractType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employee_contract_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmployeeContractType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;
    /**
     * @ORM\OneToMany(targetEntity="CalculatorConstraints", mappedBy="$employeeContractTypeEmployeeContractType")
     */
    private $constraints;


    /**
     * Get idEmployeeContractType
     *
     * @return integer
     */
    public function getIdEmployeeContractType()
    {
        return $this->idEmployeeContractType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EmployeeContractType
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
     * @return EmployeeContractType
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
