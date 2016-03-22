<?php

namespace Ait\CmsBundle\Form\Type;

use Ait\CmsBundle\Manager\BlockManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockWorkspaceType extends AbstractType
{
    use ContainerAwareTrait;

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'label' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['blocks'] = $this->manager->getBlocks();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subject = $builder->getAttribute('sonata_admin')['admin']->getSubject();
        $subjectBlocks = $subject ? $subject->getBlocks() : [];

        foreach ($subjectBlocks as $subjectBlock) {
            $builder->add(
                $this->manager->findBlockByEntity($subjectBlock)->buildForm($subjectBlock)
            );
        }

        // Remove existing blocks to avoid duplicates
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $event->getForm()->getData()->clear();
            }
        );

        // Convert newly added blocks to block objects and add to persistent collection
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                foreach ($event->getForm()->getExtraData() as $blockArray) {
                    $event->getData()->add(
                        $this->manager->findBlockByEntity($blockArray['entity'])->arrayToBlock($blockArray)
                    );
                }
            }
        );

        // Remove unwanted blocks
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $enm = $this->container->get('doctrine')->getManager();
                foreach ($event->getForm()->all() as $blockForm) {
                    if ($blockForm->get('remove')->getData()) {
                        $enm->remove($blockForm->getData());
                    }
                }
                $enm->flush();
            }
        );
    }

    public function getName()
    {
        return 'ait_blocks';
    }
}
