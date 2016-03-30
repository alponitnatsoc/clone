<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Beneficiary
 *
 * @ORM\Table(name="beneficiary", indexes={@ORM\Index(name="fk_beneficiary_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class Beneficiary
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_beneficiary", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idBeneficiary;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @ORM\Column(type="smallint")
     */
    private $disability;

    /**
     * Set idBeneficiary
     *
     * @param integer $idBeneficiary
     *
     * @return Beneficiary
     */
    public function setIdBeneficiary($idBeneficiary)
    {
        $this->idBeneficiary = $idBeneficiary;

        return $this;
    }

    /**
     * Get idBeneficiary
     *
     * @return integer
     */
    public function getIdBeneficiary()
    {
        return $this->idBeneficiary;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Beneficiary
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
     * Set disability
     *
     * @param integer $disability
     *
     * @return Beneficiary
     */
    public function setDisability($disability)
    {
        $this->disability = $disability;

        return $this;
    }

    /**
     * Get disability
     *
     * @return integer
     */
    public function getDisability()
    {
        return $this->disability;
    }
}
