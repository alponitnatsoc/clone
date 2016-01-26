<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tabla para guardar direcciones de facturacion
 * Billing Address
 *
 * @ORM\Table(name="billing_address")
 * @ORM\Entity
 */
class BillingAddress
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_billing_address", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idBillingAddress;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person")
     * @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * 
     */
    private $personPerson;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Country
     *
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Country")
     * @ORM\JoinColumn(name="id_country", referencedColumnName="id_country")
     * 
     */
    private $country;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Department
     * 
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Department")
     * @ORM\JoinColumn(name="id_department", referencedColumnName="id_department")
     * 
     */
    private $department;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\City
     * 
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\City")
     * @ORM\JoinColumn(name="id_city", referencedColumnName="id_city")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $personType;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $razonSocial;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $names;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $lastName1;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $lastName2;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $documentType;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $document;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $phone;

    /**
     * Constructor
     */
    public function __construct()
    {
        //$this->citys = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->address;
    }

    /**
     * Get idBillingAddress
     *
     * @return integer
     */
    public function getIdBillingAddress()
    {
        return $this->idBillingAddress;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return BillingAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return BillingAddress
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson = null)
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
     * Set country
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $country
     *
     * @return BillingAddress
     */
    public function setCountry(\RocketSeller\TwoPickBundle\Entity\Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     *
     * @return BillingAddress
     */
    public function setDepartment(\RocketSeller\TwoPickBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set city
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $city
     *
     * @return BillingAddress
     */
    public function setCity(\RocketSeller\TwoPickBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \RocketSeller\TwoPickBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set personType
     *
     * @param string $personType
     *
     * @return BillingAddress
     */
    public function setPersonType($personType)
    {
        $this->personType = $personType;

        return $this;
    }

    /**
     * Get personType
     *
     * @return string
     */
    public function getPersonType()
    {
        return $this->personType;
    }

    /**
     * Set razonSocial
     *
     * @param string $razonSocial
     *
     * @return BillingAddress
     */
    public function setRazonSocial($razonSocial)
    {
        $this->razonSocial = $razonSocial;

        return $this;
    }

    /**
     * Get razonSocial
     *
     * @return string
     */
    public function getRazonSocial()
    {
        return $this->razonSocial;
    }

    /**
     * Set names
     *
     * @param string $names
     *
     * @return BillingAddress
     */
    public function setNames($names)
    {
        $this->names = $names;

        return $this;
    }

    /**
     * Get names
     *
     * @return string
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * Set lastName1
     *
     * @param string $lastName1
     *
     * @return BillingAddress
     */
    public function setLastName1($lastName1)
    {
        $this->lastName1 = $lastName1;

        return $this;
    }

    /**
     * Get lastName1
     *
     * @return string
     */
    public function getLastName1()
    {
        return $this->lastName1;
    }

    /**
     * Set lastName2
     *
     * @param string $lastName2
     *
     * @return BillingAddress
     */
    public function setLastName2($lastName2)
    {
        $this->lastName2 = $lastName2;

        return $this;
    }

    /**
     * Get lastName2
     *
     * @return string
     */
    public function getLastName2()
    {
        return $this->lastName2;
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     *
     * @return BillingAddress
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set document
     *
     * @param string $document
     *
     * @return BillingAddress
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return BillingAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

}
