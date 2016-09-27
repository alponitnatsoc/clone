<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * ActionType
 *
 * @ORM\Table(name="action_type",
 *      uniqueConstraints={@UniqueConstraint(name="procedure_code_unique", columns={"code"})}
 * )
 * @ORM\Entity
 */
class ActionType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_action_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idActionType;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var string $code
     *
     * @ORM\Column(type="string", length=4)
     */
    private $code;

    /**
     * Set idActionType
     *
     * @param integer $id
     *
     * @return ActionType
     */
    public function setIdActionType($id)
    {
        $this->idActionType =$id;

        return $this;
    }

    /**
     * Get idActionType
     *
     * @return integer
     */
    public function getIdActionType()
    {
        return $this->idActionType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ActionType
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

    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return ActionType
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

    /**
     * ActionType constructor.
     * @param string $name
     * @param string $code
     */
    public function __construct($name=null, $code=null)
    {
        $this->name = $name;
        $this->code = $code;
    }
}
