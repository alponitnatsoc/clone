<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Frequency
 *
 * @ORM\Table(name="frequency")
 * @ORM\Entity
 */
class Frequency
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_frequency", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idFrequency;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;



    /**
     * Get idFrequency
     *
     * @return integer
     */
    public function getIdFrequency()
    {
        return $this->idFrequency;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Frequency
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
