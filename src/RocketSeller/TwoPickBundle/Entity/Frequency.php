<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Frequency
 *
 * @ORM\Table(name="frequency")
 * @ORM\Entity
 */
class Frequency
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_frequency", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFrequency;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;
    /**
     * @ORM\Column(type="string", length=4)
     */
    private $payroll_code;




    /**
     * Get idFrequency
     *
     * @return integer
     */
    public function getIdFrequency()
    {
        return $this->idFrequency;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Frequency
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
