<?php

namespace Ait\CmsBundle\Routing;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class Listener
{
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $pageEntityClass;

    /**
     * @var RouteData[]
     */
    protected $routeCollection;

    /**
     * @var array
     */
    protected $locales;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var array
     */
    protected $routeActions;

    /**
     * @var string
     */
    protected $firstPageRouteAction;

    /**
     * @param string $firstPageRouteAction
     */
    public function setFirstPageRouteAction($firstPageRouteAction)
    {
        $this->firstPageRouteAction = $firstPageRouteAction;
    }

    public function getRouteActions()
    {
        return $this->routeActions;
    }

    /**
     * @param array $routeActions
     */
    public function setRouteActions($routeActions)
    {
        $this->routeActions = $routeActions;
    }

    /**
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @param string $pageEntityClass
     */
    public function setPageEntityClass($pageEntityClass)
    {
        $this->pageEntityClass = $pageEntityClass;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (! $this->enabled || ! $event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($routeData = $this->match($request)) {
            $event->getRequest()->attributes->set('_controller', $routeData->getController());
            $event->getRequest()->attributes->set('route_data', $routeData);
            $event->getRequest()->setLocale($routeData->getLocale());
        }
    }

    /**
     * @param Request $request
     * @return RouteData
     * @throws \Exception
     */
    protected function match(Request $request)
    {
        $routes = $this->getRouteCollection();
        $requestPath = strtolower($request->getPathInfo());

        return isset($routes[$requestPath]) ? $routes[$requestPath] : false;
    }

    /**
     * @return RouteData[]
     * @throws \Exception
     */
    public function getRouteCollection()
    {
        if ($this->routeCollection) {
            return $this->routeCollection;
        }

        if (is_null($this->pageEntityClass)) {
            //todo: this is ugly, the class.page should be required if enable_routing is true
            throw new \Exception('You need to set `class.page` config value');
        }

        if (is_null($this->firstPageRouteAction)) {
            //todo: this is ugly, the class.page should be required if enable_routing is true
            throw new \Exception('You need to set `class.page` config value');
        }

        $pages = $this->entityManager
            ->createQueryBuilder()
            ->select('page')
            ->from($this->pageEntityClass, 'page')
            ->where('page.enabled = 1')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult();

        $routeCollection = [];

        foreach ($pages as $page) {
            $resource = $this->routeActions[$page->getRouteAction()]['resource'];
            $resourceItems = [];
            if ($resource && class_exists($resource)) {
                $resourceItems = $this->entityManager
                    ->createQueryBuilder()
                    ->select('item')
                    ->from($resource, 'item')
                    ->setMaxResults(1000)
                    ->getQuery()
                    ->getResult();
            }

            foreach ($this->locales as $locale) {
                $path = sprintf('/%s', $page->getTranslation('slug', $locale));

                $firstParent = $page->getParent();
                $lastParent = $firstParent;
                $parent = $firstParent;
                while ($parent) {
                    $path = sprintf('/%s%s', $parent->getTranslation('slug', $locale), $path);
                    $parent = $parent->getParent();
                    if ($parent) {
                        $lastParent = $parent;
                    }
                }

                if ($locale !== $this->defaultLocale) {
                    $path = sprintf('/%s%s', $locale, $path);
                }

                if ($page->getRouteAction() === $this->firstPageRouteAction) {
                    $path = $locale === $this->defaultLocale ? '/' : sprintf('/%s', $locale);
                }

                $route = new RouteData();
                $route->setPath($path)
                    ->setLocale($locale)
                    ->setController($this->routeActions[$page->getRouteAction()]['controller'])
                    ->setFirstParentPageId($firstParent ? $firstParent->getId() : null)
                    ->setLastParentPageId($lastParent ? $lastParent->getId() : null)
                    ->setPageId($page->getId())
                    ->setPageClass(get_class($page));
                $routeCollection[$path] = $route;

                $pagePath = $path;
                foreach ($resourceItems as $resourceItem) {
                    $path = sprintf('%s/%s', $pagePath, $resourceItem->getTranslation('slug', $locale));

                    $route = new RouteData();
                    $route->setPath($path)
                        ->setLocale($locale)
                        ->setController($this->routeActions[$page->getRouteAction()]['resource_controller'])
                        ->setFirstParentPageId($firstParent ? $firstParent->getId() : null)
                        ->setLastParentPageId($parent ? $parent->getId() : null)
                        ->setPageId($page->getId())
                        ->setPageClass(get_class($page))
                        ->setResourceId($resourceItem->getId())
                        ->setResourceClass(get_class($resourceItem));
                    $routeCollection[$path] = $route;
                }
            }
        }

        return $this->routeCollection = $routeCollection;
    }
}
