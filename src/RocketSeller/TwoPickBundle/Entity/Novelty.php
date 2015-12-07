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
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\PayrollDetail")
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
     * @ORM\OneToMany(targetEntity="NoveltyHasDocument", mappedBy="noveltyTypeNoveltyType", cascade={"persist"})
     */
    private $documents;

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
     * @param \RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetailPayrollDetail
     *
     * @return Novelty
     */
    public function setPayrollDetailPayrollDetail(\RocketSeller\TwoPickBundle\Entity\PayrollDetail $payrollDetailPayrollDetail)
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
     * Constructor
     */
    public function __construct()
    {
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument $document
     *
     * @return Novelty
     */
    public function addDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument $document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * Remove document
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument $document
     */
    public function removeDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyHasDocument $document)
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
}
