<?php

namespace Ait\CmsBundle\Sonata\Block;

use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

class InfoBlock extends BaseBlockService
{
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse(
            '@AitCms\Sonata\Block\info_block.html.twig',
            [
                'block' => $blockContext->getBlock()
            ],
            $response
        );
    }
}
