<?php
namespace FBoon\MapperBundle\Annotation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * When adding annotations extend this AnnotationReader
 * and override the getPropertiesFromReflectionClass.
 * 
 * TODO: attributes as array
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
    
    public function getAttribute($model)
    {
        $properties = $this->getModelProperties($model);
        return $properties['attribute'];
    }
    
    public function getOneToMany($model)
    {
        $properties = $this->getModelProperties($model);
        if (isset($properties['onetomany'])) {
            return $properties['onetomany'];
        } else {
            return null;
        }
    }

    public function getModelProperties($model)
    {
        $reflClass = new \ReflectionClass($model);
        return $this->getPropertiesFromReflectionClass($reflClass);
    }

    protected function getPropertiesFromReflectionClass(\ReflectionClass $reflClass)
    {
        $properties = array(
            'object' => null,
            'attribute' => null,
            'fields' => array(),
            'onetomany' => array()
        );
        
        $classAnnotations = $this->reader->getClassAnnotations($reflClass);
        
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof Object) {
                if (isset($classAnnotation->name)) {
                    $properties['object'] = $classAnnotation;
                }
            }
            
            if ($classAnnotation instanceof Soap\Attribute) {
                if (isset($classAnnotation->name)) {
                    $properties['attribute'] = $classAnnotation;
                }
            }
        }
        
        $reflProperties = $reflClass->getProperties();

        foreach ($reflProperties as $reflProperty) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations(
                $reflProperty, 'FBoon\MapperBundle\Annotation'
            );

            foreach ($propertyAnnotations as $key => $propertyAnnotation) {
                if ($propertyAnnotation instanceof Field) {

                    $properties['fields'][$reflProperty->getName()] =
                            $this->validateCase($propertyAnnotation);
                }
                
                if ($propertyAnnotation instanceof OneToMany) {
                    
                    $properties['onetomany'][$reflProperty->getName()] = 
                            $this->validateCase($propertyAnnotation);
                }
            }
        }
        
        if ($reflClass->getParentClass()) {
            $parentProperties = $this->getPropertiesFromReflectionClass(
                $reflClass->getParentClass()
            );

            $properties['object'] = ($properties['object'])
                                        ? $properties['object']
                                        : $parentProperties['object'];
            
            $properties['attribute'] = ($properties['attribute'])
                                        ? $properties['attribute']
                                        : $parentProperties['attribute'];

            $properties['fields'] = array_merge(
                $properties['fields'],
                $parentProperties['fields']
            );
            
            $properties['onetomany'] = array_merge(
                $properties['onetomany'],
                $parentProperties['onetomany']
            );
        }
        
        return $properties;
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
