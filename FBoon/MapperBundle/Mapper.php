<?php
namespace FBoon\MapperBundle;

use FBoon\MapperBundle\Annotation\AnnotationReader;
/**
 * Extend this class to create a new mapper
 *
 * @author Frank Boon <boon.frank@gmail.com>
 */
abstract class Mapper
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var Boolean
     */
    protected $caseInsensitive;

    /**
     *
     * @param AnnotationReader $annotationReader
     * @param Boolean $case
     */
    public function __construct(AnnotationReader $annotationReader, $case = false)
    {
        $this->annotationReader = $annotationReader;
        $this->annotationReader->setCaseInsensitive($case);
        $this->caseInsensitive = $case;
    }

    public function getCaseInsensitive()
    {
        return $this->caseInsensitive;
    }

    public function setCaseInsensitive($caseInsensitive)
    {
        $this->caseInsensitive = $caseInsensitive;
    }

    public abstract function mapToModel($model, $dataSet);

    public abstract function mapToModels($model, $dataSet);
}
