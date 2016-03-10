<?php

namespace RocketSeller\TwoPickBundle\Entity;

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
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idNovelty;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\PayrollDetail
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayrollDetail", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_detail_id_payroll_detail", referencedColumnName="id_payroll_detail")
     * })
     */
    private $payrollDetailPayrollDetail;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Payroll
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Payroll", inversedBy="novelties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="payroll_id_payroll", referencedColumnName="id_payroll")
     * })
     */
    private $payrollPayroll;
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\NoveltyType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\NoveltyType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="novelty_type_id_novelty_type", referencedColumnName="id_novelty_type")
     * })
     */
    private $noveltyTypeNoveltyType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Document", cascade={"persist"})
     * @ORM\JoinTable(name="novelty_has_documents",
     *      joinColumns={ @ORM\JoinColumn(name="novelty_id_novelty", referencedColumnName="id_novelty")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="document_id_document", referencedColumnName="id_document")}
     *      )
     */
    private $documents;

    /**
     * @ORM\Column(type="date", nullable=TRUE, name="date_start")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="date", nullable=TRUE, name="date_end")
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="string", nullable=TRUE, name="units", length=100)
     */
    private $units;

    /**
     * @ORM\Column(type="string", nullable=TRUE, name="amount", length=100)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", nullable=TRUE, name="description", length=200)
     */
    private $description;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Novelty
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
     * Set payrollDetailPayrollDetail
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetailPayrollDetail
     *
     * @return Novelty
     */
    public function setPayrollDetailPayrollDetail(\RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetailPayrollDetail = null)
    {
        $this->payrollDetailPayrollDetail = $payrollDetailPayrollDetail;

        return $this;
    }

    /**
     * Get payrollDetailPayrollDetail
     *
     * @return \RocketSeller\TwoPickBundle\Entity\PayrollDetail
     */
    public function getPayrollDetailPayrollDetail()
    {
        return $this->payrollDetailPayrollDetail;
    }

    /**
     * Set noveltyTypeNoveltyType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType
     *
     * @return Novelty
     */
    public function setNoveltyTypeNoveltyType(\RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType = null)
    {
        $this->noveltyTypeNoveltyType = $noveltyTypeNoveltyType;

        return $this;
    }

    /**
     * Get noveltyTypeNoveltyType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\NoveltyType
     */
    public function getNoveltyTypeNoveltyType()
    {
        return $this->noveltyTypeNoveltyType;
    }

    /**
     * Add document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $document
     *
     * @return Novelty
     */
    public function addDocument(\RocketSeller\TwoPickBundle\Entity\Document $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * Remove document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $document
     */
    public function removeDocument(\RocketSeller\TwoPickBundle\Entity\Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return Novelty
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateEnd
     *
     * @param \DateTime $dateEnd
     *
     * @return Novelty
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get dateEnd
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * Set units
     *
     * @param string $units
     *
     * @return Novelty
     */
    public function setUnits($units)
    {
        $this->units = $units;

        return $this;
    }

    /**
     * Get units
     *
     * @return string
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Novelty
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * Set payrollPayroll
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Payroll $payrollPayroll
     *
     * @return Novelty
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
     * Set description
     *
     * @param string $description
     *
     * @return Novelty
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
}
