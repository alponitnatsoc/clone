<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActionType
 *
 * @ORM\Table(name="action_type")
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
}
