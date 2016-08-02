<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractType
 *
 * @ORM\Table(name="contract_type")
 * @ORM\Entity
 */
class ContractType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idContractType;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;


    /**
     * Get idContractType
     *
     * @return integer
     */
    public function getIdContractType()
    {
        return $this->idContractType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ContractType
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
