<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PayType
 *
 * @ORM\Table(name="pay_type")
 * @ORM\Entity
 */
class PayType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_pay_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idPayType;



    /**
     * Get idPayType
     *
     * @return integer
     */
    public function getIdPayType()
    {
        return $this->idPayType;
    }
}
