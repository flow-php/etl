<?php

declare(strict_types=1);

namespace Flow\ETL\Optimizer;

use Flow\ETL\Loader;
use Flow\ETL\Transformer;

interface Optimizer
{
    /**
     * @param array<Loader|Transformer> $elements
     *
     * @return array<Loader|Transformer>
     */
    public function optimizePipeline(array $elements) : array;
}
