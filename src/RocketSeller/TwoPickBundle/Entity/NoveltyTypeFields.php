<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoveltyTypeFields
 *
 * @ORM\Table(name="novelty_type_fields")
 * @ORM\Entity(repositoryClass="NoveltyTypeFieldsRepository")
 */
class NoveltyTypeFields
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_novelty_type", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idNoveltyTypeFields;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var \RocketSeller\TwoPickBundle\Entity\NoveltyType
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\NoveltyType", inversedBy="requiredFields")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="novelty_type_id_novelty_type", referencedColumnName="id_novelty_type")
     * })
     */
    private $noveltyTypeNoveltyType;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $columnName;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $dataType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $displayable;

    /**
     * Cada columnName tiene un formato independiente para generar la constrain
     *
     * para date_start o date_end
     *
     * #Meses = nM
	   * Fecha exacta = da
	   * #Periodos = nP
	   * #Dias = nD
	   * #Cortes = nC
	   * #Años = nY
	   * Next Cut = cU
	   * Start Period = sP
     * End Period = eP
     *
     * Este tipo de constrain debe seguir la siguiente estuctura
     *
     * Min/Men_nM_UNITS
     * Max/Mas_dA_YYYY-MM-DD
     *
     * Siendo Min o Max (Valor minimo o maximo)
     * Men o Mas reemplazarlo por - o + (Si el valor debe ser menor o mayor al valor de la restriccion
     * Ejemplo Min/-_nP_0 Max/+_nP_1
     * Min debe ser 0 periodos previos (o el actual en otras palabras) y maximo 1 periodo siguiente al actual
     *
     * para units
     *
     * #Dias = nD
     * #Cuotas = nC
     * #Horas = nH
     *
     * Min/nC_UNITS
     *
     * Siendo Min o Max (Valor minimo o maximo)
     * Ejemplo Min/nH_1 Max/nH_40
     * Min debe ser 1 hora y max 40 horas
     *
     * para amount
     *
     * Dinero =  mo
     * Porcentaje = pe
     *
     * Min/mo_NUMBER
     * Max/pe_NUMBER_Sal
     *
     * Siendo Min o Max (Valor minimo o maximo)
     * Sal indica que es en relacion al salario
     * Ejemplo Max/pe_50_Sal
     * Maximo debe ser el 50% del salario
     * Ejemplo 2 Min/mo_1
     * Minimo debe ser 1 (el valor del dinero)
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $noveltyDataConstrain;
    
    /**
     * Get idNoveltyTypeFields
     *
     * @return integer
     */
    public function getIdNoveltyTypeFields()
    {
        return $this->idNoveltyTypeFields;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NoveltyTypeFields
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

    /**
     * Set columnName
     *
     * @param string $columnName
     *
     * @return NoveltyTypeFields
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get columnName
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Set dataType
     *
     * @param string $dataType
     *
     * @return NoveltyTypeFields
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set noveltyTypeNoveltyType
     *
     * @param \RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType
     *
     * @return NoveltyTypeFields
     */
    public function setNoveltyTypeNoveltyType(\RocketSeller\TwoPickBundle\Entity\NoveltyType $noveltyTypeNoveltyType = null)
    {
        $this->noveltyTypeNoveltyType = $noveltyTypeNoveltyType;

        return $this;
    }

    /**
     * Get noveltyTypeNoveltyType
     *
     * @return \RocketSeller\TwoPickBundle\Entity\NoveltyType
     */
    public function getNoveltyTypeNoveltyType()
    {
        return $this->noveltyTypeNoveltyType;
    }

    /**
     * Set displayable
     *
     * @param boolean $displayable
     *
     * @return NoveltyTypeFields
     */
    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;

        return $this;
    }

    /**
     * Get displayable
     *
     * @return boolean
     */
    public function getDisplayable()
    {
        return $this->displayable;
    }

    /**
     * Set noveltyDataConstrain
     *
     * @param string $noveltyDataConstrain
     *
     * @return NoveltyTypeFields
     */
    public function setNoveltyDataConstrain($noveltyDataConstrain)
    {
        $this->noveltyDataConstrain = $noveltyDataConstrain;

        return $this;
    }

    /**
     * Get noveltyDataConstrain
     *
     * @return string
     */
    public function getNoveltyDataConstrain()
    {
        return $this->noveltyDataConstrain;
    }
}
