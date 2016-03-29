<?php

namespace Ait\CmsBundle\Entity;

use Ait\CmsBundle\Model\Page;

abstract class BasePage extends Page
{
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
