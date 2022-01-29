<?php

declare(strict_types=1);

namespace Flow\ETL\Optimizer;

use Flow\ETL\Transformer;

interface Optimizer
{
    /**
     * @param Transformer ...$transformers
     *
     * @return array<Transformer>
     */
    public function optimizeTransformers(Transformer ...$transformers) : array;
}
