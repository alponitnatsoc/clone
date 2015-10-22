<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityType
 *
 * @ORM\Table(name="entity_type")
 * @ORM\Entity
 */
class EntityType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEntityType;
    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $name;



    /**
     * Get idEntityType
     *
     * @return integer
     */
    public function getIdEntityType()
    {
        return $this->idEntityType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EntityType
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
}
