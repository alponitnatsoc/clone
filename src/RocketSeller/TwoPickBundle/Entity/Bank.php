<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bank
 *
 * @ORM\Table(name="bank")
 * @ORM\Entity
 */
class Bank
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_bank", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idBank;



    /**
     * Get idBank
     *
     * @return integer
     */
    public function getIdBank()
    {
        return $this->idBank;
    }
}
