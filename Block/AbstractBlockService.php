<?php

namespace Ait\CmsBundle\Block;

use Ait\CmsBundle\Model\BlockInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class AbstractBlockService
{
    use ContainerAwareTrait;

    /**
     * @var TwigEngine
     */
    protected $templating;
    
    /**
     * @var array
     */
    protected $nativeTemplates = [];

    /**
     * @var array
     */
    protected $overrideTemplates = [];

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @param TwigEngine $templating
     */
    public function setTemplating(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param array $nativeTemplates
     */
    public function setNativeTemplates(array $nativeTemplates)
    {
        $this->nativeTemplates = $nativeTemplates;
    }

    /**
     * @param array $overrideTemplates
     */
    public function setOverrideTemplates(array $overrideTemplates)
    {
        $this->overrideTemplates = $overrideTemplates;
    }

    /**
     * Should be overridden in real blocks
     *
     * @return string
     */
    public function getTitle()
    {
        return str_replace('BlockService', '', array_reverse(explode('\\', get_called_class()))[0]);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return array_merge($this->overrideTemplates, $this->nativeTemplates);
    }

    public function render(BlockInterface $entity)
    {
        return $this->templating->render($entity->getTemplate(), ['block' => $entity]);
    }

    protected function getReference(BlockInterface $block)
    {
        $prefix = (new CamelCaseToSnakeCaseNameConverter())->normalize((new \ReflectionClass($block))->getShortName());

        return sprintf('%s_%s', $prefix, $block->getId() ?: uniqid());
    }

    protected function addDefaultFields(FormBuilderInterface $formBuilder)
    {
        $templates = $this->getTemplates();
        $formBuilder
            ->add('name')
            ->add('template', 'choice', ['choices' => array_combine($templates, $templates)])
            ->add('enabled', 'checkbox');

        if ($formBuilder->getData()->getId()) {
            $formBuilder->add('remove', 'checkbox', ['mapped' => false]);
        }

        $formBuilder
            ->add('position', 'integer')
            ->add('entity', 'hidden', ['data' => $this->entityClass, 'mapped' => false]);

        return $formBuilder;
    }

    protected function createFormBuilder(BlockInterface $block)
    {
        $formBuilder = $this->container
            ->get('form.factory')
            ->createNamedBuilder(
                $this->getReference($block),
                FormType::class,
                $block,
                [
                    'data_class' => (new \ReflectionClass($block))->getName(),
                    'attr' => [
                        'class' => 'form-group',
                    ],
                ]
            );

        return $this->addDefaultFields($formBuilder);
    }

    /**
     * Transform block array to block object
     *
     * @param array $blockArray
     *
     * @return BlockInterface
     */
    public function arrayToBlock(array $blockArray)
    {
        $blockInstance = new $blockArray['entity'];

        foreach ($blockArray as $label => $value) {
            $method = sprintf('set%s', ucfirst($label));
            if (method_exists($blockInstance, $method)) {
                $blockInstance->$method($value);
            }
        }

        return $blockInstance;
    }

    /**
     * @param BlockInterface $block
     * @return FormBuilderInterface
     */
    abstract public function buildForm(BlockInterface $block);
}
