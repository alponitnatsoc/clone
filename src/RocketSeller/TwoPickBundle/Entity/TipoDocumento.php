<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TipoDocumento
 *
 * @ORM\Table(name="tipo_documento")
 * @ORM\Entity
 */
class TipoDocumento
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_tipo_documento", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTipoDocumento;



    /**
     * Get idTipoDocumento
     *
     * @return integer
     */
    public function getIdTipoDocumento()
    {
        return $this->idTipoDocumento;
    }
}
