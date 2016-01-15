<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 09.12.15
 * Time: 14:33
 */

namespace TQ\ExtDirect\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;
use TQ\ExtDirect\Annotation\Parameter;
use TQ\ExtDirect\Metadata\ActionMetadata;
use TQ\ExtDirect\Metadata\MethodMetadata;

/**
 * Class AnnotationDriver
 *
 * @package TQ\ExtDirect\Metadata\Driver
 */
class AnnotationDriver implements DriverInterface
{
    const ACTION_ANNOTATION_CLASS = 'TQ\ExtDirect\Annotation\Action';
    const METHOD_ANNOTATION_CLASS = 'TQ\ExtDirect\Annotation\Method';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $actionMetadata = new ActionMetadata($class->name);

        $actionMetadata->fileResources[] = $class->getFilename();

        $actionAnnotation = $this->reader->getClassAnnotation($class, self::ACTION_ANNOTATION_CLASS);

        /** @var \TQ\ExtDirect\Annotation\Action $actionAnnotation */
        if ($actionAnnotation !== null) {
            $actionMetadata->isAction  = true;
            $actionMetadata->serviceId = $actionAnnotation->serviceId ?: null;
            $actionMetadata->alias     = $actionAnnotation->alias ?: null;
        } else {
            return null;
        }

        $methodCount = 0;
        foreach ($class->getMethods() as $reflectionMethod) {
            if (!$reflectionMethod->isPublic()) {
                continue;
            }

            $methodMetadata   = new MethodMetadata($class->name, $reflectionMethod->name);
            $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, self::METHOD_ANNOTATION_CLASS);

            /** @var \TQ\ExtDirect\Annotation\Method $methodAnnotation */
            if ($methodAnnotation !== null) {
                $methodMetadata->isMethod       = true;
                $methodMetadata->isFormHandler  = $methodAnnotation->formHandler;
                $methodMetadata->hasNamedParams = $methodAnnotation->namedParams;
                $methodMetadata->isStrict       = $methodAnnotation->strict;
                $methodMetadata->addParameters($reflectionMethod->getParameters());

                foreach ($this->reader->getMethodAnnotations($reflectionMethod) as $annotation) {
                    if ($annotation instanceof Parameter) {
                        if (!empty($annotation->constraints)) {
                            $methodMetadata->addParameterConstraints(
                                $annotation->name,
                                $annotation->constraints,
                                $annotation->validationGroups,
                                $annotation->strict
                            );
                        }
                    }
                }

                $actionMetadata->addMethodMetadata($methodMetadata);
                $methodCount++;
            }
        }

        if ($methodCount < 1) {
            return null;
        }

        return $actionMetadata;
    }
}
