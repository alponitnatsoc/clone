<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="RocketSeller\TwoPickBundle\Entity\CurrencyRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="currency")
 * @Gedmo\TranslationEntity(class="RocketSeller\TwoPickBundle\Entity\CurrencyTranslation") //Si no se pone, se toma de la tabla genérica
 */
class Currency
{

/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=200, nullable=TRUE, unique=TRUE)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length=3, nullable=TRUE, unique=TRUE)
     */
    protected $code;

    /**
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    protected $created_datetime;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    
    /** @ORM\PrePersist */
    function onPrePersist()
    {
        //using Doctrine DateTime here
        $this->created_datetime = new \DateTime('now');
    }
    
    /**
     * @ORM\OneToMany(
     *   targetEntity="CurrencyTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(CurrencyTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }
    
    public function __toString()
    {
        return $this->name; 
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Currency
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
     * Set code
     *
     * @param string $code
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set created_datetime
     *
     * @param \DateTime $createdDatetime
     * @return Currency
     */
    public function setCreatedDatetime($createdDatetime)
    {
        $this->created_datetime = $createdDatetime;

        return $this;
    }

    /**
     * Get created_datetime
     *
     * @return \DateTime 
     */
    public function getCreatedDatetime()
    {
        return $this->created_datetime;
    }

    /**
     * Remove translations
     *
     * @param \RocketSeller\TwoPickBundle\Entity\CurrencyTranslation $translations
     */
    public function removeTranslation(\RocketSeller\TwoPickBundle\Entity\CurrencyTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }
}
