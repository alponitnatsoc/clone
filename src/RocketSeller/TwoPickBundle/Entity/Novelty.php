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
     * @ORM\Column(type="date", nullable=TRUE, name="pay_date")
     */
    private $payDate;

    /**
     * @ORM\Column(type="date", nullable=TRUE, name="date_start")
     */
    private $dateStart;

    /**
     * @ORM\Column(type="date", nullable=TRUE, name="date_end")
     */
    private $dateEnd;
    /**
     * @ORM\Column(type="float", length=20, nullable=TRUE, name="ammount_borrowed")
     */
    private $ammountBorrowed;





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
     * Set payDate
     *
     * @param \DateTime $payDate
     *
     * @return Novelty
     */
    public function setPayDate($payDate)
    {
        $this->payDate = $payDate;

        return $this;
    }

    /**
     * Get payDate
     *
     * @return \DateTime
     */
    public function getPayDate()
    {
        return $this->payDate;
    }

    /**
     * Set ammountBorrowed
     *
     * @param float $ammountBorrowed
     *
     * @return Novelty
     */
    public function setAmmountBorrowed($ammountBorrowed)
    {
        $this->ammountBorrowed = $ammountBorrowed;

        return $this;
    }

    /**
     * Get ammountBorrowed
     *
     * @return float
     */
    public function getAmmountBorrowed()
    {
        return $this->ammountBorrowed;
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
}
