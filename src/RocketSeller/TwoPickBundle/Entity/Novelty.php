<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Novelty
 *
 * @ORM\Table(name="novelty", indexes={@ORM\Index(name="fk_novelty_payroll_detail1", columns={"payroll_detail_id_payroll_detail"})})
 * @ORM\Entity
 */
class Novelty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idNovelty;

    /**
     * @var \AppBundle\Entity\PayrollDetail
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\PayrollDetail")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_detail_id_payroll_detail", referencedColumnName="id_payroll_detail")
     * })
     */
    private $payrollDetailPayrollDetail;



    /**
     * Set idNovelty
     *
     * @param integer $idNovelty
     *
     * @return Novelty
     */
    public function setIdNovelty($idNovelty)
    {
        $this->idNovelty = $idNovelty;

        return $this;
    }

    /**
     * Get idNovelty
     *
     * @return integer
     */
    public function getIdNovelty()
    {
        return $this->idNovelty;
    }

    /**
     * Set payrollDetailPayrollDetail
     *
     * @param \AppBundle\Entity\PayrollDetail $payrollDetailPayrollDetail
     *
     * @return Novelty
     */
    public function setPayrollDetailPayrollDetail(\AppBundle\Entity\PayrollDetail $payrollDetailPayrollDetail)
    {
        $this->payrollDetailPayrollDetail = $payrollDetailPayrollDetail;

        return $this;
    }

    /**
     * Get payrollDetailPayrollDetail
     *
     * @return \AppBundle\Entity\PayrollDetail
     */
    public function getPayrollDetailPayrollDetail()
    {
        return $this->payrollDetailPayrollDetail;
    }
}
