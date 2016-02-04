<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyType
 *
 * @ORM\Table(name="novelty_type")
 * @ORM\Entity
 */
class NoveltyType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idNoveltyType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="NoveltyTypeHasDocumentType", mappedBy="noveltyTypeNoveltyType", cascade={"persist"})
     */
    private $requiredDocuments;

    /**
     * @ORM\OneToMany(targetEntity="NoveltyTypeFields", mappedBy="noveltyTypeNoveltyType", cascade={"persist"})
     */
    private $requiredFields;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $absenteeism;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $period;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $grupo;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $naturaleza;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requiredDocuments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idNoveltyType
     *
     * @return integer
     */
    public function getIdNoveltyType()
    {
        return $this->idNoveltyType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NoveltyType
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
     * Set Absenteeism
     *
     * @param string $absenteeism
     *
     * @return PayType
     */
    public function setAbsenteeism($absenteeism)
    {
        $this->absenteeism = $absenteeism;

        return $this;
    }

    /**
     * Get Absenteeism
     *
     * @return string
     */
    public function getAbsenteeism()
    {
        return $this->absenteeism;
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

    /**
     * Set period
     *
     * @param string $period
     *
     * @return NoveltyType
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period
     *
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set naturaleza
     *
     * @param string $period
     *
     * @return NoveltyType
     */
    public function setNaturaleza($naturaleza)
    {
        $this->naturaleza = $naturaleza;

        return $this;
    }

    /**
     * Get naturaleza
     *
     * @return string
     */
    public function getNaturaleza()
    {
        return $this->naturaleza;
    }

    /**
     * Add requiredDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument
     *
     * @return NoveltyType
     */
    public function addRequiredDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument)
    {
        $requiredDocument->setNoveltyTypeNoveltyType=$this;
        $this->requiredDocuments[] = $requiredDocument;

        return $this;
    }

    /**
     * Remove requiredDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument
     */
    public function removeRequiredDocument(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType $requiredDocument)
    {
        $this->requiredDocuments->removeElement($requiredDocument);
    }

    /**
     * Get requiredDocuments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredDocuments()
    {
        return $this->requiredDocuments;
    }

    /**
     * Add requiredField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields $requiredField
     *
     * @return NoveltyType
     */
    public function addRequiredField(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields $requiredField)
    {
        $this->requiredFields[] = $requiredField;

        return $this;
    }

    /**
     * Remove requiredField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields $requiredField
     */
    public function removeRequiredField(\RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields $requiredField)
    {
        $this->requiredFields->removeElement($requiredField);
    }

    /**
     * Get requiredFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    /**
     * Set grupo
     *
     * @param string $grupo
     *
     * @return NoveltyType
     */
    public function setGrupo($grupo)
    {
        $this->grupo = $grupo;

        return $this;
    }

    /**
     * Get grupo
     *
     * @return string
     */
    public function getGrupo()
    {
        return $this->grupo;
    }
}
