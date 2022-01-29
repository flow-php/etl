<?php

declare(strict_types=1);

namespace Flow\ETL\Pipeline;

use Flow\ETL\ErrorHandler;
use Flow\ETL\ErrorHandler\ThrowError;
use Flow\ETL\Loader;
use Flow\ETL\Optimizer\Optimizer;
use Flow\ETL\Pipeline;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

final class SynchronousPipeline implements Pipeline
{
    /**
     * @var array<Loader|Transformer>
     */
    private array $elements;

    private ErrorHandler $errorHandler;

    private Optimizer $optimizer;

    public function __construct(Optimizer $optimizer = null)
    {
        $this->elements = [];
        $this->errorHandler = new ThrowError();
        $this->optimizer = $optimizer ?? new \Flow\ETL\Optimizer\BulkTransformationOptimizer();
    }

    public function clean() : Pipeline
    {
        $newPipeline = new self();
        $newPipeline->errorHandler = $this->errorHandler;

        return $newPipeline;
    }

    public function onError(ErrorHandler $errorHandler) : void
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function registerTransformer(Transformer ...$transformers) : void
    {
        $this->elements = \array_merge($this->elements, $this->optimizer->optimizeTransformers(...$transformers));
    }

    public function registerLoader(Loader ...$loaders) : void
    {
        $this->elements = \array_merge($this->elements, $loaders);
    }

    /**
     * @param \Generator<int, Rows, mixed, void> $generator
     *
     * @throws \Throwable
     *
     * @return \Generator<int, Rows, mixed, void>
     */
    public function process(\Generator $generator) : \Generator
    {
        $index = 0;

        while ($generator->valid()) {
            /** @var Rows $rows */
            $rows = $generator->current();
            $generator->next();

            if ($index === 0) {
                $rows = $rows->makeFirst();
            }

            if ($generator->valid() === false) {
                $rows = $rows->makeLast();
            }

            foreach ($this->elements as $element) {
                try {
                    if ($element instanceof Transformer) {
                        $rows = $element->transform($rows);
                    } else {
                        $element->load($rows);
                    }
                } catch (\Throwable $exception) {
                    if ($this->errorHandler->throw($exception, $rows)) {
                        throw $exception;
                    }

                    if ($this->errorHandler->skipRows($exception, $rows)) {
                        break;
                    }
                }
            }

            yield $rows;

            $index++;
        }
    }
}
