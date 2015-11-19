<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TimeCommitment
 *
 * @ORM\Table(name="time_commitment")
 * @ORM\Entity
 */
class TimeCommitment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_time_commitment", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTimeCommitment;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;



    /**
     * Get idTimeCommitment
     *
     * @return integer
     */
    public function getIdTimeCommitment()
    {
        return $this->idTimeCommitment;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TimeCommitment
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
}
