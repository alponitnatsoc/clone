<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Benefits
 *
 * @ORM\Table(name="benefits")
 * @ORM\Entity
 */
class Benefits
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_benefits", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBenefits;
    
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;


    /**
     * Get idBenefits
     *
     * @return integer
     */
    public function getIdBenefits()
    {
        return $this->idBenefits;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Benefits
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
