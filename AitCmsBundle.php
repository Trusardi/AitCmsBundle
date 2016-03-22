<?php

namespace Ait\CmsBundle;

use Ait\CmsBundle\DependencyInjection\CompilerPass\BlockCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AitCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new BlockCompilerPass());
    }
}
