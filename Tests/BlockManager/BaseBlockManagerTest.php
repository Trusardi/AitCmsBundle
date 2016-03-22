<?php

namespace Ait\CmsBundle\Tests\BlockManager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseBlockManagerTest extends KernelTestCase
{
    protected function setUp()
    {
        self::bootKernel();
    }

    protected function getContainer()
    {
        return self::$kernel->getContainer();
    }

    public function testBlockManagerOverall()
    {
        $manager = $this->getContainer()->get('ait_cms.block_manager');
    }
}
