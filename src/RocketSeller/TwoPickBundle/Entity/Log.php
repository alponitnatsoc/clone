<?php

namespace RocketSeller\TwoPickBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;

/**
 * Log
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Log
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
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\User", inversedBy="logs", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id_user",referencedColumnName="id")
     */
    private $userUser;

    /**
     * @var string
     * @ORM\Column(name="table_name",type="string",length=100)
     */
    private $tableName;

    /**
     * @var string
     * @ORM\Column(name="column_name",type="string",length=80)
     */
    private $columnName;

    /**
     * @var integer
     * @ORM\Column(name="row_id",type="integer")
     */
    private $rowId;

    /**
     * @var string
     * @ORM\Column(name="previous_data",type="string",length=300)
     */
    private $previousData;

    /**
     * @var string
     * @ORM\Column(name="actual_data",type="string",length=300)
     */
    private $actualData;

    /**
     * @ORM\Column(name="created_at",type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=300, nullable=true)
     */
    private $message;

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
     * Set tableName
     *
     * @param string $tableName
     *
     * @return Log
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get tableName
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set columnName
     *
     * @param string $columnName
     *
     * @return Log
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get columnName
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set rowId
     *
     * @param integer $rowId
     *
     * @return Log
     */
    public function setRowId($rowId)
    {
        $this->rowId = $rowId;

        return $this;
    }

    /**
     * Get rowId
     *
     * @return integer
     */
    public function getRowId()
    {
        return $this->rowId;
    }

    /**
     * Set previousData
     *
     * @param string $previousData
     *
     * @return Log
     */
    public function setPreviousData($previousData)
    {
        $this->previousData = $previousData;

        return $this;
    }

    /**
     * Get previousData
     *
     * @return string
     */
    public function getPreviousData()
    {
        return $this->previousData;
    }

    /**
     * Set actualData
     *
     * @param string $actualData
     *
     * @return Log
     */
    public function setActualData($actualData)
    {
        $this->actualData = $actualData;

        return $this;
    }

    /**
     * Get actualData
     *
     * @return string
     */
    public function getActualData()
    {
        return $this->actualData;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Log
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Log
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set userUser
     *
     * @param \RocketSeller\TwoPickBundle\Entity\User $userUser
     *
     * @return Log
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
     * Constructor
     */
    public function __construct($user =null,$tableName = null,$columnName = null,$rowId = null,$previousData = null,$actualData = null,$message = null)
    {
        $this->userUser = $user;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->rowId = $rowId;
        $this->previousData = $previousData;
        $this->actualData = $actualData;
        $this->createdAt = new DateTime();
        $this->message = $message;
    }
}
