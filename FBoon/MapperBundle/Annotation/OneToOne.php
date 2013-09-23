<?php

namespace FBoon\MapperBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class OneToOne extends Annotation
{
    public $name;
    
    public $object;
}