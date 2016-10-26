<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="high_tech_log")
 * @ORM\Entity
 */
class HighTechLog
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_high_tech_log", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idHighTechLog;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $serviceCalled;

    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"}, length=255, nullable=false)
     */
    private $timeWhenCalled;
	
		/**
		 * @ORM\Column(type="array", nullable=false)
		 */
		private $parameters;
	
		/**
		 * @ORM\Column(type="integer", nullable=true)
		 */
		private $resultCode;
	
		/**
		 * @ORM\Column(type="integer", nullable=true)
		 */
		private $rawResultCode;
	

    /**
     * Get idHighTechLog
     *
     * @return integer
     */
    public function getIdHighTechLog()
    {
        return $this->idHighTechLog;
    }

    /**
     * Set serviceCalled
     *
     * @param string $serviceCalled
     *
     * @return HighTechLog
     */
    public function setServiceCalled($serviceCalled)
    {
        $this->serviceCalled = $serviceCalled;

        return $this;
    }

    /**
     * Get serviceCalled
     *
     * @return string
     */
    public function getServiceCalled()
    {
        return $this->serviceCalled;
    }

    /**
     * Set timeWhenCalled
     *
     * @param \DateTime $timeWhenCalled
     *
     * @return HighTechLog
     */
    public function setTimeWhenCalled($timeWhenCalled)
    {
        $this->timeWhenCalled = $timeWhenCalled;

        return $this;
    }

    /**
     * Get timeWhenCalled
     *
     * @return \DateTime
     */
    public function getTimeWhenCalled()
    {
        return $this->timeWhenCalled;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     *
     * @return HighTechLog
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
     * Set result
     *
     * @param integer $result
     *
     * @return HighTechLog
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set resultCode
     *
     * @param integer $resultCode
     *
     * @return HighTechLog
     */
    public function setResultCode($resultCode)
    {
        $this->resultCode = $resultCode;

        return $this;
    }

    /**
     * Get resultCode
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * Set rawResultCode
     *
     * @param integer $rawResultCode
     *
     * @return HighTechLog
     */
    public function setRawResultCode($rawResultCode)
    {
        $this->rawResultCode = $rawResultCode;

        return $this;
    }

    /**
     * Get rawResultCode
     *
     * @return integer
     */
    public function getRawResultCode()
    {
        return $this->rawResultCode;
    }
}
