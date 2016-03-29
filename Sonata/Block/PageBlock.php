<?php

namespace Ait\CmsBundle\Sonata\Block;

use Doctrine\ORM\EntityManager;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class PageBlock extends BaseBlockService
{
    protected $enm;

    protected $pageClass;

    public function __construct($name, EngineInterface $templating, EntityManager $enm, $pageClass)
    {
        parent::__construct($name, $templating);
        $this->enm = $enm;
        $this->pageClass = $pageClass;
    }


    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $pages = $this->enm->createQueryBuilder()
            ->select('p.id, p.name')
            ->from('AppBundle:Page', 'p')
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        return $this->renderResponse('AitCmsBundle:Sonata/Block:page_block.html.twig', [
            'block' => $blockContext->getBlock(),
            'pages' => $pages,
        ], $response);
    }
}
