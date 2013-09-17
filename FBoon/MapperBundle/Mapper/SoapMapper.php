<?php
namespace FBoon\MapperBundle\Mapper;

use FBoon\MapperBundle\Mapper;
/**
 * Description of SoapMapper
 *
 * @author FBoon <boon.frank@gmail.com>
 */
class SoapMapper extends Mapper
{
    public function mapToModel($model, $dataSet) 
    {
        $properties = $this->annotationReader->getModelProperties($model);     
        $reflectionClass = new \ReflectionClass($model);
        $dataSet = $this->getBase($model, $dataSet);
        
        foreach ($properties['fields'] as $prop => $content) {
            foreach ($content as $key => $value) {
                if ($key == 'name') {
                    $reflectionProperty = $reflectionClass->getProperty($prop);
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($model, $dataSet->$value);
                }
            }
        }
        
        if (isset($properties['onetomany'])) {
            foreach ($properties['onetomany'] as $prop => $content) {

                $refl = new \ReflectionClass($content->object);
                $maatschappij = $refl->newInstance();
                $subObjName = $content->name;
                
                foreach ($dataSet->$subObjName as $key => $value) {
                    if (count($value) > 1) {
                        $result = $this->mapToModels($maatschappij, $value);
                    } else {
                        $result = $this->mapToModel($maatschappij, $value);
                    }
                    
                    $reflectionProperty = $reflectionClass->getProperty($prop);
                    $reflectionProperty->setAccessible(true);

                    $reflectionProperty->setValue($model, $result);
                }
            }
        }
        
        return clone $model;
    }
    
    protected function getBase($model, $dataSet)
    {
        try {
            $properties = $this->annotationReader->getModelProperties($model);    
            $objectName = $properties['object']->name;

            return $dataSet->$objectName;
        }
        catch (\Exception $e)
        {
            return $dataSet;
        }
    }

    public function mapToModels($model, $dataSet) 
    {
        $modelArray = array();
        
        foreach ($dataSet as $row) {
            $newModel = $this->mapToModel($model, $row);
            array_push($modelArray, $newModel);
        }
        
        return $modelArray;
    }
}