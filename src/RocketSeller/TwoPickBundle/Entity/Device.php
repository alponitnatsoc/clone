<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Device
 *
 * @ORM\Table(name="device")
 * @ORM\Entity
 */
class Device
{
    /**
     * @var integer
     * @ORM\Column(name="id_device", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idDevice;

    /**
     * @var string
     * @ORM\Column(name="token", type="text")
     */
    private $token;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="devices")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     * @Exclude
     */
    private $userUser;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_login_in_device", type="datetime")
     */
    private $lastLoginInDevice;
    

    /**
     * Get idDevice
     *
     * @return integer
     */
    public function getIdDevice()
    {
        return $this->idDevice;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Device
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set lastLoginInDevice
     *
     * @param \DateTime $lastLoginInDevice
     *
     * @return Device
     */
    public function setLastLoginInDevice($lastLoginInDevice)
    {
        $this->lastLoginInDevice = $lastLoginInDevice;

        return $this;
    }

    /**
     * Get lastLoginInDevice
     *
     * @return \DateTime
     */
    public function getLastLoginInDevice()
    {
        return $this->lastLoginInDevice;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return Device
     */
    public function setUserUser(\RocketSeller\TwoPickBundle\Entity\User $userUser = null)
    {
        $this->userUser = $userUser;

        return $this;
    }

    /**
     * Get userUser
     *
     * @return \RocketSeller\TwoPickBundle\Entity\User
     */
    public function getUserUser()
    {
        return $this->userUser;
    }
}
