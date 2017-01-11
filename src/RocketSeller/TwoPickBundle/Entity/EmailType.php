<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailType
 *
 * @ORM\Table(name="email_type")
 * @ORM\Entity
 */
class EmailType
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_email_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmailType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string",length=200)
     */
    private $emailType;

    /**
     * @ORM\Column(type="string",length=10,unique=true)
     */
    private $code;

    /**
     * Get idEmailType
     *
     * @return integer
     */
    public function getIdEmailType()
    {
        return $this->idEmailType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EmailType
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
     * Set emailType
     *
     * @param string $emailType
     *
     * @return EmailType
     */
    public function setEmailType($emailType)
    {
        $this->emailType = $emailType;

        return $this;
    }

    /**
     * Get emailType
     *
     * @return string
     */
    public function getEmailType()
    {
        return $this->emailType;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return EmailType
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
}
