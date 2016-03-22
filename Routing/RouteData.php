<?php

namespace Ait\CmsBundle\Routing;

class RouteData
{
    private $path;
    private $locale;
    private $controller;
    private $firstParentPageId = null;
    private $lastParentPageId = null;
    private $pageId;
    private $pageClass;
    private $resourceId = null;
    private $resourceClass = null;
    private $redirectPath;

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    public function getFirstParentPageId()
    {
        return $this->firstParentPageId;
    }

    public function setFirstParentPageId($firstParentPageId)
    {
        $this->firstParentPageId = $firstParentPageId;

        return $this;
    }

    public function getLastParentPageId()
    {
        return $this->lastParentPageId;
    }

    public function setLastParentPageId($lastParentPageId)
    {
        $this->lastParentPageId = $lastParentPageId;

        return $this;
    }

    public function getPageId()
    {
        return $this->pageId;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    public function getPageClass()
    {
        return $this->pageClass;
    }

    public function setPageClass($pageClass)
    {
        $this->pageClass = $pageClass;

        return $this;
    }

    public function getResourceId()
    {
        return $this->resourceId;
    }

    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public function getRedirectPath()
    {
        return $this->redirectPath;
    }

    public function setRedirectPath($redirectPath)
    {
        $this->redirectPath = $redirectPath;

        return $this;
    }

    public function isResource()
    {
        return $this->resourceId && $this->resourceClass;
    }
}
