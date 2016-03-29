<?php

namespace Ait\CmsBundle\Model;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\MediaInterface;

abstract class Page implements PageInterface
{
    protected $name;

    protected $slug;

    protected $routeAction;

    protected $parent;

    protected $excerpt;

    protected $content;

    protected $featuredImage;

    protected $enabled;

    protected $blocks;

    protected $extraFieldDefinitions;

    protected $extraFields;

    protected $seoTitle;

    protected $seoDescription;

    protected $createdAt;

    protected $updatedAt;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->enabled = true;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->setSlug($name);
        $this->name = $name;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = (new Slugify)->slugify($slug);
    }

    public function getRouteAction()
    {
        return $this->routeAction;
    }

    public function setRouteAction($routeAction)
    {
        $this->routeAction = $routeAction;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(PageInterface $parent = null)
    {
        $this->parent = $parent;
    }

    public function getExcerpt()
    {
        return $this->excerpt;
    }

    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getFeaturedImage()
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(MediaInterface $featuredImage = null)
    {
        $this->featuredImage = $featuredImage;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function addBlock(BlockInterface $block)
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks[] = $block;
        }
    }

    public function removeBlock(BlockInterface $block)
    {
        $this->blocks->removeElement($block);
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getExtraFieldDefinitions()
    {
        return $this->extraFieldDefinitions;
    }

    public function setExtraFieldDefinitions($extraFieldDefinitions)
    {
        $this->extraFieldDefinitions = $extraFieldDefinitions;
    }

    public function getExtraFields()
    {
        return $this->extraFields;
    }

    public function setExtraFields(array $extraFields)
    {
        $this->extraFields = $extraFields;
    }

    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
