<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity
 */
class Person
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_person", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPerson;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $fisrtName;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $surName;

    /**
     * @ORM\Column(type="string", length=40, nullable=TRUE)
     */
    private $lastName;

    /**
     * Get idPerson
     *
     * @return integer
     */
    public function getIdPerson()
    {
        return $this->idPerson;
    }

    /**
     * Set fisrtName
     *
     * @param string $fisrtName
     *
     * @return Person
     */
    public function setFisrtName($fisrtName)
    {
        $this->fisrtName = $fisrtName;

        return $this;
    }

    /**
     * Get fisrtName
     *
     * @return string
     */
    public function getFisrtName()
    {
        return $this->fisrtName;
    }

    /**
     * Set surName
     *
     * @param string $surName
     *
     * @return Person
     */
    public function setSurName($surName)
    {
        $this->surName = $surName;

        return $this;
    }

    /**
     * Get surName
     *
     * @return string
     */
    public function getSurName()
    {
        return $this->surName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Person
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }
}
