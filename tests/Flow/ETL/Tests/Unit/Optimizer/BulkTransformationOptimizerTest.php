<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Optimizer;

use Flow\ETL\Optimizer\BulkTransformationOptimizer;
use Flow\ETL\Tests\Double\EntryTransformerStub;
use Flow\ETL\Tests\Double\RowTransformerStub;
use Flow\ETL\Tests\Double\TransformerStub;
use Flow\ETL\Transformer\BulkTransformer;
use PHPUnit\Framework\TestCase;

final class BulkTransformationOptimizerTest extends TestCase
{
    public function test_not_optimizing_regular_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizeTransformers(
                new TransformerStub('1'),
                new TransformerStub('2'),
                new TransformerStub('3'),
            );

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
            ->optimizeTransformers(
                new RowTransformerStub('1'),
                new RowTransformerStub('2'),
                new RowTransformerStub('3'),
            );

        $this->assertEquals(
            [
                BulkTransformer::rows(
                    new RowTransformerStub('1'),
                    new RowTransformerStub('2'),
                    new RowTransformerStub('3'),
                ),
            ],
            $transformers
        );
    }

    public function test_optimizing_only_entries_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizeTransformers(
                new EntryTransformerStub('1'),
                new EntryTransformerStub('2'),
                new EntryTransformerStub('3'),
            );

        $this->assertEquals(
            [
                BulkTransformer::rows(
                    new EntryTransformerStub('1'),
                    new EntryTransformerStub('2'),
                    new EntryTransformerStub('3'),
                ),
            ],
            $transformers
        );
    }

    public function test_optimizing_transformer_between_rows_and_entries_transformers() : void
    {
        $transformers = (new BulkTransformationOptimizer())
            ->optimizeTransformers(
                new RowTransformerStub('1'),
                new EntryTransformerStub('1'),
                new TransformerStub('1'),
                new EntryTransformerStub('2'),
                new RowTransformerStub('2'),
                new EntryTransformerStub('3'),
            );

        $this->assertEquals(
            [
                BulkTransformer::rows(new RowTransformerStub('1'))
                    ->addEntry(new EntryTransformerStub('1')),
                new TransformerStub('1'),
                BulkTransformer::entries(new EntryTransformerStub('2'))
                    ->addRow(new RowTransformerStub('2'))
                    ->addEntry(new EntryTransformerStub('3')),
            ],
            $transformers,
        );
    }
}
