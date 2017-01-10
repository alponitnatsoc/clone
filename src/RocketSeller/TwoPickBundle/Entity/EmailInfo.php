<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailInfo
 *
 * @ORM\Table(name="email_info")
 * @ORM\Entity
 */
class EmailInfo
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_email_info", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idEmailInfo;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=FALSE)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=FALSE)
     */
    private $documentType="CC";

    /**
     * @ORM\Column(type="string", length=20, nullable=FALSE)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\EmailGroup", inversedBy="emailsInfo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="email_group_id", referencedColumnName="id_email_group",nullable=FALSE)
     * })
     */
    private $emailGroup;


    /**
     * Set idEmailInfo
     *
     * @param integer $idEmailInfo
     *
     * @return EmailInfo
     */
    public function setIdEmailInfo($idEmailInfo)
    {
        $this->idEmailInfo = $idEmailInfo;

        return $this;
    }

    /**
     * Get idEmailInfo
     *
     * @return integer
     */
    public function getIdEmailInfo()
    {
        return $this->idEmailInfo;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EmailInfo
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
     * Set email
     *
     * @param string $email
     *
     * @return EmailInfo
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
     * Set documentType
     *
     * @param string $documentType
     *
     * @return EmailInfo
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set document
     *
     * @param string $document
     *
     * @return EmailInfo
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set emailGroup
     *
     * @param \RocketSeller\TwoPickBundle\Entity\EmailGroup $emailGroup
     *
     * @return EmailInfo
     */
    public function setEmailGroup(\RocketSeller\TwoPickBundle\Entity\EmailGroup $emailGroup)
    {
        $this->emailGroup = $emailGroup;

        return $this;
    }

    /**
     * Get emailGroup
     *
     * @return \RocketSeller\TwoPickBundle\Entity\EmailGroup
     */
    public function getEmailGroup()
    {
        return $this->emailGroup;
    }
}
