<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LiquidationReason
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class LiquidationReason
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=TRUE)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return LiquidationReason
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
     * Set description
     *
     * @param string $description
     *
     * @return LiquidationReason
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set payroll code
     *
     * @param string $payroll_code
     *
     * @return PayType
     */
    public function setPayrollCode($payroll_code)
    {
        $this->payroll_code = $payroll_code;

        return $this;
    }

    /**
     * Get payroll_code
     *
     * @return string
     */
    public function getPayrollCode()
    {
        return $this->payroll_code;
    }
}
