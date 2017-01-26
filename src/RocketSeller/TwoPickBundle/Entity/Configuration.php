<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Country
 *
 * @ORM\Table(name="configuration")
 * @ORM\Entity
 */
class Configuration
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_configuration", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idConfiguration;

    /**
     * @ORM\Column(type="string", length=50, nullable=false, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity="UserHasConfig", mappedBy="configurationConfiguration", cascade={"persist"})
     * @Exclude
     */
    private $configHasUsers;


    /**
     * Set idConfigurations
     *
     * @param integer $idConfiguration
     *
     * @return Configuration
     */
    public function setIdConfiguration($idConfiguration)
    {
        $this->idConfiguration = $idConfiguration;

        return $this;
    }

    /**
     * Get idConfiguration
     *
     * @return integer
     */
    public function getIdConfiguration()
    {
        return $this->idConfiguration;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Configuration
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
     * @return Configuration
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
     * @return Configuration
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configHasUsers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add configHasUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasConfig $configHasUser
     *
     * @return Configuration
     */
    public function addConfigHasUser(\RocketSeller\TwoPickBundle\Entity\UserHasConfig $configHasUser)
    {
        $this->configHasUsers[] = $configHasUser;

        return $this;
    }

    /**
     * Remove configHasUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasConfig $configHasUser
     */
    public function removeConfigHasUser(\RocketSeller\TwoPickBundle\Entity\UserHasConfig $configHasUser)
    {
        $this->configHasUsers->removeElement($configHasUser);
    }

    /**
     * Get configHasUsers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConfigHasUsers()
    {
        return $this->configHasUsers;
    }
}
