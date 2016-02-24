<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\NotificationRepository")
 */
class Notification {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=TRUE)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=TRUE)
     */
    private $title;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person", inversedBy="docs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Role
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Role", inversedBy="roleHasTask")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id_role", referencedColumnName="id_role")
     * })
     */
    private $roleRole;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sawDate", type="datetime", nullable=TRUE)
     */
    private $sawDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="relatedlink", type="string", length=100, nullable=TRUE)
     */
    private $relatedLink;

    /**
     * @var string
     *
     * @ORM\Column(name="accion", type="string", length=100, nullable=TRUE)
     */
    private $accion;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=TRUE)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline", type="datetime", nullable=TRUE)
     */
    private $deadline;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Liquidation
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Liquidation", inversedBy="notifications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="liquidation", referencedColumnName="id")
     * })
     */
    private $liquidation;

    public function _construct() {
        $this->sawDate = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Notification
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Notification
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set sawDate
     *
     * @param \DateTime $sawDate
     *
     * @return Notification
     */
    public function setSawDate($sawDate) {
        $this->sawDate = $sawDate;

        return $this;
    }

    /**
     * Get sawDate
     *
     * @return \DateTime
     */
    public function getSawDate() {
        return $this->sawDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Notification
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set relatedLink
     *
     * @param string $relatedLink
     *
     * @return Notification
     */
    public function setRelatedLink($relatedLink) {
        $this->relatedLink = $relatedLink;

        return $this;
    }

    /**
     * Get relatedLink
     *
     * @return string
     */
    public function getRelatedLink() {
        return $this->relatedLink;
    }

    /**
     * Set accion
     *
     * @param string $accion
     *
     * @return Notification
     */
    public function setAccion($accion) {
        $this->accion = $accion;

        return $this;
    }

    /**
     * Get accion
     *
     * @return string
     */
    public function getAccion() {
        return $this->accion;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Notification
     */
    public function setDescription($description) {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set deadline
     *
     * @param \DateTime $deadline
     *
     * @return Notification
     */
    public function setDeadline($deadline) {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get deadline
     *
     * @return \DateTime
     */
    public function getDeadline() {
        return $this->deadline;
    }

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return Notification
     */
    public function setPersonPerson(\RocketSeller\TwoPickBundle\Entity\Person $personPerson = null) {
        $this->personPerson = $personPerson;

        return $this;
    }

    /**
     * Get personPerson
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Person
     */
    public function getPersonPerson() {
        return $this->personPerson;
    }


    /**
     * Set roleRole
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Role $roleRole
     *
     * @return Notification
     */
    public function setRoleRole(\RocketSeller\TwoPickBundle\Entity\Role $roleRole = null)
    {
        $this->roleRole = $roleRole;

        return $this;
    }

    /**
     * Get roleRole
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Role
     */
    public function getRoleRole()
    {
        return $this->roleRole;
    }

    /**
     * Set liquidation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation
     *
     * @return Notification
     */
    public function setLiquidation(\RocketSeller\TwoPickBundle\Entity\Liquidation $liquidation = null)
    {
        $this->liquidation = $liquidation;

        return $this;
    }

    /**
     * Get liquidation
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Liquidation
     */
    public function getLiquidation()
    {
        return $this->liquidation;
    }
}
