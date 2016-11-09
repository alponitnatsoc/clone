<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use RocketSeller\TwoPickBundle\RocketSellerTwoPickBundle;

/**
 * Person
 *
 * @ORM\Table(name="person",
 *      uniqueConstraints={@UniqueConstraint(name="documentUnique", columns={"document_type", "document"})},
 *      uniqueConstraints={@UniqueConstraint(name="uploadedDocumentUnique", columns={"document_id_document"})},
 *      uniqueConstraints={@uniqueConstraint(name="uploadedRutUnique",columns={"rut_id_document"})},
 *     uniqueConstraints={@uniqueConstraint(name="uploadedBirthRegUnique",columns={"registro_id_document"})}
 * )
 * @ORM\Entity(repositoryClass="PersonRepository")
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
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $names;

    /**
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $lastName1;

    /**
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $lastName2;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $documentType;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $document;

    /**
     * @var Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="document_id_document",referencedColumnName="id_document")
     * })
     */
    private $documentDocument;

    /** @var Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="rut_id_document",referencedColumnName="id_document")
     * )
     */
    private $rutDocument;

    /** @var Document
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Document")
     * @ORM\JoinColumns(
     *     @ORM\JoinColumn(name="registro_id_document",referencedColumnName="id_document")
     * )
     */
    private $birthRegDocument;

    /**
     * @ORM\Column(type="date", nullable=TRUE)
     */
    private $documentExpeditionDate;

    /**
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $documentExpeditionPlace;

    /**
     * @ORM\Column(type="string", length=50, nullable=TRUE)
     */
    private $email;

    /**
     * @ORM\Column(type="date", nullable=TRUE)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $mainAddress;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    private $neighborhood;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_department", referencedColumnName="id_department")
     * })
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="Phone", mappedBy="personPerson", cascade={"persist"})
     */
    private $phones;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="id_city", referencedColumnName="id_city")
     */
    private $city;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer", mappedBy="personPerson", cascade={"persist", "remove"})
     */
    private $employer;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Employee
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employee", mappedBy="personPerson", cascade={"persist", "remove"})
     * @Exclude
     */
    private $employee;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Document
     * @ORM\OneToMany(targetEntity="\RocketSeller\TwoPickBundle\Entity\Document", mappedBy="personPerson", cascade={"persist"})
     * @Exclude
     */
    private $docs;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Notification
     * @ORM\OneToMany(targetEntity="\RocketSeller\TwoPickBundle\Entity\Notification", mappedBy="personPerson", cascade={"persist"})
     * @Exclude
     */
    private $notifications;

    /**
     * @ORM\OneToMany(targetEntity="SpecificData", mappedBy="personPerson", cascade={"persist"})
     * @Exclude
     */
    private $specificData;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="personPerson", cascade={"persist"})
     * @Exclude
     */
    private $action;

    /**
     * @var \Application\Sonata\MediaBundle\Entity\Gallery
     * @ORM\OneToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Gallery", mappedBy="person", cascade={"persist"}, fetch="LAZY")
     * @Exclude
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_birth_country", referencedColumnName="id_country")
     * })
     */
    private $birthCountry;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_birth_department", referencedColumnName="id_department")
     * })
     */
    private $birthDepartment;

    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="id_birth_city", referencedColumnName="id_city")
     */
    private $birthCity;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $civilStatus;

    /**
     * @ORM\Column(type="string", length=20, nullable=TRUE)
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="BillingAddress", mappedBy="personPerson", cascade={"persist", "remove"})
     * @Exclude
     */
    private $billingAddress;

    /**
     * @ORM\ManyToMany(targetEntity="Configuration", cascade={"persist"})
     * @ORM\JoinTable(name="persons_has_configurations",
     *      joinColumns={ @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="configuration_id_configuration", referencedColumnName="id_configuration")}
     *      )
     */
    private $configurations;

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
     * Set names
     *
     * @param string $names
     *
     * @return Person
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
     * @return Person
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
     * @return Person
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

    public function getFullName()
    {
        return $this->getNames() . " " . $this->getLastName1() . " " . $this->getLastName2();
    }

    /**
     * Set documentType
     *
     * @param string $documentType
     *
     * @return Person
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
     * @return Person
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
     * Set email
     *
     * @param string $email
     *
     * @return Person
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return Person
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set mainAddress
     *
     * @param string $mainAddress
     *
     * @return Person
     */
    public function setMainAddress($mainAddress)
    {
        $this->mainAddress = $mainAddress;

        return $this;
    }

    /**
     * Get mainAddress
     *
     * @return string
     */
    public function getMainAddress()
    {
        return $this->mainAddress;
    }

    /**
     * Set neighborhood
     *
     * @param string $neighborhood
     *
     * @return Person
     */
    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    /**
     * Get neighborhood
     *
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * Set civilStatus
     *
     * @param string $civilStatus
     *
     * @return Person
     */
    public function setCivilStatus($civilStatus)
    {
        $this->civilStatus = $civilStatus;

        return $this;
    }

    /**
     * Get civilStatus
     *
     * @return string
     */
    public function getCivilStatus()
    {
        return $this->civilStatus;
    }

    /**
     * Set department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     *
     * @return Person
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
     * Add phone
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Phone $phone
     *
     * @return Person
     */
    public function addPhone(\RocketSeller\TwoPickBundle\Entity\Phone $phone)
    {
        $phone->setPersonPerson($this);
        $this->phones[] = $phone;

        return $this;
    }

    /**
     * Remove phone
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Phone $phone
     */
    public function removePhone(\RocketSeller\TwoPickBundle\Entity\Phone $phone)
    {
        $this->phones->removeElement($phone);
    }

    /**
     * Get phones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Set city
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $city
     *
     * @return Person
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
     * Set employer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employer
     *
     * @return Person
     */
    public function setEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employer = null)
    {
        $employer->setPersonPerson($this);
        $this->employer = $employer;

        return $this;
    }

    /**
     * Get employer
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employer
     */
    public function getEmployer()
    {
        return $this->employer;
    }

    /**
     * Set employee
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employee $employee
     *
     * @return Person
     */
    public function setEmployee(\RocketSeller\TwoPickBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * Add doc
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $doc
     *
     * @return Person
     */
    public function addDoc(\RocketSeller\TwoPickBundle\Entity\Document $doc)
    {
        $doc->setPersonPerson($this);
        $this->docs[] = $doc;

        return $this;
    }

    /**
     * Remove doc
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $doc
     */
    public function removeDoc(\RocketSeller\TwoPickBundle\Entity\Document $doc)
    {
        $this->docs->removeElement($doc);
    }

    /**
     * Get docs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * Add specificDatum
     *
     * @param \RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum
     *
     * @return Person
     */
    public function addSpecificDatum(\RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum)
    {
        $specificDatum->setPersonPerson($this);
        $this->specificData[] = $specificDatum;

        return $this;
    }

    /**
     * Remove specificDatum
     *
     * @param \RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum
     */
    public function removeSpecificDatum(\RocketSeller\TwoPickBundle\Entity\SpecificData $specificDatum)
    {
        $this->specificData->removeElement($specificDatum);
    }

    /**
     * Get specificData
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpecificData()
    {
        return $this->specificData;
    }

    /**
     * Add action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     *
     * @return Person
     */
    public function addAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
        $action->setPersonPerson($this);
        $this->action[] = $action;

        return $this;
    }

    /**
     * Remove action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     */
    public function removeAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
        $this->action->removeElement($action);
    }

    /**
     * Get action
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set gallery
     *
     * @param \Application\Sonata\MediaBundle\Entity\Gallery $gallery
     *
     * @return Person
     */
    public function setGallery(\Application\Sonata\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery
     *
     * @return \Application\Sonata\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set birthCountry
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Country $birthCountry
     *
     * @return Person
     */
    public function setBirthCountry(\RocketSeller\TwoPickBundle\Entity\Country $birthCountry = null)
    {
        $this->birthCountry = $birthCountry;

        return $this;
    }

    /**
     * Get birthCountry
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Country
     */
    public function getBirthCountry()
    {
        return $this->birthCountry;
    }

    /**
     * Set birthDepartment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $birthDepartment
     *
     * @return Person
     */
    public function setBirthDepartment(\RocketSeller\TwoPickBundle\Entity\Department $birthDepartment = null)
    {
        $this->birthDepartment = $birthDepartment;

        return $this;
    }

    /**
     * Get birthDepartment
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Department
     */
    public function getBirthDepartment()
    {
        return $this->birthDepartment;
    }

    /**
     * Set birthCity
     *
     * @param \RocketSeller\TwoPickBundle\Entity\City $birthCity
     *
     * @return Person
     */
    public function setBirthCity(\RocketSeller\TwoPickBundle\Entity\City $birthCity = null)
    {
        $this->birthCity = $birthCity;

        return $this;
    }

    /**
     * Get birthCity
     *
     * @return \RocketSeller\TwoPickBundle\Entity\City
     */
    public function getBirthCity()
    {
        return $this->birthCity;
    }

    /**
     * Set documentExpeditionDate
     *
     * @param \DateTime $documentExpeditionDate
     *
     * @return Person
     */
    public function setDocumentExpeditionDate($documentExpeditionDate)
    {
        $this->documentExpeditionDate = $documentExpeditionDate;

        return $this;
    }

    /**
     * Get documentExpeditionDate
     *
     * @return \DateTime
     */
    public function getDocumentExpeditionDate()
    {
        return $this->documentExpeditionDate;
    }

    /**
     * Set documentExpeditionPlace
     *
     * @param string $documentExpeditionPlace
     *
     * @return Person
     */
    public function setDocumentExpeditionPlace($documentExpeditionPlace)
    {
        $this->documentExpeditionPlace = $documentExpeditionPlace;

        return $this;
    }

    /**
     * Get documentExpeditionPlace
     *
      string */
    public function getDocumentExpeditionPlace()
    {
        return $this->documentExpeditionPlace;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return Person
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    public function __toString()
    {
        return (string) $this->getFullName();
    }

    /**
     * Add billingAddress
     *
     * @param \RocketSeller\TwoPickBundle\Entity\BillingAddress $billingAddress
     *
     * @return Person
     */
    public function addBillingAddress(\RocketSeller\TwoPickBundle\Entity\BillingAddress $billingAddress)
    {
        $billingAddress->setPersonPerson($this);
        $this->billingAddress[] = $billingAddress;

        return $this;
    }

    /**
     * Remove billingAddress
     *
     * @param \RocketSeller\TwoPickBundle\Entity\BillingAddress $billingAddress
     */
    public function removeBillingAddress(\RocketSeller\TwoPickBundle\Entity\BillingAddress $billingAddress)
    {
        $this->billingAddress->removeElement($billingAddress);
    }

    /**
     * Get billingAddress
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->phones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->docs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->specificData = new \Doctrine\Common\Collections\ArrayCollection();
        $this->action = new \Doctrine\Common\Collections\ArrayCollection();
        $this->billingAddress = new \Doctrine\Common\Collections\ArrayCollection();
        $this->configurations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add notification
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Notification $notification
     *
     * @return Person
     */
    public function addNotification(\RocketSeller\TwoPickBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Notification $notification
     */
    public function removeNotification(\RocketSeller\TwoPickBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add configuration
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Configuration $configuration
     *
     * @return Person
     */
    public function addConfiguration(\RocketSeller\TwoPickBundle\Entity\Configuration $configuration)
    {
        $this->configurations[] = $configuration;

        return $this;
    }

    /**
     * Set configuration
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Configuration $configuration
     *
     * @return Person
     */
    public function setConfiguration($configuration)
    {
        $this->configurations = $configuration;

        return $this;
    }

    /**
     * Remove configuration
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Configuration $configuration
     */
    public function removeConfiguration(\RocketSeller\TwoPickBundle\Entity\Configuration $configuration)
    {
        $this->configurations->removeElement($configuration);
    }

    /**
     * Get configurations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Set documentDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $documentDocument
     *
     * @return Person
     */
    public function setDocumentDocument(\RocketSeller\TwoPickBundle\Entity\Document $documentDocument = null)
    {
        $this->documentDocument = $documentDocument;

        return $this;
    }

    /**
     * Get documentDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getDocumentDocument()
    {
        return $this->documentDocument;
    }

    /**
     * Set rutDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $rutDocument
     *
     * @return Person
     */
    public function setRutDocument(\RocketSeller\TwoPickBundle\Entity\Document $rutDocument = null)
    {
        $this->rutDocument = $rutDocument;

        return $this;
    }

    /**
     * Get rutDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getRutDocument()
    {
        return $this->rutDocument;
    }

    /**
     * Set birthRegDocument
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Document $birthRegDocument
     *
     * @return Person
     */
    public function setBirthRegDocument(\RocketSeller\TwoPickBundle\Entity\Document $birthRegDocument = null)
    {
        $this->birthRegDocument = $birthRegDocument;

        return $this;
    }

    /**
     * Get birthRegDocument
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Document
     */
    public function getBirthRegDocument()
    {
        return $this->birthRegDocument;
    }

    /**
     * @param $actionType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsByActionType($actionType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('actionTypeActionType',$actionType));
        return $this->action->matching($criteria);
    }

    /**
     * @param $actionType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsNotMatching($actionType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->neq('actionTypeActionType',$actionType));
        return $this->action->matching($criteria);
    }

    /**
     * @param $employerHasEntity
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionByEmployerHasEntity($employerHasEntity){
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('employerEntity',$employerHasEntity));
        return $this->action->matching($criteria);
    }

    /**
     * @param $employeeHasEntity
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionByEmployeeHasEntity($employeeHasEntity){
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('employeeEntity',$employeeHasEntity));
        return $this->action->matching($criteria);
    }

    /**
     * @param string $id
     * @return RocketSellerTwoPickBundle:Action
     */
    public function getActionById($id)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('idAction',intval($id)));
        return $this->action->matching($criteria)->first();
    }

    /**
     * @param $actionStatus
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsByActionStatus($actionStatus)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('actionStatus',$actionStatus));
        return $this->action->matching($criteria);
    }

    /**
     * @param $actionStatus
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsNotMatchingActionStatus($actionStatus)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->neq('actionStatus',$actionStatus));
        return $this->action->matching($criteria);
    }

}
