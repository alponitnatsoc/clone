<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Employee
 *
 * @ORM\Table(name="employee", indexes={@ORM\Index(name="fk_employee_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class Employee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_employee", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idEmployee;

    /**
     * @var \AppBundle\Entity\Person
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;



    /**
     * Set idEmployee
     *
     * @param integer $idEmployee
     *
     * @return Employee
     */
    public function setIdEmployee($idEmployee)
    {
        $this->idEmployee = $idEmployee;

        return $this;
    }

    /**
     * Get idEmployee
     *
     * @return integer
     */
    public function getIdEmployee()
    {
        return $this->idEmployee;
    }

    /**
     * Set personPerson
     *
     * @param \AppBundle\Entity\Person $personPerson
     *
     * @return Employee
     */
    public function setPersonPerson(\AppBundle\Entity\Person $personPerson)
    {
        $this->personPerson = $personPerson;

        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \AppBundle\Entity\Person
     */
    public function getPersonPerson()
    {
        return $this->personPerson;
    }
}
