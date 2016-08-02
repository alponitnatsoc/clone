<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_config", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idConfig;

    /**
     * @ORM\Column(type="string", length=50, nullable=false, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private $descripcion;

    /**
     * Set idConfig
     *
     * @param integer $idConfig
     *
     * @return Config
     */
    public function setIdConfig($idConfig)
    {
        $this->idConfig = $idConfig;

        return $this;
    }

    /**
     * Get idConfig
     *
     * @return integer
     */
    public function getIdConfig()
    {
        return $this->idConfig;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Config
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
     * Set value
     *
     * @param string $value
     *
     * @return Config
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Config
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }
}
