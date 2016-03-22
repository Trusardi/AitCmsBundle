<?php

namespace Ait\CmsBundle\Model;

use Sonata\MediaBundle\Model\MediaInterface;

interface PageInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * @return string
     */
    public function getRouteAction();

    /**
     * @param string $routeAction
     */
    public function setRouteAction($routeAction);

    /**
     * @return PageInterface
     */
    public function getParent();

    /**
     * @param PageInterface|null $parent
     */
    public function setParent(PageInterface $parent = null);

    /**
     * @return string
     */
    public function getExcerpt();

    /**
     * @param string $excerpt
     */
    public function setExcerpt($excerpt);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     */
    public function setContent($content);

    /**
     * @return MediaInterface
     */
    public function getFeaturedImage();

    /**
     * @param MediaInterface|null $featuredImage
     */
    public function setFeaturedImage(MediaInterface $featuredImage = null);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @param BlockInterface $block
     */
    public function addBlock(BlockInterface $block);

    /**
     * @param BlockInterface $block
     */
    public function removeBlock(BlockInterface $block);

    /**
     * @return BlockInterface[]
     */
    public function getBlocks();

    /**
     * @return string
     */
    public function getExtraFieldDefinitions();

    /**
     * @param string $extraFieldDefinitions
     */
    public function setExtraFieldDefinitions($extraFieldDefinitions);

    /**
     * @return array
     */
    public function getExtraFields();

    /**
     * @param array $extraFields
     */
    public function setExtraFields(array $extraFields);

    /**
     * @return string
     */
    public function getSeoTitle();

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle);

    /**
     * @return string
     */
    public function getSeoDescription();

    /**
     * @param string $seoDescription
     */
    public function setSeoDescription($seoDescription);
}
