<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Optimizer;

use Flow\ETL\Optimizer\BulkTransformationOptimizer;
use Flow\ETL\Tests\Double\MergeableTransformerStub;
use Flow\ETL\Tests\Double\TransformerStub;
use Flow\ETL\Transformer\BulkTransformer;
use PHPUnit\Framework\TestCase;

final class BulkTransformationOptimizerTest extends TestCase
{
    public function test_not_optimizing_regular_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizePipeline([
                new TransformerStub('1'),
                new TransformerStub('2'),
                new TransformerStub('3'),
            ]);

        $this->assertEquals(
            [
                new TransformerStub('1'),
                new TransformerStub('2'),
                new TransformerStub('3'),
            ],
            $transformers
        );
    }

    public function test_optimizing_only_row_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizePipeline([
                new MergeableTransformerStub('1'),
                new MergeableTransformerStub('2'),
                new MergeableTransformerStub('3'),
            ]);

        $this->assertEquals(
            [
                new BulkTransformer(
                    new MergeableTransformerStub('1'),
                    new MergeableTransformerStub('2'),
                    new MergeableTransformerStub('3'),
                ),
            ],
            $transformers
        );
    }

    public function test_optimizing_transformer_between_rows_and_entries_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizePipeline([
                new MergeableTransformerStub('1'),
                new MergeableTransformerStub('2'),
                new TransformerStub('1'),
                new MergeableTransformerStub('3'),
                new MergeableTransformerStub('4'),
                new BulkTransformer(
                    new MergeableTransformerStub('5'),
                    new MergeableTransformerStub('6'),
                ),
                new MergeableTransformerStub('7'),

            ]);

        $this->assertEquals(
            [
                new BulkTransformer(
                    new MergeableTransformerStub('1'),
                    new MergeableTransformerStub('2'),
                ),
                new TransformerStub('1'),
                new BulkTransformer(
                    new MergeableTransformerStub('3'),
                    new MergeableTransformerStub('4'),
                    new BulkTransformer(
                        new MergeableTransformerStub('5'),
                        new MergeableTransformerStub('6'),
                    ),
                    new MergeableTransformerStub('7'),
                ),
            ],
            $transformers,
        );
    }
}
