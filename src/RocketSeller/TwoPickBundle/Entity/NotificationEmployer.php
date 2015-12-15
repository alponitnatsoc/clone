<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\NotificationEmployerRepository")
 */
class NotificationEmployer
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
     * @var \RocketSeller\TwoPickBundle\Entity\Employer
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\Employer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employer_id_employer", referencedColumnName="id_employer")
     * })
     */
    private $employerEmployer;

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
     * @var integer
     *
     * @ORM\Column(name="related_link", type="string", length=100, nullable=TRUE)
     */
    private $relatedLink;
    /**
     * @var integer
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=TRUE)
     */
    private $description;

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
     * Set sawDate
     *
     * @param \DateTime $sawDate
     *
     * @return NotificationEmployer
     */
    public function setSawDate($sawDate)
    {
        $this->sawDate = $sawDate;

        return $this;
    }

    /**
     * Get sawDate
     *
     * @return \DateTime
     */
    public function getSawDate()
    {
        return $this->sawDate;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return NotificationEmployer
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
     * Set employerEmployer
     *
     * @param \RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer
     *
     * @return NotificationEmployer
     */
    public function setEmployerEmployer(\RocketSeller\TwoPickBundle\Entity\Employer $employerEmployer = null)
    {
        $this->employerEmployer = $employerEmployer;

        return $this;
    }

    /**
     * Get employerEmployer
     *
     * @return \RocketSeller\TwoPickBundle\Entity\Employer
     */
    public function getEmployerEmployer()
    {
        return $this->employerEmployer;
    }


    /**
     * Set relatedLink
     *
     * @param string $relatedLink
     *
     * @return NotificationEmployer
     */
    public function setRelatedLink($relatedLink)
    {
        $this->relatedLink = $relatedLink;

        return $this;
    }

    /**
     * Get relatedLink
     *
     * @return string
     */
    public function getRelatedLink()
    {
        return $this->relatedLink;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return NotificationEmployer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
