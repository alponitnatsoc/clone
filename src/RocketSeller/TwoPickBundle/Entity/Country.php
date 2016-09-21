<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Country
 *
 * @ORM\Table(name="country")
 * @ORM\Entity
 */
class Country
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_country", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCountry;

    /**
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Id del continente al que pertenece el pais:
     * 01 - Unión Europea
     * 02 - Resto de Europa
     * 03 - Africa
     * 04 - América del Norte
     * 05 - Centro América y Caribe
     * 06 - Sudamerica
     * 07 - Asia
     * 08 - Oceanía
     */
    private $continente;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $countryCode;

    /**
     *
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Department", mappedBy="countryCountry")
     * @Exclude
     */
    private $departments;

    /**
     * Get idCountry
     *
     * @return integer
     */
    public function getIdCountry()
    {
        return $this->idCountry;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Country
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
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Set continente
     *
     * @param integer $continente
     *
     * @return Country
     */
    public function setContinente($continente)
    {
        $this->continente = $continente;

        return $this;
    }

    /**
     * Get continente
     *
     * @return integer
     */
    public function getContinente()
    {
        return $this->continente;
    }

    /**
     * Set countryCode
     *
     * @param integer $countryCode
     *
     * @return Country
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return integer
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->departments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     *
     * @return Country
     */
    public function addDepartment(\RocketSeller\TwoPickBundle\Entity\Department $department)
    {
        $this->departments[] = $department;

        return $this;
    }

    /**
     * Remove department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     */
    public function removeDepartment(\RocketSeller\TwoPickBundle\Entity\Department $department)
    {
        $this->departments->removeElement($department);
    }

    /**
     * Get departments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartments()
    {
        return $this->departments;
    }
}
