<?php

namespace Ait\CmsBundle\Manager;

use Ait\CmsBundle\Block\AbstractBlockService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockManager
{
    /**
     * Block definitions, id => [entity, class, base_class]
     *
     * @var array
     */
    protected $blockDefinitions;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $id
     * @return array
     */
    public function getDefinition($id)
    {
        return $this->blockDefinitions[$id];
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getBlockDefinitions()
    {
        return $this->blockDefinitions;
    }

    /**
     * @param array $blockDefinitions
     */
    public function setBlockDefinitions(array $blockDefinitions)
    {
        $this->blockDefinitions = $blockDefinitions;
    }

    /**
     * @param mixed $entity
     *
     * @return AbstractBlockService
     * @throws \Exception
     */
    public function findBlockByEntity($entity)
    {
        $class = is_object($entity) ? get_class($entity) : $entity;
        foreach ($this->blockDefinitions as $id => $definition) {
            if ($definition['entity'] === $class) {
                return $this->container->get($id);
            }
        }

        throw new \Exception(sprintf('Block service for entity "%s" not found!', $class));
    }

    public function getBlocks()
    {
        $container = $this->container;

        return array_combine(
            array_keys($this->blockDefinitions),
            array_map(function ($id) use ($container) {
                return $container->get($id);
            }, array_keys($this->blockDefinitions))
        );
    }

}
