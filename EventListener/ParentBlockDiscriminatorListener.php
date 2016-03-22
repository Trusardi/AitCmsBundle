<?php

namespace Ait\CmsBundle\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ParentBlockDiscriminatorListener
{
    use ContainerAwareTrait;

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $config = $this->container->getParameter('ait_cms.config');
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        if ($metadata->getName() === $config['class']['parent_block']) {
            $definitions = $this->container->get('ait_cms.block_manager')->getBlockDefinitions();
            $map = array_combine(array_keys($definitions), array_column($definitions, 'entity'));
            $metadata->setDiscriminatorMap($map);
        }
    }
}
