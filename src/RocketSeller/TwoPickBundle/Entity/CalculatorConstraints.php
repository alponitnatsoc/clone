<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccountType
 *
 * @ORM\Table(name="calculator_constraints")
 * @ORM\Entity
 */
class CalculatorConstraints
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_account_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCalculatorConstraints;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;
    /**
     * @ORM\Column(type="float")
     */
    private $value;




    /**
     * Get idCalculatorConstraints
     *
     * @return integer
     */
    public function getIdCalculatorConstraints()
    {
        return $this->idCalculatorConstraints;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CalculatorConstraints
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
     * Set value
     *
     * @param float $value
     *
     * @return CalculatorConstraints
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }
}
