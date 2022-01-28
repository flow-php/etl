<?php

declare(strict_types=1);

namespace Flow\ETL;

interface Pipeline
{
    /**
     * Create clean instance of pipeline, without any transformers/loaders registered.
     */
    public function clean() : self;

    public function registerTransformer(Transformer ...$transformers) : void;

    public function registerLoader(Loader ...$loaders) : void;

    /**
     * @param \Generator<int, Rows, mixed, void> $generator
     *
     * @return \Generator<int, Rows, mixed, void>
     */
    public function process(\Generator $generator) : \Generator;

    public function onError(ErrorHandler $errorHandler) : void;
}
