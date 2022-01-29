<?php

declare(strict_types=1);

namespace Flow\ETL\Optimizer;

use Flow\ETL\Transformer;

final class BulkTransformationOptimizer implements Optimizer
{
    public function optimizeTransformers(Transformer ...$transformers) : array
    {
        $transformersArray = $transformers;
        /**
         * @var array<Transformer> $optimizedTransformers
         */
        $optimizedTransformers = [];
        $bulkTransformer = Transformer\BulkTransformer::empty();
        /**
         * @var ?Transformer $previousTransformer
         */
        $previousTransformer = null;

        while (\count($transformersArray)) {
            $transformer = \array_shift($transformersArray);

            if (!$this->isBulkTransformer($transformer)) {
                if (!$bulkTransformer->isEmpty()) {
                    $optimizedTransformers[] = $bulkTransformer;
                    $bulkTransformer = Transformer\BulkTransformer::empty();
                }

                $optimizedTransformers[] = $transformer;
            } else {
                if ($previousTransformer !== null && !$this->isBulkTransformer($previousTransformer) && !$bulkTransformer->isEmpty()) {
                    $optimizedTransformers[] = $bulkTransformer;
                    $bulkTransformer = Transformer\BulkTransformer::empty();
                }

                if ($transformer instanceof Transformer\EntryTransformer) {
                    $bulkTransformer = $bulkTransformer->addEntry($transformer);
                } elseif ($transformer instanceof Transformer\RowTransformer) {
                    $bulkTransformer = $bulkTransformer->addRow($transformer);
                }
            }

            $previousTransformer = $transformer;
        }

        if (!$bulkTransformer->isEmpty()) {
            return \array_merge($optimizedTransformers, [$bulkTransformer]);
        }

        return $optimizedTransformers;
    }

    /**
     * @param Transformer $transformer
     *
     * @return bool
     */
    private function isBulkTransformer(Transformer $transformer) : bool
    {
        return $transformer instanceof Transformer\EntryTransformer || $transformer instanceof Transformer\RowTransformer;
    }
}
