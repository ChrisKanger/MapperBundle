<?php

namespace FBoon\MapperBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class OneToMany extends Annotation
{
    public $name;
    
    public $object;
}