<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterType
 *
 * @ORM\Table(name="filter_type")
 * @ORM\Entity
 */
class FilterType
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_filter_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFilterType;
    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $name;

    /**
     * Get idFilterType
     *
     * @return integer
     */
    public function getIdFilterType()
    {
        return $this->idFilterType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FilterType
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
