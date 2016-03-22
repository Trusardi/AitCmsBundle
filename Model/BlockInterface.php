<?php

namespace Ait\CmsBundle\Model;

interface BlockInterface
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
     * @return int
     */
    public function getPosition();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param string $template
     */
    public function setTemplate($template);
}
