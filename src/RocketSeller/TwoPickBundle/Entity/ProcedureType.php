<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProcedureType
 *
 * @ORM\Table(name="procedure_type")
 * @ORM\Entity
 */
class ProcedureType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_procedure_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idProcedureType;



    /**
     * Get idProcedureType
     *
     * @return integer
     */
    public function getIdProcedureType()
    {
        return $this->idProcedureType;
    }
}
