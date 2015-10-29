<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractType
 *
 * @ORM\Table(name="contract_type")
 * @ORM\Entity
 */
class ContractType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_contract_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idContractType;
    /**
     * @ORM\Column(type="string", length=100, nullable=TRUE)
     */
    private $name;


    /**
     * Get idContractType
     *
     * @return integer
     */
    public function getIdContractType()
    {
        return $this->idContractType;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ContractType
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
