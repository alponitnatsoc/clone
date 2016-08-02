<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class HelpCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_help_category", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idHelpCategory;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;
    
    /**
     *
     * @ORM\OneToMany(targetEntity="RocketSeller\TwoPickBundle\Entity\HelpArticle", mappedBy="helpCategoryHelpCategory")
     */
    private $helpArticles;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

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
     *
     * @return HelpCategories
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
     * Set description
     *
     * @param string $description
     *
     * @return HelpCategories
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return HelpCategories
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->helpArticles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get idHelpCategory
     *
     * @return integer
     */
    public function getIdHelpCategory()
    {
        return $this->idHelpCategory;
    }

    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * Add helpArticle
     *
     * @param \RocketSeller\TwoPickBundle\Entity\HelpArticle $helpArticle
     *
     * @return HelpCategory
     */
    public function addHelpArticle(\RocketSeller\TwoPickBundle\Entity\HelpArticle $helpArticle)
    {
        $this->helpArticles[] = $helpArticle;

        return $this;
    }

    /**
     * Remove helpArticle
     *
     * @param \RocketSeller\TwoPickBundle\Entity\HelpArticle $helpArticle
     */
    public function removeHelpArticle(\RocketSeller\TwoPickBundle\Entity\HelpArticle $helpArticle)
    {
        $this->helpArticles->removeElement($helpArticle);
    }

    /**
     * Get helpArticles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHelpArticles()
    {
        return $this->helpArticles;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return HelpCategory
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
