<?php
namespace FBoon\MapperBundle\Mapper;

use FBoon\MapperBundle\Annotation\AnnotationReader;
use FBoon\MapperBundle\Mapper;
/**
 * Description of XmlMapper
 *
 * @author Frank Boon <boon.frank@gmail.com>
 */
class XmlMapper extends Mapper
{
    /**
     * Map the values from the xml to a model
     *
     * @param mixed $model
     * @param SimpleXml $dataSet
     * @return mixed
     */
    public function mapToModel($model, $dataSet)
    {
        $dataSet = $this->getSingleResult($dataSet);
        if ($dataSet->getName() == 'root') {
            $dataSet = $dataSet->children();
        }

        if ($this->caseInsensitive) {
            $array = $this->xml2array($dataSet);
            $dataSet = array_change_key_case ($array, CASE_LOWER);
        }

        $properties = $this->annotationReader->getFields($model);

        $reflectionClass = new \ReflectionClass($model);
        foreach ($properties as $prop => $content) {
            foreach ($content as $key => $value) {
                if ($key == 'name') {
                    $reflectionProperty = $reflectionClass->getProperty($prop);
                    $reflectionProperty->setAccessible(true);

                    if ($this->caseInsensitive) {
                        try {
                            $reflectionProperty->setValue($model, $dataSet[strtolower($value)]);
                        } catch (\ErrorException $e) {
                            #   property still not found
                        }
                    } else {
                        $reflectionProperty->setValue($model, (string)$this->getAttribute($dataSet, $value));
                    }
                }
            }
        }

        return clone $model;
    }

    /**
     * get an attribute from a singlexml element
     *
     * @param SimpleXml $xml
     * @param String $attribute
     * @return String | null
     */
    protected function getAttribute($xml, $attribute)
    {
        foreach ($xml->attributes() as $key => $value) {
            if ($key == $attribute) {
                return $value;
            }
        }

        return null;
    }

    /**
     *
     * @param mixed $model
     * @param array $dataSet
     * @return array(mixed)
     */
    public function mapToModels($model, $dataSet)
    {
        $object = $this->annotationReader->getModel($model);
        $modelArray = array();
        $name = $object->name;

        foreach ($dataSet->$name as $single) {
            $newModel = $this->mapToModel($model, $single);
            array_push($modelArray, $newModel);
        }

        return $modelArray;
    }

    /**
     * api might give an array back even though we know it will return only 1 result
     *
     * @param array $model
     * @return array
     */
    protected function getSingleResult($xml)
    {
        try {
            $object = $this->annotationReader->getModel($xml);
            $name = $object->name;

            #   api might give an array back even though we know it will return only 1 result
            foreach ($xml->$name as $single) {
                return $single;
            }
        } catch (\Exception $e) {
            #   xml not in an array
            return $xml;
        }
    }

    protected function xml2array($xml)
    {
        $arr = array();
        foreach ($xml as $element) {
            $tag = $element->getName();
            $e = get_object_vars($element);
            if (!empty($e)) {
                $arr[$tag] = $element instanceof SimpleXMLElement ? $this->xml2array($element) : $e;
            } else {
                $arr[$tag] = trim($element);
            }
        }

        return $arr;
    }
}
