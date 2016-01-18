<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\SmallIntType;

/**
 * Invitation
 *
 * @ORM\Table(name="invitation")
 * @ORM\Entity
 */
class Invitation
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
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="invitations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=256)
     */
    private $email;

    /**
     * El estado de la invitacion, si el usuario la utilizó o no
     * 0 / 1 0 - Sin utilizar / 1 - Utilizada
     * @var SmallIntType
     * @ORM\Column(name="status", type="smallint")
     */
    private $status = 0;

    /**
     * When sending invitation be sure to set this value to `true`
     *
     * It can prevent invitations from being sent twice
     *
     * @var boolean
     *
     * @ORM\Column(name="sent", type="boolean")
     */
    private $sent = false;

    public function __construct()
    {
        // generate identifier only once, here a 6 characters length code
        $this->code = substr(md5(uniqid(rand(), true)), 0, 6);
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

    public function isSent() {
        return $this->getSent();
    }

    public function send()
    {
        return $this->setSent(true);
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Invitation
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
     * Set status
     *
     * @param integer $status
     *
     * @return Invitation
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
     * Set sent
     *
     * @param boolean $sent
     *
     * @return Invitation
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return boolean
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set userId
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userId
     *
     * @return Invitation
     */
    public function setUserId(\RocketSeller\TwoPickBundle\Entity\User $userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
