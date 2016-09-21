<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * StatusType
 *
 * @ORM\Table(name="status_types",
 *      uniqueConstraints={@UniqueConstraint(name="status_code_unique", columns={"code"})}
 * )
 * @ORM\Entity
 */
class StatusTypes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_status_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idStatusType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var string $code
     *
     * @ORM\Column(type="string", length=6)
     */
    private $code;

//    /**
//     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\Action", mappedBy="actionStatus", cascade={"persist"})
//     */
//    private $actions;
//
//    /**
//     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\RealProcedure",mappedBy="procedureStatus", cascade={"persist"})
//     */
//    private $procedures;

    /**
     * Set idStatusType
     *
     * @param integer $id
     *
     * @return StatusTypes
     */
    public function setIdStatusType($id)
    {
        $this->idStatusType =$id;

        return $this;
    }

    /**
     * StatusType toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * ActionType constructor.
     * @param string $name
     * @param string $code
     */
    public function __construct($name=null, $code=null)
    {
        $this->name = $name;
        $this->code = $code;
//        $this->procedures = new \Doctrine\Common\Collections\ArrayCollection();
//        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idStatusType
     *
     * @return integer
     */
    public function getIdStatusType()
    {
        return $this->idStatusType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return StatusTypes
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
     * Set code
     *
     * @param string $code
     *
     * @return StatusTypes
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

//    /**
//     * Add procedure
//     *
//     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $procedure
//     *
//     * @return StatusTypes
//     */
//    public function addProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $procedure)
//    {
//        $this->procedures[] = $procedure;
//
//        return $this;
//    }
//
//    /**
//     * Remove procedure
//     *
//     * @param \RocketSeller\TwoPickBundle\Entity\RealProcedure $procedure
//     */
//    public function removeProcedure(\RocketSeller\TwoPickBundle\Entity\RealProcedure $procedure)
//    {
//        $this->procedures->removeElement($procedure);
//    }
//
//    /**
//     * Get procedures
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getProcedures()
//    {
//        return $this->procedures;
//    }
//
//
//
//    /**
//     * Add action
//     *
//     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
//     *
//     * @return StatusTypes
//     */
//    public function addAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
//    {
//        $this->actions[] = $action;
//
//        return $this;
//    }
//
//    /**
//     * Remove action
//     *
//     * @param \RocketSeller\TwoPickBundle\Entity\Action $action
//     */
//    public function removeAction(\RocketSeller\TwoPickBundle\Entity\Action $action)
//    {
//        $this->actions->removeElement($action);
//    }
//
//    /**
//     * Get actions
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getActions()
//    {
//        return $this->actions;
//    }
}
