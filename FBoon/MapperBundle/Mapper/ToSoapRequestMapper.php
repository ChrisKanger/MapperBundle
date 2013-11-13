<?php
namespace FBoon\MapperBundle\Mapper;

use FBoon\MapperBundle\Mapper;
use FBoon\MapperBundle\Annotation\Soap\Encoding;
/**
 * Instead of mapping xml, json or something else to an entity
 * this class maps a class to an array. This can be usefull 
 * when you need to create a soap request and dont want to
 * give your properties the same name as in the request
 *
 * @author FBoon <boon.frank@gmail.com>
 */
class ToSoapRequestMapper extends Mapper
{
    protected $namespace = null;
    
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
    
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
            
            if (isset($properties['fields'][$refProps->getName()])
                    || isset($properties['onetomany'][$refProps->getName()])) {
                $refProps->setAccessible(true);

                if (isset($properties['fields'][$refProps->getName()])) {
                    
                    if (isset($properties['fields'][$refProps->getName()]->nullable) 
                            && $properties['fields'][$refProps->getName()]->nullable == true) {
                        //  Very ugly emergency solution... PHP doesnt seem to like attributes
                        $fields[] = new \SoapVar('<ns1:'.$properties['fields'][$refProps->getName()]->name.' xsi:nil="true" />', XSD_ANYXML);
                    } else {
                        $fields[] = new \SoapVar(
                            $refProps->getValue($model), 
                            $this->getEncoding($properties['fields'][$refProps->getName()]->type), 
                            null, 
                            null, 
                            $properties['fields'][$refProps->getName()]->name, 
                            $this->namespace
                        );                        
                    }
                    
                } elseif (isset($properties['onetomany'][$refProps->getName()])) {

                    if (is_array($refProps->getValue($model))) {      
                        $result = $this->mapToModels($refProps->getValue($model));
                        
                        $fields[] = new \SoapVar(
                            $result, 
                            SOAP_ENC_OBJECT, 
                            null, 
                            null, 
                            $properties['onetomany'][$refProps->getName()]->name, 
                            $this->namespace
                        );        
                    } else {
                        $result = $this->mapToModel($refProps->getValue($model));
                        
                        if (isset($result->enc_stype)) {
                            $typeName = $result->enc_stype;
                        } else {
                            $typeName = null;
                        }
                        
                        $fields[] = new \SoapVar(
                            $result, 
                            SOAP_ENC_OBJECT, 
                            $typeName, 
                            null, 
                            $properties['onetomany'][$refProps->getName()]->name, 
                            $this->namespace
                        );
                    }

                } else {
                    $fields[] = new \SoapVar(
                        $refProps->getValue($model), 
                        XSD_ANYTYPE, 
                        null, 
                        null, 
                        $refProps->getName(), 
                        $this->namespace
                    );

                }
            }
        }
        
        if (isset($properties['object'] )) {
            if (isset($properties['attribute'])) {
                $prop =  $reflectionClass->getProperty($properties['attribute']->property);
                $prop->setAccessible(true);
                
                // Very ugly namespace solution for the xsi:type :(
                $array = new \SoapVar(
                    $fields, 
                    SOAP_ENC_OBJECT, 
                    'ns1:'.$prop->getValue($model), 
                    null, 
                    $properties['object']->name, 
                    $this->namespace
                );     
                
            } else {
                $array = new \SoapVar($fields, SOAP_ENC_OBJECT, null, null, $properties['object']->name, $this->namespace);
            }
        } else {
            $array = new \SoapVar($fields, SOAP_ENC_OBJECT, null, null, $reflectionClass->getShortName(), $this->namespace);
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
    
    protected function getEncoding($encoding)
    {
        switch ($encoding)
        {
            case "string":
                return Encoding::STRING;
                break;
            case "integer":
                return Encoding::INTEGER;
                break;
            case "boolean":
                return Encoding::BOOLEAN;
                break;
        }
    }
}