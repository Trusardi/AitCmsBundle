<?php

namespace Ait\CmsBundle\Twig;

use Ait\CmsBundle\Manager\BlockManager;

class AitCmsExtension extends \Twig_Extension
{
    /**
     * @var BlockManager
     */
    protected $manager;

    /**
     * @param BlockManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    public function renderBlock($entity)
    {
        return $this->manager->findBlockByEntity(get_class($entity))->render($entity);
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_block', [$this, 'renderBlock'], ['is_safe' => ['html']])
        ];
    }

    public function getName()
    {
        return 'ait_cms';
    }

}
