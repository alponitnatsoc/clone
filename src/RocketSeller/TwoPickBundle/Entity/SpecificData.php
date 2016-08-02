<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterType
 *
 * @ORM\Table(name="specific_data")
 * @ORM\Entity
 */
class SpecificData
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_specific_data", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idSpecificData;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="specificData")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EntityFields
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityFields", inversedBy="specificData")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_fields_id_entity_fields", referencedColumnName="id_entity_fields")
     * })
     */
    private $entityFieldsEntityFields;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data;
    /**
     * Get idSpecificData
     *
     * @return integer
     */
    public function getIdSpecificData()
    {
        return $this->idSpecificData;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return SpecificData
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson = null)
    {
        $this->personPerson = $personPerson;

        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Person
     */
    public function getPersonPerson()
    {
        return $this->personPerson;
    }

    /**
     * Set entityFieldsEntityFields
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityFields $entityFieldsEntityFields
     *
     * @return SpecificData
     */
    public function setEntityFieldsEntityFields(\RocketSeller\TwoPickBundle\Entity\EntityFields $entityFieldsEntityFields = null)
    {
        $this->entityFieldsEntityFields = $entityFieldsEntityFields;

        return $this;
    }

    /**
     * Get entityFieldsEntityFields
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EntityFields
     */
    public function getEntityFieldsEntityFields()
    {
        return $this->entityFieldsEntityFields;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return SpecificData
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
}
