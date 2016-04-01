<?php

namespace Ait\CmsBundle\Controller;

use Ait\CmsBundle\Block\AbstractBlockService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends Controller
{
    public function blankFormAction(Request $request)
    {
        $manager = $this->get('ait_cms.block_manager');
        $serviceId = $request->query->get('service_id');
        $entity = $manager->getDefinition($serviceId)['entity'];

        /** @var AbstractBlockService $block */
        $block = $this->get($serviceId);

        $admin = $this->container->get($request->query->get('admin_service_id'));
        $admin->setUniqid($request->query->get('uniqid'));

        $formBuilder = $block->buildForm(new $entity());
        $view = $formBuilder->getForm()->setParent($admin->getForm()->get('blocks'))->createView();

        $twig = $this->container->get('twig');
        $twig->getExtension('form')->renderer->setTheme($view, $admin->getFormTheme());

        return new Response($twig->render('AitCmsBundle:Form:blank_block_form.html.twig', ['form' => $view]));
    }

    public function deleteBlockAction(Request $request)
    {
        $tokenManager = $this->get('security.csrf.token_manager');
        $id = $request->request->get('id');
        $isTokenValid = $tokenManager->isTokenValid($tokenManager->getToken(sprintf('block_delete_%s', $id)));
        if ($id && $isTokenValid) {
            /** @var EntityManager $enm */
            $enm = $this->getDoctrine()->getManager();
            $block = $enm->find($this->getParameter('ait_cms.class.parent_block'), $id);
            $enm->remove($block);
            $enm->flush();
        }

        return new JsonResponse();
    }
}
