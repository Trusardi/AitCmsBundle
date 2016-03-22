<?php

namespace Ait\CmsBundle\DependencyInjection;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AitCmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ait_cms.routing.enabled', $config['enable_routing']);
        $container->setParameter('ait_cms.routing.first_page_route_action', $config['first_page_route_action']);
        $container->setParameter('ait_cms.class.page', $config['class']['page']);
        $container->setParameter('ait_cms.admin.page.class', $config['admin']['page']['class']);
        $container->setParameter('ait_cms.config', $config);

        $this->registerDoctrineMapping($config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', [
            'fieldName' => 'featuredImage',
            'targetEntity' => $config['class']['sonata_media'],
            'cascade' => [
                'persist',
            ],
            'joinColumn' => [
                'onDelete' => 'SET NULL',
            ],
        ]);

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', [
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                'persist',
            ],
            'joinColumn' => [
                'onDelete' => 'SET NULL',
            ],
        ]);

        $collector->addAssociation($config['class']['page'], 'mapManyToMany', [
            'fieldName' => 'blocks',
            'targetEntity' => $config['class']['parent_block'],
            'orphanRemoval' => true,
            'cascade' => [
                'persist',
                'remove',
            ],
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addInheritanceType($config['class']['parent_block'], ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
        $collector->addDiscriminatorColumn($config['class']['parent_block'], [
            'name' => 'discr',
            'type' => 'string',
            'length' => 255,
        ]);
        $collector->addDiscriminator($config['class']['parent_block'], 'ait_cms__parent', $config['class']['parent_block']);
    }
}
