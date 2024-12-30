<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\Pipeline;

use function Flow\ETL\DSL\ref;
use Flow\ETL\Pipeline\{Optimizer, SynchronousPipeline};
use Flow\ETL\Tests\FlowTestCase;
use Flow\ETL\Transformer\SelectEntriesTransformer;

final class OptimizerTest extends FlowTestCase
{
    public function test_adding_element_to_pipeline_when_no_optimization_is_applicable() : void
    {
        $pipeline = new SynchronousPipeline();

        $optimizedPipeline = (new Optimizer())->optimize(new SelectEntriesTransformer(ref('id')), $pipeline);

        self::assertCount(1, $optimizedPipeline->pipes()->all());
    }
}
