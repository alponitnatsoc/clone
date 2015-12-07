<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\SettingsRepository")
 */
class Settings
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
     * @ORM\OneToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     */
    private $userUser;

    /**
     * @ORM\Column(type="string", length=200, nullable=FALSE)
     */
    protected $setting_key;
    
    /**
     * @ORM\Column(type="string", length=200, nullable=FALSE)
     */
    protected $setting_value;

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
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return Settings
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

    /**
     * Set settingKey
     *
     * @param string $settingKey
     *
     * @return Settings
     */
    public function setSettingKey($settingKey)
    {
        $this->setting_key = $settingKey;

        return $this;
    }

    /**
     * Get settingKey
     *
     * @return string
     */
    public function getSettingKey()
    {
        return $this->setting_key;
    }

    /**
     * Set settingValue
     *
     * @param string $settingValue
     *
     * @return Settings
     */
    public function setSettingValue($settingValue)
    {
        $this->setting_value = $settingValue;

        return $this;
    }

    /**
     * Get settingValue
     *
     * @return string
     */
    public function getSettingValue()
    {
        return $this->setting_value;
    }

}
