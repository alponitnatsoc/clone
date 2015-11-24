<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Log
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="docs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="json_array")
     */
    private $data;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Notification
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson)
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
     * Set type
     *
     * @param string $type
     *
     * @return Log
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return Log
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}

