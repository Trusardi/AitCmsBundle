<?php

namespace Ait\CmsBundle\Controller;

use Ait\CmsBundle\Block\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends Controller
{
    public function blankFormAction(Request $request)
    {
        $serviceId = $request->query->get('service_id');

        $manager = $this->get('ait_cms.block_manager');

        $entity = $manager->getDefinition($serviceId)['entity'];

        $block = $this->get($serviceId);
        /** @var AbstractBlockService $block */

        $pageAdmin = $this->container->get('ait_cms.admin.page');
        $pageAdmin->setUniqid($request->query->get('uniqid'));

        $formBuilder = $block->buildForm(new $entity());


        $view = $formBuilder->getForm()->setParent($pageAdmin->getForm()->get('blocks'))->createView();

        $this->container->get('twig')->getExtension('form')->renderer->setTheme($view, $pageAdmin->getFormTheme());

        return new Response(
            $this->container->get('twig')->render('AitCmsBundle:Form:blank_block_form.html.twig', ['form' => $view])
        );
    }

}
