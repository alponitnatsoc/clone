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
     * Get idBenefits
     *
     * @return integer
     */
    public function getIdBenefits()
    {
        return $this->idBenefits;
    }
}
