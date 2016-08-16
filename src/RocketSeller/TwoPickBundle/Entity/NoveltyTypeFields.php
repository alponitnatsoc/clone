<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyTypeFields
 *
 * @ORM\Table(name="novelty_type_fields")
 * @ORM\Entity(repositoryClass="NoveltyTypeFieldsRepository")
 */
class NoveltyTypeFields
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idNoveltyTypeFields;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\NoveltyType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\NoveltyType", inversedBy="requiredFields")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="novelty_type_id_novelty_type", referencedColumnName="id_novelty_type")
     * })
     */
    private $noveltyTypeNoveltyType;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $columnName;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $dataType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayable;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $noveltyDataConstrain;
    
    /**
     * Get idNoveltyTypeFields
     *
     * @return integer
     */
    public function getIdNoveltyTypeFields()
    {
        return $this->idNoveltyTypeFields;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NoveltyTypeFields
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
     * Set columnName
     *
     * @param string $columnName
     *
     * @return NoveltyTypeFields
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get columnName
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set dataType
     *
     * @param string $dataType
     *
     * @return NoveltyTypeFields
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set noveltyTypeNoveltyType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType
     *
     * @return NoveltyTypeFields
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
     * Set displayable
     *
     * @param boolean $displayable
     *
     * @return NoveltyTypeFields
     */
    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;

        return $this;
    }

    /**
     * Get displayable
     *
     * @return boolean
     */
    public function getDisplayable()
    {
        return $this->displayable;
    }

    /**
     * Set noveltyDataConstrain
     *
     * @param string $noveltyDataConstrain
     *
     * @return NoveltyTypeFields
     */
    public function setNoveltyDataConstrain($noveltyDataConstrain)
    {
        $this->noveltyDataConstrain = $noveltyDataConstrain;

        return $this;
    }

    /**
     * Get noveltyDataConstrain
     *
     * @return string
     */
    public function getNoveltyDataConstrain()
    {
        return $this->noveltyDataConstrain;
    }
}
