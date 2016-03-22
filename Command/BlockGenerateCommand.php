<?php

namespace Ait\CmsBundle\Command;

use Doctrine\DBAL\Types\Type;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\DependencyInjection\Container;

class BlockGenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ait:generate:block')
            ->setDescription('Input block name in CamelCase, like MeetUs')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'A class name')
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'Class fields');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        $fields = $input->getOption('fields');

        $blockGenerator = $this->getContainer()->get('ait.block.generator');

        $output->writeln(sprintf("Generating the %s Block, %s Service and %s Template in AppBundle...", $name . 'Block', $name . 'Block', $name . 'Block/default.html.twig'));
        $blockGenerator->generateBlock($name, $fields);

        $output->writeln("Run the doctrine:schema:update command, then you can use the generated code!");

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = new QuestionHelper();

        $output->writeln('Welcome to AIT Block generator');

        $output->writeln(array(
            '',
            'Input desired block name in CamelCase, like <comment>MeetUs</comment>.',
            'No need to specify the "Block" ending.',
            '',
        ));

        while (true) {
            $question = new Question($helper->getQuestion('The Block name', $input->getOption('name')), $input->getOption('name'));

            $name = $helper->ask($input, $output, $question);

            try {

                if (strlen($name) > 2) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Block name must be at least 3 letters long!</>.'));
            } catch (\Exception $e) {

                throw new \Exception($e->getMessage());
            }
        }

        $input->setOption('name', $name);

        $input->setOption('fields', $this->addFields($input, $output, $helper));
    }

    /**
     * Add fields to new Block entity
     * @see GenerateDoctrineEntityCommand
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @return array
     */
    private function addFields(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));

        $output->writeln(array(
            '',
            'Instead of starting with a blank entity, you can <info>add some fields now.</info>',
            'Just like with <comment>Sensio Generator</comment>, there will be several fields pre-generated for the block already:',
            'Id, Name, Position, Enabled, Translations.',
            '',
        ));

        $output->write('<info>Available types:</info> ');

        $types = array_keys(Type::getTypesMap());

        array_walk($types, function ($type) use ($output) {
            $output->write(sprintf('<comment>%s</comment>', $type));
            $output->write(', ');
        });
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getGenerator();
            $question = new Question($questionHelper->getQuestion('New field name (press <return> to stop adding fields)', null), null);

            $question->setValidator(function ($name) use ($fields, $generator) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                }

                if ($generator->isReservedKeyword($name)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                }

                if (!is_null($name) && !$generator->isValidPhpVariableName($name)) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid PHP variable name.', $name));
                }

                return $name;
            });

            $columnName = $questionHelper->ask($input, $output, $question);
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            $question = new Question($questionHelper->getQuestion('Field type', $defaultType), $defaultType);
            $question->setValidator($fieldValidator);
            $question->setAutocompleterValues($types);
            $type = $questionHelper->ask($input, $output, $question);

            $data = array('columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type);

            switch ($type) {
                case 'string':
                    $question = new Question($questionHelper->getQuestion('Field length', 255), 255);
                    $data['length'] = $questionHelper->ask($input, $output, $question);
                    break;
                case 'decimal':
                    // 10 is the default value given in \Doctrine\DBAL\Schema\Column::$_precision
                    $question = new Question($questionHelper->getQuestion('Precision', 10), 10);
                    $data['precision'] = $questionHelper->ask($input, $output, $question);

                    // 0 is the default value given in \Doctrine\DBAL\Schema\Column::$_scale
                    $question = new Question($questionHelper->getQuestion('Scale', 0), 0);
                    $data['scale'] = $questionHelper->ask($input, $output, $question);
                    break;
            }

            $question = new Question($questionHelper->getQuestion('Is nullable', 'false'), false);
            $question->setAutocompleterValues(array('true', 'false'));
            if ($nullable = $questionHelper->ask($input, $output, $question)) {
                $data['nullable'] = $nullable;
            }

            $question = new Question($questionHelper->getQuestion('Unique', 'false'), false);
            $question->setAutocompleterValues(array('true', 'false'));
            if ($unique = $questionHelper->ask($input, $output, $question)) {
                $data['unique'] = $unique;
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    protected function getGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }

    /**
     * @param $input
     * @return array
     */
    private function parseFields($input)
    {
        if (is_array($input)) {

            return $input;
        }

        $fields = array();
        $inputFields = preg_split('{(?:\([^\(]*\))(*SKIP)(*F)|\s+}', $input);

        foreach ($inputFields as $value) {
            $elements = explode(':', $value);
            $fieldName = $elements[0];

            $fieldAttributes = array();

            if ($fieldName) {
                $fieldAttributes['fieldName'] = $fieldName;
                $type = isset($elements[1]) ? $elements[1] : 'string';

                preg_match_all('{(.*)\((.*)\)}', $type, $matches);
                $fieldAttributes['type'] = isset($matches[1][0]) ? $matches[1][0] : $type;

                $length = null;
                if ($fieldAttributes['type'] == 'string') {
                    $fieldAttributes['length'] = $length;
                }

                if (isset($matches[2][0]) && $length = $matches[2][0]) {

                    $attributesFound = array();
                    if (false !== strpos($length, '=')) {
                        preg_match_all('{([^,= ]+)=([^,= ]+)}', $length, $result);
                        $attributesFound = array_combine($result[1], $result[2]);
                    } else {
                        $fieldAttributes['length'] = $length;
                    }

                    $fieldAttributes = array_merge($fieldAttributes, $attributesFound);
                    foreach (array('length', 'precision', 'scale') as $intAttribute) {
                        if (isset($fieldAttributes[$intAttribute])) {
                            $fieldAttributes[$intAttribute] = (int)$fieldAttributes[$intAttribute];
                        }
                    }

                    foreach (array('nullable', 'unique') as $boolAttribute) {
                        if (isset($fieldAttributes[$boolAttribute])) {
                            $fieldAttributes[$boolAttribute] = (bool)$fieldAttributes[$boolAttribute];
                        }
                    }
                }

                $fields[$fieldName] = $fieldAttributes;
            }
        }

        return $fields;
    }
}