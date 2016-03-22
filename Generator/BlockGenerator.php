<?php

namespace Ait\CmsBundle\Generator;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;

class BlockGenerator {
    /**
     * @return Kernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @param Kernel $kernel
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return mixed
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param mixed $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @var Kernel
     */
    protected $kernel;

    protected $doctrine;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @param TwigEngine $templating
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    public function generateBlock($name, $fields)
    {
        $generalPath = $this->kernel->getRootDir() . '/../src/AppBundle/';
        $this->generateModel($generalPath, $name, $fields);
        $this->generateService($generalPath, $name, $fields);
        $this->generateTemplate($generalPath, $name);
        $this->generateConfig($generalPath, $name);
    }

    public function generateService($generalPath, $name, $fields)
    {
        $filePath = $this->kernel->locateResource('@AitCmsBundle/Resources/templates/ServiceStub.php.twig');

        $className = ucfirst($name);

        $addedFields = '';
        foreach($fields as $field) {
            $fieldName = $field['fieldName'];
            $addedFields .= "->add('$fieldName')" . PHP_EOL;
        }

        $templateData = [
            'add_block' => $addedFields,
            'classname' => $className
        ];

        $template = $this->templating->render($filePath, $templateData);

        file_put_contents(
            $generalPath . 'Block/'. $className .'.php',
            $template
        );
    }

    public function generateTemplate($generalPath, $name)
    {
        $templatePath = $this->kernel
            ->locateResource('@AitCmsBundle/Resources/templates/TemplateStub.html.twig');
        $sampleTemplateContent = $this->templating->render($templatePath);

        $directoryName = $generalPath . 'Resources/views/Block/' . ucfirst($name);
        $fs = new Filesystem();
        $fs->mkdir($directoryName);
        $templateName = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $name)), '_');
        $generatedFilename = $directoryName . '/' . $templateName . '.html.twig';

        file_put_contents($generatedFilename, $sampleTemplateContent);
    }

    public function generateModel($generalPath, $name, $fields)
    {
        $templatePath = $this->kernel
            ->locateResource('@AitCmsBundle/Resources/templates/EntityStub.php.twig');
        $templateData['classname'] = ucfirst($name);
        $templateData['discriminator_name'] = strtolower($name) . 'block';

        foreach ($fields as $field) {

            $field['bigName'] = ucfirst($field['fieldName']);
            $field['nullable'] = isset($field['nullable']) ? $field['nullable'] : false;
            $field['params'] = '';

            $possibleParameters = ['length', 'precision' ,'scale', 'unique'];

            foreach ($possibleParameters as $parameter) {
                if (isset($field[$parameter])) {
                    $field['params'] .= ','. $parameter . '=' . $field[$parameter];
                }
            }

            $templateData['fields'][] = $field;;
        }

        $fileContent = $this->templating->render($templatePath, $templateData);

        file_put_contents(
            $generalPath . 'Entity/'. ucfirst($name) .'Block.php',
            $fileContent
        );
    }

    public function generateConfig($generalPath, $name)
    {
        $templatePath = $this->kernel
            ->locateResource('@AitCmsBundle/Resources/templates/ConfigStub.php.twig');

        $servicesFile = $this->getKernel()->getRootDir() . '/config/services.yml';

        $templateData['underscore_classname'] = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $name)), '_');
        $templateData['class_name'] = ucfirst($name);

        $servicesContent = PHP_EOL . '    ' . $this->templating->render($templatePath, $templateData);

        file_put_contents($servicesFile, $servicesContent, FILE_APPEND);
    }
}
