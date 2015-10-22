<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccountType
 *
 * @ORM\Table(name="account_type")
 * @ORM\Entity
 */
class AccountType
{
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id_account_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idAccountType;
    
    /**
     * Get idAccountType
     *
     * @return integer
     */
    public function getIdAccountType()
    {
        return $this->idAccountType;
    }
}
