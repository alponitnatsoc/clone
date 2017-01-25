<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Entity
 *
 * @ORM\Table(name="entity", indexes={@ORM\Index(name="fk_entity_entity_type1", columns={"entity_type_id_entity_type"})})
 * @ORM\Entity
 */
class Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_entity", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idEntity;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\EntityType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EntityType", inversedBy="entities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_type_id_entity_type", referencedColumnName="id_entity_type")
     * })
     */
    private $entityTypeEntityType;

    /**
     * @ORM\OneToMany(targetEntity="EntityFields", mappedBy="entityEntity", cascade={"persist"})
     * @Exclude
     */
    private $entityFields;

    /**
     * @ORM\OneToMany(targetEntity="Office", mappedBy="entityEntity", cascade={"persist"})
     */
    private $office;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $contact;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="entityEntity", cascade={"persist"})
     * @Exclude
     */
    private $action;

    /**
     * @ORM\OneToMany(targetEntity="EntityHasDocumentType", mappedBy="entityEntity", cascade={"persist"})
     */
    private $entityHasDocumentType;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $payroll_code;

    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $pila_code;

    /**
     * @ORM\ManyToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Department", inversedBy="entities", cascade={"persist"})
     * @ORM\JoinTable(name="entity_has_department",
     *      joinColumns={ @ORM\JoinColumn(name="entity_id_entity", referencedColumnName="id_entity")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="department_id_department", referencedColumnName="id_department")}
     *      )
     */
    private $departments;
	
		/**
		 * @ORM\Column(type="string", length=100, nullable=TRUE)
		 */
		private $address;
	
		/**
		 * @ORM\Column(type="string", length=100, nullable=TRUE)
		 */
		private $webpage;
	
		/**
		 * @ORM\Column(type="string", length=100, nullable=TRUE)
		 */
		private $phone;
	
		/**
		 * @ORM\Column(type="string", length=100, nullable=TRUE)
		 */
		private $attentionHours;

    /**
     * Set idEntity
     *
     * @param integer $idEntity
     *
     * @return Entity
     */
    public function setIdEntity($idEntity)
    {
        $this->idEntity = $idEntity;

        return $this;
    }

    /**
     * Get idEntity
     *
     * @return integer
     */
    public function getIdEntity()
    {
        return $this->idEntity;
    }

    /**
     * Set entityTypeEntityType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType
     *
     * @return Entity
     */
    public function setEntityTypeEntityType(\RocketSeller\TwoPickBundle\Entity\EntityType $entityTypeEntityType)
    {
        $this->entityTypeEntityType = $entityTypeEntityType;

        return $this;
    }

    /**
     * Get entityTypeEntityType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EntityType
     */
    public function getEntityTypeEntityType()
    {
        return $this->entityTypeEntityType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entityFields = new \Doctrine\Common\Collections\ArrayCollection();
        $this->office = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add entityField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityFields $entityField
     *
     * @return Entity
     */
    public function addEntityField(\RocketSeller\TwoPickBundle\Entity\EntityFields $entityField)
    {
        $this->entityFields[] = $entityField;

        return $this;
    }

    /**
     * Remove entityField
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityFields $entityField
     */
    public function removeEntityField(\RocketSeller\TwoPickBundle\Entity\EntityFields $entityField)
    {
        $this->entityFields->removeElement($entityField);
    }

    /**
     * Get entityFields
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntityFields()
    {
        return $this->entityFields;
    }

    /**
     * Add office
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Office $office
     *
     * @return Entity
     */
    public function addOffice(\RocketSeller\TwoPickBundle\Entity\Office $office)
    {
        $this->office[] = $office;

        return $this;
    }

    /**
     * Remove office
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Office $office
     */
    public function removeOffice(\RocketSeller\TwoPickBundle\Entity\Office $office)
    {
        $this->office->removeElement($office);
    }

    /**
     * Get office
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Entity
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
     * Set payroll code
     *
     * @param string $payroll_code
     *
     * @return PayType
     */
    public function setPayrollCode($payroll_code)
    {
        $this->payroll_code = $payroll_code;

        return $this;
    }

    /**
     * Get payroll_code
     *
     * @return string
     */
    public function getPayrollCode()
    {
        return $this->payroll_code;
    }

    /**
     * Set pila code
     *
     * @param string $pila_code
     *
     * @return PayType
     */
    public function setPilaCode($pila_code)
    {
        $this->pila_code = $pila_code;

        return $this;
    }

    /**
     * Get pila code
     *
     * @return string
     */
    public function getPilaCode()
    {
        return $this->pila_code;
    }


    /**
     * Add action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     *
     * @return Entity
     */
    public function addAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
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
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Add entityHasDocumentType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityHasDocumentType $entityHasDocumentType
     *
     * @return Entity
     */
    public function addEntityHasDocumentType(\RocketSeller\TwoPickBundle\Entity\EntityHasDocumentType $entityHasDocumentType)
    {
        $this->entityHasDocumentType[] = $entityHasDocumentType;

        return $this;
    }

    /**
     * Remove entityHasDocumentType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EntityHasDocumentType $entityHasDocumentType
     */
    public function removeEntityHasDocumentType(\RocketSeller\TwoPickBundle\Entity\EntityHasDocumentType $entityHasDocumentType)
    {
        $this->entityHasDocumentType->removeElement($entityHasDocumentType);
    }

    /**
     * Get entityHasDocumentType
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntityHasDocumentType()
    {
        return $this->entityHasDocumentType;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Entity
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add department
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Department $department
     *
     * @return Entity
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

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Entity
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
     * Set webpage
     *
     * @param string $webpage
     *
     * @return Entity
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;

        return $this;
    }

    /**
     * Get webpage
     *
     * @return string
     */
    public function getWebpage()
    {
        return $this->webpage;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Entity
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

    /**
     * Set attentionHours
     *
     * @param string $attentionHours
     *
     * @return Entity
     */
    public function setAttentionHours($attentionHours)
    {
        $this->attentionHours = $attentionHours;

        return $this;
    }

    /**
     * Get attentionHours
     *
     * @return string
     */
    public function getAttentionHours()
    {
        return $this->attentionHours;
    }
}
