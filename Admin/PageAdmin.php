<?php

namespace Ait\CmsBundle\Admin;

use Ait\CmsBundle\Form\Type\BlockWorkspaceType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PageAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $routeActions = $container->get('ait_cms.routing.listener')->getRouteActions();

        $formMapper
            ->with('Blocks', ['class' => 'col-md-8'])
                ->add('blocks', BlockWorkspaceType::class)
            ->end()
            ->with('General', ['class' => 'col-md-4'])
                ->add('name', 'text')
                ->add('slug', 'text', [
                    'required' => false,
                ])
                ->add('routeAction', 'choice', [
                    'choices' => array_combine(array_keys($routeActions), array_column($routeActions, 'name')),
                    'required' => false,
                    'placeholder' => 'Not set',
                ])
                ->add('parent', 'sonata_type_model_list', [
                    'required' => false,
                    'btn_add' => false,
                ])
                ->add('excerpt', 'textarea', [
                    'required' => false,
                ])
                ->add('content', 'textarea', [
                    'required' => false,
                    'attr' => [
                        'class' => 'wysiwyg',
                    ],
                ])
                ->add('featuredImage', 'sonata_type_model_list', [
                    'required' => false,
                ])
                ->add('enabled', 'checkbox', [
                    'required' => false,
                ])
            ->end()
            ->with('Extra Fields', ['class' => 'col-md-4 pull-right'])
                ->add('extraFieldDefinitions', 'textarea', [
                    'required' => false,
                    'label' => 'Extra Field Definitions (Comma-separated)',
                    'help' => 'Example: "btn_text, left_sidebar_title". These definitions generate extra fields after save.',
                ])
                ->add('extraFields', 'sonata_type_immutable_array', [
                    'required' => false,
                    'keys' => array_map(function ($definition) {
                        return [$definition, 'text', []];
                    }, $this->prepareExtraFieldDefinitions($this->getSubject())),
                ])
            ->end()
            ->with('SEO', ['class' => 'col-md-4 pull-right'])
                ->add('seoTitle', 'text', [
                    'required' => false,
                ])
                ->add('seoDescription', 'textarea', [
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->addIdentifier('parent')
            ->addIdentifier('createdAt')
            ->add('enabled', null, ['editable' => true])
        ;
    }

    private function prepareExtraFieldDefinitions($subject)
    {
        if (!$subject) {
            return [];
        }

        $extraFieldDefinitions = $subject->getExtraFieldDefinitions();
        $definitions = [];
        $possibleDefinitions = explode(',', trim($extraFieldDefinitions, ', '));
        foreach ($possibleDefinitions as $possibleDefinition) {
            $possibleDefinition = trim($possibleDefinition, ', ');
            if ($possibleDefinition) {
                $definitions[] = $possibleDefinition;
            }
        }

        return $definitions;
    }
}
