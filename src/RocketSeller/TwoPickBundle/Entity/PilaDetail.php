<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PilaDetail
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PilaDetail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pila_detail", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idPilaDetail;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="pilaDetails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @ORM\Column(type="float", nullable=TRUE)
     */
    private $sqlValueCia;

    /**
     * @ORM\Column(type="float", nullable=TRUE)
     */
    private $sqlValueEmp;



    /**
     * Get idPilaDetail
     *
     * @return integer
     */
    public function getIdPilaDetail()
    {
        return $this->idPilaDetail;
    }

    /**
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PilaDetail
     */
    public function setPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll = null)
    {
        $this->payrollPayroll = $payrollPayroll;

        return $this;
    }

    /**
     * Get payrollPayroll
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Payroll
     */
    public function getPayrollPayroll()
    {
        return $this->payrollPayroll;
    }

    /**
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return PilaDetail
     */
    public function setEntityEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entityEntity = null)
    {
        $this->entityEntity = $entityEntity;

        return $this;
    }

    /**
     * Get entityEntity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Entity
     */
    public function getEntityEntity()
    {
        return $this->entityEntity;
    }

    /**
     * Set sqlValueCia
     *
     * @param float $sqlValueCia
     *
     * @return PilaDetail
     */
    public function setSqlValueCia($sqlValueCia)
    {
        $this->sqlValueCia = $sqlValueCia;

        return $this;
    }

    /**
     * Get sqlValueCia
     *
     * @return float
     */
    public function getSqlValueCia()
    {
        return $this->sqlValueCia;
    }

    /**
     * Set sqlValueEmp
     *
     * @param float $sqlValueEmp
     *
     * @return PilaDetail
     */
    public function setSqlValueEmp($sqlValueEmp)
    {
        $this->sqlValueEmp = $sqlValueEmp;

        return $this;
    }

    /**
     * Get sqlValueEmp
     *
     * @return float
     */
    public function getSqlValueEmp()
    {
        return $this->sqlValueEmp;
    }
}
