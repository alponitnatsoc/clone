<?php

namespace RocketSeller\TwoPickBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="user", indexes={@ORM\Index(name="fk_user_person1", columns={"person_id_person"})})
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    protected $facebook_id;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    protected $google_id;

    /**
     * @ORM\Column(type="string", length=200, nullable=TRUE)
     */
    protected $linkedin_id;

    /**
     * @ORM\Column(type="text",  nullable=true)
     */
    protected $facebook_access_token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $google_access_token;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $linkedin_access_token;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Person
     *
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Person",cascade={"persist", "remove"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id_person", referencedColumnName="id_person")
     * })
     */
    private $personPerson;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="userUser", cascade={"persist"})
     */
    private $action;

    /**
     * @ORM\OneToMany(targetEntity="RealProcedure", mappedBy="userUser", cascade={"persist"})
     */
    private $realProcedure;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseOrders", mappedBy="idUser", cascade={"persist"})
     */
    private $purchaseOrders;

    /**
     * @ORM\OneToMany(targetEntity="Pay", mappedBy="userIdUser", cascade={"persist"})
     */
    private $payments;

    /**
     * Columna utilizada para conocer el estado de la suscripcion del usuario
     * 0 Inactivo
     * 1 Activo
     * 2 Free
     * 3 Free3
     * 
     * @var \SmallIntType
     * 
     * @ORM\Column(type="smallint")
     */
    private $status = 2;

    /**
     * Set personPerson
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Person $personPerson
     *
     * @return User
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
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set googleId
     *
     * @param string $googleId
     *
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->google_id = $googleId;

        return $this;
    }

    /**
     * Get googleId
     *
     * @return string
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set linkedinId
     *
     * @param string $linkedinId
     *
     * @return User
     */
    public function setLinkedinId($linkedinId)
    {
        $this->linkedin_id = $linkedinId;

        return $this;
    }

    /**
     * Get linkedinId
     *
     * @return string
     */
    public function getLinkedinId()
    {
        return $this->linkedin_id;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Set googleAccessToken
     *
     * @param string $googleAccessToken
     *
     * @return User
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->google_access_token = $googleAccessToken;

        return $this;
    }

    /**
     * Get googleAccessToken
     *
     * @return string
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    /**
     * Set linkedinAccessToken
     *
     * @param string $linkedinAccessToken
     *
     * @return User
     */
    public function setLinkedinAccessToken($linkedinAccessToken)
    {
        $this->linkedin_access_token = $linkedinAccessToken;

        return $this;
    }

    /**
     * Get linkedinAccessToken
     *
     * @return string
     */
    public function getLinkedinAccessToken()
    {
        return $this->linkedin_access_token;
    }

    /**
     * Add action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     *
     * @return User
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

    /**
     * Add realProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure
     *
     * @return User
     */
    public function addRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure)
    {
        $this->realProcedure[] = $realProcedure;

        return $this;
    }

    /**
     * Remove realProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure
     */
    public function removeRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure)
    {
        $this->realProcedure->removeElement($realProcedure);
    }

    /**
     * Get realProcedure
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRealProcedure()
    {
        return $this->realProcedure;
    }

    /**
     * Add purchaseOrder
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrder
     *
     * @return User
     */
    public function addPurchaseOrder(\RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrder)
    {
        $this->purchaseOrders[] = $purchaseOrder;

        return $this;
    }

    /**
     * Remove purchaseOrder
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrder
     */
    public function removePurchaseOrder(\RocketSeller\TwoPickBundle\Entity\PurchaseOrders $purchaseOrder)
    {
        $this->purchaseOrders->removeElement($purchaseOrder);
    }

    /**
     * Get purchaseOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchaseOrders()
    {
        return $this->purchaseOrders;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return User
     * Estados del usuario:
     *      0 - Inactivo / Suscripcion desactivada o inactiva
     *      1 - Activo / Suscripcion activa
     *      2 - Free / Periodo gratis para el usuario primer mes
     *      3 - Free3 / Periodo gratis para el usuario +2 meses por completar registro 48 horas total 3 meses gratis
     *
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

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
     * Add payment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Pay $payment
     *
     * @return User
     */
    public function addPayment(\RocketSeller\TwoPickBundle\Entity\Pay $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Pay $payment
     */
    public function removePayment(\RocketSeller\TwoPickBundle\Entity\Pay $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
