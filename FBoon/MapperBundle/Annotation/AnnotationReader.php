<?php
namespace FBoon\MapperBundle\Annotation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * When adding annotations extend this AnnotationReader
 * and override the getPropertiesFromReflectionClass.
 *
 * @author Frank Boon <boon.frank@gmail.com>
 */
class AnnotationReader
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Boolean
     */
    protected $caseInsensitive = true;

    public function __construct(Reader $reader, $caseInsensitive = false)
    {
        $this->reader = $reader;
        $this->caseInsensitive = $caseInsensitive;
    }

    public function getFields($model)
    {
        $properties = $this->getModelProperties($model);
        return $properties['fields'];
    }

    public function getModel($model)
    {
        $properties = $this->getModelProperties($model);
        return $properties['object'];
    }

    public function getModelProperties($model)
    {
        $reflClass = new \ReflectionClass($model);
        return $this->getPropertiesFromReflectionClass($reflClass);
    }

    protected function getPropertiesFromReflectionClass(\ReflectionClass $reflClass)
    {
        $classAnnotation = $this->reader->getClassAnnotation($reflClass,
            'FBoon\MapperBundle\Annotation\Object'
        );
        
        if (isset($classAnnotation->name)) {
            $tvdbProperties['object'] = $classAnnotation;
        }
        
        $reflProperties = $reflClass->getProperties();

        foreach ($reflProperties as $reflProperty) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations(
                $reflProperty, 'FBoon\MapperBundle\Annotation'
            );

            foreach ($propertyAnnotations as $key => $propertyAnnotation) {
                if ($propertyAnnotation instanceof Field) {

                    $tvdbProperties['fields'][$reflProperty->getName()] =
                            $this->validateCase($propertyAnnotation);
                }
            }
        }

        if ($reflClass->getParentClass()) {
            $properties = $this->getPropertiesFromReflectionClass(
                $reflClass->getParentClass()
            );

            $tvdbProperties['object'] = ($tvdbProperties['object'])
                                              ? $tvdbProperties['object']
                                              : $properties['object'];

            $tvdbProperties['fields'] = array_merge(
                $properties['fields'],
                $tvdbProperties['fields']
            );
        }

        return $tvdbProperties;
    }

    protected function validateCase($property)
    {
        if ($this->caseInsensitive) {
            $property->name = strtolower($property->name);
            return $property;
        } else {
            return $property;
        }
    }

    public function setCaseInsensitive($caseSensitive)
    {
        $this->caseInsensitive = $caseSensitive;
    }
}
