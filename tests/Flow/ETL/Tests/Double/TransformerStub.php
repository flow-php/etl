<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Double;

use Flow\ETL\Rows;
use Flow\ETL\Transformer;

final class TransformerStub implements Transformer
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function transform(Rows $rows) : Rows
    {
        return $rows;
    }
}
