<?php
namespace FBoon\MapperBundle\Mapper;

use FBoon\MapperBundle\Mapper;
/**
 * Instead of mapping xml, json or something else to an entity
 * this class maps a class to an array. This can be usefull 
 * when you need to create a soap request and dont want to
 * give your properties the same name as in the request
 *
 * @author FBoon <boon.frank@gmail.com>
 */
class ToArrayMapper extends Mapper
{
    /**
     * Dataset is obsolete for this mapper
     * 
     * @param type $model
     * @param type $dataSet
     */
    public function mapToModel($model, $dataSet = null)
    {
        $properties = $this->annotationReader->getModelProperties($model);     
        $reflectionClass = new \ReflectionClass($model);
        
        $array = array();
        $fields = array();
        foreach ($reflectionClass->getProperties() as $refProps) {
            
            $refProps->setAccessible(true);
            
            if (isset($properties['fields'][$refProps->getName()])) {

                $fields[$properties['fields'][$refProps->getName()]->name] = $refProps->getValue($model);
                        
            } elseif (isset($properties['onetomany'][$refProps->getName()])) {
                
                if (is_array($refProps->getValue($model))) {
                    $fields[$properties['onetomany'][$refProps->getName()]->name] = $this->mapToModels($refProps->getValue($model));
                } else {
                    $result = $this->mapToModel($refProps->getValue($model));
                    $fields[$properties['onetomany'][$refProps->getName()]->name] = $result[$refProps->getName()];
                }
                
            } else {
                
                $fields[$refProps->getName()] = $refProps->getValue($model);
                
            }
        }
        
        if (isset( $properties['object'] )) {
            $array = array($properties['object']->name => $fields);
        } else {
            $array = array($reflectionClass->getShortName() => $fields);
        }
        
        return $array;
    }

    /**
     * Dataset is obsolete for this mapper
     * 
     * @param type $model
     * @param type $dataSet
     */
    public function mapToModels($model, $dataSet = null)
    {
        $array = array();
        foreach ($model as $row) {
            array_push($array, $this->mapToModel($row));
        }
        
        return $array;
    }    
}