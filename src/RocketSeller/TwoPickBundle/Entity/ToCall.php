<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ToCall
 *
 * @ORM\Table(name="to_call")
 * @ORM\Entity
 */
class ToCall
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_to_call", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idToCall;

    /**
     * @ORM\Column(type="string", length=250, nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"}, nullable=false)
     */
    private $timeCreated;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private $parameters;

    /**
     * 0 created
     * 1 approved
     * 2 rejected
     * 3 failed to execute
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status = 0;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $reasonToAuthorize;

    public function __construct(){
        $this->timeCreated=new \DateTime();
    }


    /**
     * Get idToCall
     *
     * @return integer
     */
    public function getIdToCall()
    {
        return $this->idToCall;
    }

    /**
     * Set service
     *
     * @param string $service
     *
     * @return ToCall
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set timeCreated
     *
     * @param \DateTime $timeCreated
     *
     * @return ToCall
     */
    public function setTimeCreated($timeCreated)
    {
        $this->timeCreated = $timeCreated;

        return $this;
    }

    /**
     * Get timeCreated
     *
     * @return \DateTime
     */
    public function getTimeCreated()
    {
        return $this->timeCreated;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     *
     * @return ToCall
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return ToCall
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
     * Set reasonToAuthorize
     *
     * @param string $reasonToAuthorize
     *
     * @return ToCall
     */
    public function setReasonToAuthorize($reasonToAuthorize)
    {
        $this->reasonToAuthorize = $reasonToAuthorize;

        return $this;
    }

    /**
     * Get reasonToAuthorize
     *
     * @return string
     */
    public function getReasonToAuthorize()
    {
        return $this->reasonToAuthorize;
    }
}
