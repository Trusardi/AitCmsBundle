<?php

namespace Ait\CmsBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class RouteAction implements Annotation
{
    /**
     * Unique string that gets saved in database
     * @var string
     * @Required
     */
    public $reference;

    /**
     * User friendly template name (generates automatically if not set)
     * @var string
     */
    public $name = '';

    /**
     * Fully qualified class name to resource to whom this action serves as root page
     * @var string
     */
    public $resource = '';

    /**
     * Controller that handles open page for specified resource (use "AppBundle:Hello:index" controller syntax)
     * @var string
     */
    public $resourceController = '';
}
