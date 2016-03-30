<?php

namespace Ait\CmsBundle\Model;

abstract class ParentBlock implements BlockInterface
{
    protected $name;

    protected $position;

    protected $enabled;

    protected $template;

    public function __construct()
    {
        $this->enabled = true;
        $this->position = 0;
        $this->name = sprintf('%s %s', (new \ReflectionClass($this))->getShortName(), substr(md5(uniqid()), 0, 8));
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
