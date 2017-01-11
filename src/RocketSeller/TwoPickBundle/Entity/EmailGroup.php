<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailGroup
 *
 * @ORM\Table(name="email_group")
 * @ORM\Entity
 */
class EmailGroup
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_email_group", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmailGroup;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\EmailInfo",mappedBy="emailGroup")
     */
    private $emailsInfo;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->emailsInfo = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idEmailGroup
     *
     * @return integer
     */
    public function getIdEmailGroup()
    {
        return $this->idEmailGroup;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EmailGroup
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
     * Add emailsInfo
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmailInfo $emailsInfo
     *
     * @return EmailGroup
     */
    public function addEmailsInfo(\RocketSeller\TwoPickBundle\Entity\EmailInfo $emailsInfo)
    {
        $this->emailsInfo[] = $emailsInfo;

        return $this;
    }

    /**
     * Remove emailsInfo
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmailInfo $emailsInfo
     */
    public function removeEmailsInfo(\RocketSeller\TwoPickBundle\Entity\EmailInfo $emailsInfo)
    {
        $this->emailsInfo->removeElement($emailsInfo);
    }

    /**
     * Get emailsInfo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmailsInfo()
    {
        return $this->emailsInfo;
    }
}
