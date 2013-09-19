<?php

namespace FBoon\MapperBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Field extends Annotation
{
    public $name;
    
    public $type;
    
//    public $isAttribute;
    
    public $nullable;
}