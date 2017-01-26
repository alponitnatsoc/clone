<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Regex(
     *  pattern="/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{7,})\S$/",
     *  message="Password must be seven or more characters long and contain at least one digit, one upper- and one lowercase character."
     * )
     */
    protected $plainPassword;

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

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="userUser", cascade={"persist"})
     */
    private $actions;

    /**
     * @ORM\OneToMany(targetEntity="RealProcedure", mappedBy="userUser", cascade={"persist"})
     */
    private $realProcedures;

    public function __construct()
    {
        parent::__construct();
        $this->date_created = new \DateTime("now");
        //@todo GABRIEL agregar id unico del usuario al momento de registrar.
        $this->code = substr(md5(uniqid(rand(), true)), 0, 8);
        $this->purchaseOrders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->promotionCodes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->promoCodes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->realProcedures = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->devices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invitations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->referrals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->userHasCampaigns = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\OneToMany(targetEntity="UserHasCampaign", mappedBy="userUser", cascade={"persist"})
     */
    private $userHasCampaigns;

    /**
     * @ORM\OneToMany(targetEntity="Device", mappedBy="userUser", cascade={"persist"})
     */
    private $devices;

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
     *
     * Estados del usuario:
     *      0 - Inactivo / Suscripcion desactivada o inactiva
     *      1 - Mail confirmado
     *      2 - Registro completado
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $status = 1;


    /**
     * Columna utilizada para conocer el estado de revision en DataCredito
     *
     * Estados del usuario:
     *      0 - No Enviado
     *      1 - Enviado
     *      2 - Aprobado
     *      3 - No Aprobado
     *      4 - Exedio el numero de intentos
     *      5 - DataCredito Toteo
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $dataCreditStatus = 0;

    /**
     * Columna utilizada para guardar el codigo sms enviado para registrar empleado.
     *
     *
     * @var Int
     *
     * @ORM\Column(type="integer")
     */
    private $smsCode = 0;

    /**
     * Columna utilizada para saber cantidad de meses gratis
     *
     * Estados del usuario:
     *      0 - sin tiempo gratis
     *      1 - 1 mes gratis
     *      n - n meses gratis
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $isFree = 1;

    /**
     * Columna utilizada para conocer el estado del empleado
     * 0 No ha iiciado labores
     * 1 ya inicio labores
     * -1 para preguntarle antiguedad al segundo empleado
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $legalFlag = -2;

    /**
     * Columna utilizada para saber si el usuario requiere registro express
     * 0 false
     * 1 true
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $express = 0;

    /**
     * Columna utilizada para conocer el estado de la suscripcion del usuario
     * 0 Actualmente Sin pagar Symplifica
     * 1 Pagando Symplifica
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint")
     */
    private $paymentState = 0;

    /**
     *
     * @var SmallIntType
     *
     * @ORM\Column(type="smallint",nullable=true)
     */
    private $dayToPay;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $date_created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPayDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $isFreeTo;

    /**
     * @var string
     * @ORM\Column(type="string", length=200)
     */
    protected $code;

    /**
     * @ORM\OneToMany(targetEntity="Invitation", mappedBy="userId", cascade={"persist"})
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity="Referred", mappedBy="userId", cascade={"persist"})
     */
    private $referrals;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCode", mappedBy="userUser", cascade={"persist"})
     */
    private $promotionCodes;
    /**
     * @ORM\ManyToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\PromotionCode", mappedBy="users")
     * @Exclude
     */
    private $promoCodes;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $promoCodeClaimedByReferidor;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $money = 0;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $sHash;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Log", mappedBy="userUser",cascade={"persist"})
     */
    private $logs;

    /**
     * @var UserHasConfig $userHasConfig
     * @ORM\OneToMany(targetEntity="UserHasConfig", mappedBy="userUser", cascade={"persist"})
     */
    private $UserHasConfigs;

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
     * Set status
     *
     * @param integer $status
     *
     * @return User
     * Estados del usuario:
     *      0 - Inactivo / Suscripcion desactivada o inactiva
     *      1 - Mail confirmado
     *      2 - Registro completado
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

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     *
     * @return User
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return User
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add invitation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Invitation $invitation
     *
     * @return User
     */
    public function addInvitation(\RocketSeller\TwoPickBundle\Entity\Invitation $invitation)
    {
        $this->invitations[] = $invitation;

        return $this;
    }

    /**
     * Remove invitation
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Invitation $invitation
     */
    public function removeInvitation(\RocketSeller\TwoPickBundle\Entity\Invitation $invitation)
    {
        $this->invitations->removeElement($invitation);
    }

    /**
     * Get invitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvitations()
    {
        return $this->invitations;
    }

    /**
     * Add referral
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Referred $referral
     *
     * @return User
     */
    public function addReferral(\RocketSeller\TwoPickBundle\Entity\Referred $referral)
    {
        $this->referrals[] = $referral;

        return $this;
    }

    /**
     * Remove referral
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Referred $referral
     */
    public function removeReferral(\RocketSeller\TwoPickBundle\Entity\Referred $referral)
    {
        $this->referrals->removeElement($referral);
    }

    /**
     * Get referrals
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferrals()
    {
        return $this->referrals;
    }

    /**
     * Set dayToPay
     *
     * @param integer $dayToPay
     *
     * @return User
     */
    public function setDayToPay($dayToPay)
    {
        $this->dayToPay = $dayToPay;

        return $this;
    }

    /**
     * Get dayToPay
     *
     * @return integer
     */
    public function getDayToPay()
    {
        return $this->dayToPay;
    }

    /**
     * Set paymentState
     *
     * @param integer $paymentState
     *
     * @return User
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;

        return $this;
    }

    /**
     * Get paymentState
     *
     * @return integer
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * Set express
     *
     * @param integer $express
     *
     * @return User
     */
    public function setExpress($express)
    {
        $this->express = $express;

        return $this;
    }

    /**
     * Get express
     *
     * @return integer
     */
    public function getExpress()
    {
        return $this->express;
    }

    /**
     * Set lastPayDate
     *
     * @param \DateTime $lastPayDate
     *
     * @return User
     */
    public function setLastPayDate($lastPayDate)
    {
        $this->lastPayDate = $lastPayDate;

        return $this;
    }

    /**
     * Get lastPayDate
     *
     * @return \DateTime
     */
    public function getLastPayDate()
    {
        return $this->lastPayDate;
    }

    /**
     * Set legalFlag
     *
     * @param integer $legalFlag
     *
     * @return User
     */
    public function setLegalFlag($legalFlag)
    {
        $this->legalFlag = $legalFlag;

        return $this;
    }

    /**
     * Get legalFlag
     *
     * @return integer
     */
    public function getLegalFlag()
    {
        return $this->legalFlag;
    }


    /**
     * Set isFree
     *
     * @param integer $isFree
     *
     * @return User
     */
    public function setIsFree($isFree)
    {
        $this->isFree = $isFree;

        return $this;
    }

    /**
     * Get isFree
     *
     * @return integer
     */
    public function getIsFree()
    {
        return $this->isFree;
    }

    /**
     * Set isFreeTo
     *
     * @param \DateTime $isFreeTo
     *
     * @return User
     */
    public function setIsFreeTo($isFreeTo)
    {
        $this->isFreeTo = $isFreeTo;

        return $this;
    }

    /**
     * Get isFreeTo
     *
     * @return \DateTime
     */
    public function getIsFreeTo()
    {
        return $this->isFreeTo;
    }

    /**
     * Set dataCreditStatus
     *
     * @param integer $dataCreditStatus
     *
     * @return User
     */
    public function setDataCreditStatus($dataCreditStatus)
    {
        $this->dataCreditStatus = $dataCreditStatus;

        return $this;
    }

    /**
     * Get dataCreditStatus
     *
     * @return integer
     */
    public function getDataCreditStatus()
    {
        return $this->dataCreditStatus;
    }

    /**
     * Set $smsCode
     *
     * @param integer $smsCode
     *
     * @return User
     */
    public function setSmsCode($smsCode)
    {
        $this->smsCode = $smsCode;

        return $this;
    }

    /**
     * Get $smsCode
     *
     * @return integer
     */
    public function getSmsCode()
    {
        return $this->smsCode;
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
        $purchaseOrder->setIdUser($this);
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
     * Add promotionCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode
     *
     * @return User
     */
    public function addPromotionCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode)
    {
        $this->promotionCodes[] = $promotionCode;

        return $this;
    }

    /**
     * Remove promotionCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode
     */
    public function removePromotionCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promotionCode)
    {
        $this->promotionCodes->removeElement($promotionCode);
    }

    /**
     * Get promotionCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPromotionCodes()
    {
        return $this->promotionCodes;
    }

    /**
     * Add device
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Device $device
     *
     * @return User
     */
    public function addDevice(\RocketSeller\TwoPickBundle\Entity\Device $device)
    {
        $this->devices[] = $device;

        return $this;
    }

    /**
     * Remove device
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Device $device
     */
    public function removeDevice(\RocketSeller\TwoPickBundle\Entity\Device $device)
    {
        $this->devices->removeElement($device);
    }

    /**
     * Get devices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Add log
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Log $log
     *
     * @return User
     */
    public function addLog(\RocketSeller\TwoPickBundle\Entity\Log $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Log $log
     */
    public function removeLog(\RocketSeller\TwoPickBundle\Entity\Log $log)
    {
        $this->logs->removeElement($log);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param $procedureType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProceduresNotMatching($procedureType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->neq('procedureTypeProcedureType',$procedureType));
        return $this->realProcedures->matching($criteria);
    }

    /**
     * @param $procedureType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProceduresByType($procedureType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('procedureTypeProcedureType',$procedureType));
        return $this->realProcedures->matching($criteria);
    }

    /**
     * @param $actionType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsNotMatching($actionType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->neq('actionTypeActionType',$actionType));
        return $this->actions->matching($criteria);
    }

    /**
     * @param $actionType
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActionsByType($actionType)
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('actionTypeActionType',$actionType));
        return $this->actions->matching($criteria);
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
        $action->setUserUser($this);
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Remove action
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
     */
    public function removeAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
    {
        $this->actions->removeElement($action);
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAction()
    {
        return $this->actions;
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
        $realProcedure->setUserUser($this);
        $this->realProcedures[] = $realProcedure;

        return $this;
    }

    /**
     * Remove realProcedure
     *
     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure
     */
    public function removeRealProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $realProcedure)
    {
        $this->realProcedures->removeElement($realProcedure);
    }

    /**
     * Get realProcedures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRealProcedure()
    {
        return $this->realProcedures;
    }

    /**
     * Get actions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Get realProcedures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRealProcedures()
    {
        return $this->realProcedures;
    }

    /**
     * Add promoCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promoCode
     *
     * @return User
     */
    public function addPromoCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promoCode)
    {
        $this->promoCodes[] = $promoCode;

        return $this;
    }

    /**
     * Remove promoCode
     *
     * @param \RocketSeller\TwoPickBundle\Entity\PromotionCode $promoCode
     */
    public function removePromoCode(\RocketSeller\TwoPickBundle\Entity\PromotionCode $promoCode)
    {
        $this->promoCodes->removeElement($promoCode);
    }

    /**
     * Get promoCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPromoCodes()
    {
        return $this->promoCodes;
    }

    /**
     * Add userHasCampaign
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign
     *
     * @return User
     */
    public function addUserHasCampaign(\RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign)
    {
        $this->userHasCampaigns[] = $userHasCampaign;

        return $this;
    }

    /**
     * Remove userHasCampaign
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign
     */
    public function removeUserHasCampaign(\RocketSeller\TwoPickBundle\Entity\UserHasCampaign $userHasCampaign)
    {
        $this->userHasCampaigns->removeElement($userHasCampaign);
    }

    /**
     * Get userHasCampaigns
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserHasCampaigns()
    {
        return $this->userHasCampaigns;
    }

    /**
     * Set promoCodeClaimedByReferidor
     *
     * @param boolean $promoCodeClaimedByReferidor
     *
     * @return User
     */
    public function setPromoCodeClaimedByReferidor($promoCodeClaimedByReferidor)
    {
        $this->promoCodeClaimedByReferidor = $promoCodeClaimedByReferidor;

        return $this;
    }

    /**
     * Get promoCodeClaimedByReferidor
     *
     * @return boolean
     */
    public function getPromoCodeClaimedByReferidor()
    {
        return $this->promoCodeClaimedByReferidor;
    }

    /**
     * Set money
     *
     * @param float $money
     *
     * @return User
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money
     *
     * @return float
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * Set sHash
     *
     * @param string $sHash
     *
     * @return User
     */
    public function setSHash($sHash)
    {
        $this->sHash = $sHash;

        return $this;
    }

    /**
     * Get sHash
     *
     * @return string
     */
    public function getSHash()
    {
        return $this->sHash;
    }

    /**
     * Add userHasConfig
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasConfig $userHasConfig
     *
     * @return User
     */
    public function addUserHasConfig(\RocketSeller\TwoPickBundle\Entity\UserHasConfig $userHasConfig)
    {
        $this->UserHasConfigs[] = $userHasConfig;

        return $this;
    }

    /**
     * Remove userHasConfig
     *
     * @param \RocketSeller\TwoPickBundle\Entity\UserHasConfig $userHasConfig
     */
    public function removeUserHasConfig(\RocketSeller\TwoPickBundle\Entity\UserHasConfig $userHasConfig)
    {
        $this->UserHasConfigs->removeElement($userHasConfig);
    }

    /**
     * Get userHasConfigs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserHasConfigs()
    {
        return $this->UserHasConfigs;
    }
}
