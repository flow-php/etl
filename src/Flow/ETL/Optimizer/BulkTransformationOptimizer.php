<?php

declare(strict_types=1);

namespace Flow\ETL\Optimizer;

use Flow\ETL\Loader;
use Flow\ETL\Transformer;

final class BulkTransformationOptimizer implements Optimizer
{
    /**
     * @param array<Loader|Transformer> $elements
     *
     * @return array<Loader|Transformer>
     */
    public function optimizePipeline(array $elements) : array
    {
        $elementsArray = $elements;
        /**
         * @var array<Loader|Transformer> $optimizedPipeline
         */
        $optimizedPipeline = [];
        $bulkTransformer = Transformer\BulkTransformer::empty();
        /**
         * @var null|Loader|Transformer $previousElement
         */
        $previousElement = null;

        while (\count($elementsArray)) {
            $element = \array_shift($elementsArray);

            if ($element instanceof Loader || !$element instanceof Transformer\MergeableTransformer) {
                if (!$bulkTransformer->isEmpty()) {
                    $optimizedPipeline[] = $bulkTransformer;
                    $bulkTransformer = Transformer\BulkTransformer::empty();
                }

                $optimizedPipeline[] = $element;
            } else {
                if ($previousElement !== null && !$previousElement instanceof Transformer\MergeableTransformer && !$bulkTransformer->isEmpty()) {
                    $optimizedPipeline[] = $bulkTransformer;
                    $bulkTransformer = Transformer\BulkTransformer::empty();
                }

                $bulkTransformer = $bulkTransformer->add($element);
            }

            $previousElement = $element;
        }

        if (!$bulkTransformer->isEmpty()) {
            return \array_merge($optimizedPipeline, [$bulkTransformer]);
        }

        return $optimizedPipeline;
    }
}
