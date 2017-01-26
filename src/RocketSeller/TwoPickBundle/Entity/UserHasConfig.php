<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;


/**
 * UserHasConfig
 *
 * @ORM\Table(name="user_has_config",
 *     indexes={@ORM\Index(name="fk_user_has_config_user1",columns={"user_id_user"}),
 *              @ORM\Index(name="fk_user_has_config_configuration1",columns={"configuration_id_configuration"})}
 * )
 * @ORM\Entity
 */
class UserHasConfig
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_user_has_config", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idUserHasConfig;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\User
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="UserHasConfigs", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id_user", referencedColumnName="id")
     * })
     * @Exclude
     */
    private $userUser;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\Configuration
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Configuration", inversedBy="configHasUsers", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="configuration_id_configuration", referencedColumnName="id_configuration")
     * })
     */
    private $configurationConfiguration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $acceptedAt;



    /**
     * Get idUserHasConfig
     *
     * @return integer
     */
    public function getIdUserHasConfig()
    {
        return $this->idUserHasConfig;
    }

    /**
     * Set acceptedAt
     *
     * @param \DateTime $acceptedAt
     *
     * @return UserHasConfig
     */
    public function setAcceptedAt($acceptedAt)
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    /**
     * Get acceptedAt
     *
     * @return \DateTime
     */
    public function getAcceptedAt()
    {
        return $this->acceptedAt;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return UserHasConfig
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
     * Set configurationConfiguration
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Configuration $configurationConfiguration
     *
     * @return UserHasConfig
     */
    public function setConfigurationConfiguration(\RocketSeller\TwoPickBundle\Entity\Configuration $configurationConfiguration = null)
    {
        $this->configurationConfiguration = $configurationConfiguration;

        return $this;
    }

    /**
     * Get configurationConfiguration
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Configuration
     */
    public function getConfigurationConfiguration()
    {
        return $this->configurationConfiguration;
    }
}
