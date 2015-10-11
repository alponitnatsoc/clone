<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayrollDetail
 *
 * @ORM\Table(name="payroll_detail", indexes={@ORM\Index(name="fk_payroll_detail_payroll1", columns={"payroll_id_payroll"})})
 * @ORM\Entity
 */
class PayrollDetail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_payroll_detail", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idPayrollDetail;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;



    /**
     * Set idPayrollDetail
     *
     * @param integer $idPayrollDetail
     *
     * @return PayrollDetail
     */
    public function setIdPayrollDetail($idPayrollDetail)
    {
        $this->idPayrollDetail = $idPayrollDetail;

        return $this;
    }

    /**
     * Get idPayrollDetail
     *
     * @return integer
     */
    public function getIdPayrollDetail()
    {
        return $this->idPayrollDetail;
    }

    /**
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return PayrollDetail
     */
    public function setPayrollPayroll(\RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll)
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
}
