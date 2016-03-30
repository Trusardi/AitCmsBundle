<?php

namespace Ait\CmsBundle\DependencyInjection\CompilerPass;

use Ait\CmsBundle\Annotation\RouteAction;
use Doctrine\Common\Annotations\AnnotationReader;
use Sonata\DoctrineORMAdminBundle\DependencyInjection\Compiler\AddTemplatesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class BlockCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $blockServiceIds = array_keys($container->findTaggedServiceIds('ait_cms.block'));

        //extract block defintions and add them to manager
        $managerDefinition = $container->getDefinition('ait_cms.block_manager');

        $blockDefinitions = [];

        foreach ($blockServiceIds as $blockServiceId) {
            $blockDefinition = $container->getDefinition($blockServiceId);

            $blockDefinitions[$blockServiceId] = [
                'entity' => $blockDefinition->getArgument(0),
                'class' => $blockDefinition->getClass(),
                'base_class' => str_replace('BlockService', '', array_reverse(explode('\\', $blockDefinition->getClass()))[0])
            ];
        }

        $baseBlockClasses = array_column($blockDefinitions, 'base_class');

        $managerDefinition->addMethodCall('setBlockDefinitions', [$blockDefinitions]);

        //add template definitions to blocks
        $blockTemplates = [];

        foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
            //todo: ugly
            $bundleReflection = new \ReflectionClass($bundleClass);

            if ($bundleName == 'AitCmsBundle') {
                $blockTemplateDirectory = realpath(dirname($bundleReflection->getFileName()) . '/Resources/views/Block');
            } else {
                $blockTemplateDirectory = realpath(dirname($bundleReflection->getFileName()) . '/Resources/views/AitCmsBundle/Block');
            }

            if (!$blockTemplateDirectory) {
                continue;
            }

            $finder = new Finder();

            foreach ($finder->in($blockTemplateDirectory)->directories() as $directory) {
                /* @var $directory \Symfony\Component\Finder\SplFileInfo */
                $fileFinder = new Finder();
                foreach ($fileFinder->in($directory->getRealPath())->name('*.html.twig')->files() as $file) {
                    /* @var $file \Symfony\Component\Finder\SplFileInfo */
                    $blockName = $directory->getBasename();

                    if (!in_array($blockName, $baseBlockClasses)) {
                        continue;
                    }

                    if ('AitCmsBundle' == $bundleName) {
                        $template = sprintf('AitCmsBundle:Block/%s:%s', $blockName, $file->getBasename());
                        $blockTemplates[$blockName]['native'][] = $template;
                    } else {
                        $template = sprintf('%s:AitCmsBundle/Block/%s:%s', $bundleName, $blockName, $file->getBasename());
                        $blockTemplates[$blockName]['override'][] = $template;
                    }
                }
            }
        }

        //add template definitions
        array_walk($blockDefinitions, function($info, $id) use ($blockTemplates, $container) {
            $baseClass = $blockTemplates[$info['base_class']];

            if (isset($baseClass)) {
                $blockDefinition = $container->getDefinition($id);
                if (isset($blockTemplates[$info['base_class']]['native'])) {
                    $blockDefinition
                        ->addMethodCall('setNativeTemplates', [$baseClass['native']]);
                }

                if (isset($blockTemplates[$info['base_class']]['override'])) {
                    $blockDefinition
                        ->addMethodCall('setOverrideTemplates', [$baseClass['override']]);
                }
            }
         });

        //misc definitions (templating, entity)
        foreach ($blockDefinitions as $id => $info) {
            $definition = $container->getDefinition($id);

            $definition->addMethodCall('setTemplating', [new Reference('templating')]);
            $definition->addMethodCall('setEntityClass', [$info['entity']]);
        }

        //modify twig templating
        $resources = $container->getParameter('twig.form.resources');
        $resources[] = 'AitCmsBundle:Form:widgets.html.twig';
        $container->setParameter('twig.form.resources', $resources);

        //route actions parsing
        $routeActions = [];

        foreach ($container->getParameter('kernel.bundles') as $bundleName => $bundleClass) {
            //todo: ugly
            $bundleReflection = new \ReflectionClass($bundleClass);

            $controllerDirectory = realpath(dirname($bundleReflection->getFileName()) . '/Controller');

            if (!$controllerDirectory) {
                continue;
            }

            //todo: process Controller\Api\FoobarController, remove depth limit
            foreach ((new Finder())->files()->depth(0)->in($controllerDirectory) as $file) {
                $controllerClass = sprintf('%s\\Controller\\%s', $bundleReflection->getNamespaceName(), $file->getBasename('.php'));

                foreach ((new \ReflectionClass($controllerClass))->getMethods() as $method) {
                    /** @var RouteAction $annotation */
                    $annotation = (new AnnotationReader())->getMethodAnnotation($method, RouteAction::class);
                    if ($annotation) {
                        $routeActions[$annotation->reference] = [
                            'name' => sprintf(
                                '%s (%s)',
                                $annotation->name ?: ucfirst(str_replace('_', ' ', $annotation->reference)),
                                $annotation->reference
                            ),
                            'controller' => sprintf(
                                '%s:%s:%s',
                                $bundleReflection->getNamespaceName(),
                                str_replace('Controller', '', $file->getBasename('.php')),
                                str_replace('Action', '', $method->getShortName())
                            ),
                            'resource' => $annotation->resource,
                            'resource_controller' => $annotation->resourceController,
                        ];
                    }
                }
            }
        }

        $routingListenerDefinition = $container->getDefinition('ait_cms.routing.listener');
        $routingListenerDefinition->addMethodCall('setRouteActions', [$routeActions]);

        $sonataTemplates = $container->getParameter('sonata.admin.configuration.templates');
        $sonataTemplates['layout'] = 'AitCmsBundle:Sonata/Admin:standard_layout.html.twig';
        $container->setParameter('sonata.admin.configuration.templates', $sonataTemplates);
    }
}
