<?php

declare(strict_types=1);

namespace Flow\ETL\Pipeline;

use Flow\ETL\ErrorHandler;
use Flow\ETL\ErrorHandler\ThrowError;
use Flow\ETL\Loader;
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

    public function __construct()
    {
        $this->elements = [];
        $this->errorHandler = new ThrowError();
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
        $bulkRead = true;

        foreach ($transformers as $transformer) {
            if (!$transformer instanceof Transformer\RowTransformer) {
                $bulkRead = false;

                break;
            }
        }

        $this->elements = ($bulkRead === true)
            /** @phpstan-ignore-next-line  */
            ? \array_merge($this->elements, [new Transformer\BulkTransformer(...$transformers)])
            : \array_merge($this->elements, $transformers);
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
