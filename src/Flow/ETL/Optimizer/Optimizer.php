<?php

declare(strict_types=1);

namespace Flow\ETL\Optimizer;

use Flow\ETL\Loader;
use Flow\ETL\Transformer;

interface Optimizer
{
    /**
     * @param array<Transformer|Loader> elements
     *
     * @return array<Transformer|Loader>
     */
    public function optimizePipeline(array $elements) : array;
}
