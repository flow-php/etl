<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Loader;

use function Flow\ETL\DSL\to_transformation;
use Flow\ETL\{Config, FlowContext, Loader, Rows, Tests\FlowTestCase, Transformer};

final class TransformerLoaderTest extends FlowTestCase
{
    public function test_transformer_loader() : void
    {
        $transformerMock = $this->createMock(Transformer::class);
        $transformerMock->expects(self::once())
            ->method('transform')
            ->willReturn(new Rows());

        $loaderMock = $this->createMock(Loader::class);
        $loaderMock->expects(self::once())
            ->method('load');

        $transformer = to_transformation(
            $transformerMock,
            $loaderMock
        );

        $transformer->load(new Rows(), new FlowContext(Config::default()));
    }
}
