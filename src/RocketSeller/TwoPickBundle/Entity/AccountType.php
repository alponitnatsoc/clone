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
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * Get idAccountType
     *
     * @return integer
     */
    public function getIdAccountType()
    {
        return $this->idAccountType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return AccountType
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
