<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityFields
 *
 * @ORM\Table(name="entity_fields", indexes={@ORM\Index(name="fk_entity_fields_entity1", columns={"entity_id_entity"})})
 * @ORM\Entity
 */
class EntityFields
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity_fields", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEntityFields;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Entity
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Entity", inversedBy="entityFields")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")
     * })
     */
    private $entityEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\FilterType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\FilterType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="filter_type_id_filter_type", referencedColumnName="id_filter_type")
     * })
     */
    private $filterTypeFilterType;

    /**
     * @ORM\OneToMany(targetEntity="SpecificData", mappedBy="entityFieldsEntityFields", cascade={"persist"})
     */
    private $specificData;
    /**
     * @ORM\Column(type="string", length=64, nullable=TRUE)
     */
    private $tableReferenced;
    /**
     * @ORM\Column(type="string", length=64, nullable=TRUE)
     */
    private $columnReferenced;
    /**
     * @ORM\Column(type="string", length=64, nullable=TRUE)
     */
    private $name;

    /**
     * Set idEntityFields
     *
     * @param integer $idEntityFields
     *
     * @return EntityFields
     */
    public function setIdEntityFields($idEntityFields)
    {
        $this->idEntityFields = $idEntityFields;

        return $this;
    }

    /**
     * Get idEntityFields
     *
     * @return integer
     */
    public function getIdEntityFields()
    {
        return $this->idEntityFields;
    }

    /**
     * Set entityEntity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Entity $entityEntity
     *
     * @return EntityFields
     */
    public function setEntityEntity(\RocketSeller\TwoPickBundle\Entity\Entity $entityEntity)
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
     * Set filterTypeFilterType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\FilterType $filterTypeFilterType
     *
     * @return EntityFields
     */
    public function setFilterTypeFilterType(\RocketSeller\TwoPickBundle\Entity\FilterType $filterTypeFilterType = null)
    {
        $this->filterTypeFilterType = $filterTypeFilterType;

        return $this;
    }

    /**
     * Get filterTypeFilterType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\FilterType
     */
    public function getFilterTypeFilterType()
    {
        return $this->filterTypeFilterType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->specificData = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add specificDatum
     *
     * @param \RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum
     *
     * @return EntityFields
     */
    public function addSpecificDatum(\RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum)
    {
        $this->specificData[] = $specificDatum;

        return $this;
    }

    /**
     * Remove specificDatum
     *
     * @param \RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum
     */
    public function removeSpecificDatum(\RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum)
    {
        $this->specificData->removeElement($specificDatum);
    }

    /**
     * Get specificData
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecificData()
    {
        return $this->specificData;
    }

    /**
     * Set tableRefecenced
     *
     * @param string $tableRefecenced
     *
     * @return EntityFields
     */
    public function setTableRefecenced($tableRefecenced)
    {
        $this->tableRefecenced = $tableRefecenced;

        return $this;
    }

    /**
     * Get tableRefecenced
     *
     * @return string
     */
    public function getTableRefecenced()
    {
        return $this->tableRefecenced;
    }

    /**
     * Set columnReferenced
     *
     * @param string $columnReferenced
     *
     * @return EntityFields
     */
    public function setColumnReferenced($columnReferenced)
    {
        $this->columnReferenced = $columnReferenced;

        return $this;
    }

    /**
     * Get columnReferenced
     *
     * @return string
     */
    public function getColumnReferenced()
    {
        return $this->columnReferenced;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EntityFields
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
     * Set tableReferenced
     *
     * @param string $tableReferenced
     *
     * @return EntityFields
     */
    public function setTableReferenced($tableReferenced)
    {
        $this->tableReferenced = $tableReferenced;

        return $this;
    }

    /**
     * Get tableReferenced
     *
     * @return string
     */
    public function getTableReferenced()
    {
        return $this->tableReferenced;
    }
    public function __toString()
    {
        return (string) $this->name;
    }
}
