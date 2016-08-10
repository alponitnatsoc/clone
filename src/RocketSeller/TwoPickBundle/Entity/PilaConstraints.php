<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalculatorConstraints
 *
 * @ORM\Table(name="pila_constraints")
 * @ORM\Entity
 */
class PilaConstraints
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id_pila_constraints", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPilaConstraints;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $type;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $lastTwoDigitsFrom;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $lastTwoDigitsTo;

    /**
     * @ORM\Column(type="smallint", nullable=TRUE)
     */
    private $lastDay;



    /**
     * Get idPilaConstraints
     *
     * @return integer
     */
    public function getIdPilaConstraints()
    {
        return $this->idPilaConstraints;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return PilaConstraints
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set lastTwoDigitsFrom
     *
     * @param integer $lastTwoDigitsFrom
     *
     * @return PilaConstraints
     */
    public function setLastTwoDigitsFrom($lastTwoDigitsFrom)
    {
        $this->lastTwoDigitsFrom = $lastTwoDigitsFrom;

        return $this;
    }

    /**
     * Get lastTwoDigitsFrom
     *
     * @return integer
     */
    public function getLastTwoDigitsFrom()
    {
        return $this->lastTwoDigitsFrom;
    }

    /**
     * Set lastTwoDigitsTo
     *
     * @param integer $lastTwoDigitsTo
     *
     * @return PilaConstraints
     */
    public function setLastTwoDigitsTo($lastTwoDigitsTo)
    {
        $this->lastTwoDigitsTo = $lastTwoDigitsTo;

        return $this;
    }

    /**
     * Get lastTwoDigitsTo
     *
     * @return integer
     */
    public function getLastTwoDigitsTo()
    {
        return $this->lastTwoDigitsTo;
    }

    /**
     * Set lastDay
     *
     * @param integer $lastDay
     *
     * @return PilaConstraints
     */
    public function setLastDay($lastDay)
    {
        $this->lastDay = $lastDay;

        return $this;
    }

    /**
     * Get lastDay
     *
     * @return integer
     */
    public function getLastDay()
    {
        return $this->lastDay;
    }
}
