<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MongoDB\BSON\Binary;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TempFile
 * @package RocketSeller\TwoPickBundle\Entity
 *
 * @ORM\Table(name="temp_file")
 * @ORM\Entity
 */
class TempFile
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_temp_file", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idTempFile;

    /**
     * @ORM\Column(type="binary")
     */
    private $image;
    

    /**
     * Get idTempFile
     *
     * @return integer
     */
    public function getIdTempFile()
    {
        return $this->idTempFile;
    }

    /**
     * Set image
     *
     * @param binary $image
     *
     * @return TempFile
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return binary
     */
    public function getImage()
    {
        return $this->image;
    }
}
