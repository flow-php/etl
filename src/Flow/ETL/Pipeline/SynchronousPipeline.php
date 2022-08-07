<?php

declare(strict_types=1);

namespace Flow\ETL\Pipeline;

use Flow\ETL\DSL\From;
use Flow\ETL\Extractor;
use Flow\ETL\FlowContext;
use Flow\ETL\Loader;
use Flow\ETL\Pipeline;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

final class SynchronousPipeline implements Pipeline
{
    private Extractor $extractor;

    private readonly Pipes $pipes;

    public function __construct()
    {
        $this->pipes = Pipes::empty();
        $this->extractor = From::rows(new Rows());
    }

    public function add(Loader|Transformer $pipe) : self
    {
        $this->pipes->add($pipe);

        return $this;
    }

    public function cleanCopy() : Pipeline
    {
        return new self();
    }

    /**
     * @psalm-suppress PossiblyNullOperand
     */
    public function process(FlowContext $context) : \Generator
    {
        $generator = $this->extractor->extract($context);
        $reductor = new Limiter($context->config->limit());

        while ($generator->valid()) {
            $rows = $reductor->limit($generator->current());
            $generator->next();

            if ($rows === null) {
                foreach ($this->pipes->all() as $pipe) {
                    if ($pipe instanceof Pipeline\Closure) {
                        $pipe->closure($reductor->latest(), $context);
                    }
                }

                break;
            }

            foreach ($this->pipes->all() as $pipe) {
                try {
                    if ($pipe instanceof Transformer) {
                        $rows = $reductor->limitTransformed($pipe->transform($rows, $context));
                    } elseif ($pipe instanceof Loader) {
                        $pipe->load($rows, $context);
                    }

                    if ($pipe instanceof Pipeline\Closure) {
                        if ($generator->valid() === false) {
                            $pipe->closure($rows, $context);
                        }
                    }
                } catch (\Throwable $exception) {
                    if ($context->errorHandler()->throw($exception, $rows)) {
                        throw $exception;
                    }

                    if ($context->errorHandler()->skipRows($exception, $rows)) {
                        break;
                    }
                }
            }

            yield $rows;
        }
    }

    public function source(Extractor $extractor) : self
    {
        $this->extractor = $extractor;

        return $this;
    }
}
