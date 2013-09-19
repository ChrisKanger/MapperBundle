<?php
namespace FBoon\MapperBundle\Annotation\Soap;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Attribute extends Annotation
{
    public $name;
    
    public $property;
}