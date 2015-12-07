<?php

namespace RocketSeller\TwoPickBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpArticle
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class HelpArticle
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id_help_article", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idHelpArticle;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;
    /**
     * @var \RocketSeller\TwoPickBundle\Entity\HelpCategory
     *
     * @ORM\ManyToOne(targetEntity="RocketSeller\TwoPickBundle\Entity\HelpCategory", inversedBy="helpArticles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="help_category_id_help_category", referencedColumnName="id_help_category")
     * })
     */
    private $helpCategoryHelpCategory;
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;


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
     * Set description
     *
     * @param string $description
     *
     * @return HelpArticles
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
     * Set type
     *
     * @param string $type
     *
     * @return HelpArticles
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get idHelpArticle
     *
     * @return integer
     */
    public function getIdHelpArticle()
    {
        return $this->idHelpArticle;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return HelpArticle
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set helpCategoryHelpCategory
     *
     * @param \RocketSeller\TwoPickBundle\Entity\HelpCategory $helpCategoryHelpCategory
     *
     * @return HelpArticle
     */
    public function setHelpCategoryHelpCategory(\RocketSeller\TwoPickBundle\Entity\HelpCategory $helpCategoryHelpCategory = null)
    {
        $this->helpCategoryHelpCategory = $helpCategoryHelpCategory;

        return $this;
    }

    /**
     * Get helpCategoryHelpCategory
     *
     * @return \RocketSeller\TwoPickBundle\Entity\HelpCategory
     */
    public function getHelpCategoryHelpCategory()
    {
        return $this->helpCategoryHelpCategory;
    }
    public function __toString()
    {
        return (string) $this->title;
    }

    /**
     * Set image
     *
     * @param string $image
     *
     * @return HelpArticle
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
}
