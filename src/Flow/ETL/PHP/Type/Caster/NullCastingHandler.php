<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Caster;

use Flow\ETL\PHP\Type\{Caster, Type};
use Flow\ETL\PHP\Type\Native\NullType;

final class NullCastingHandler implements CastingHandler
{
    /**
     * @param Type<null> $type
     */
    public function supports(Type $type) : bool
    {
        return $type instanceof NullType;
    }

    public function value(mixed $value, Type $type, Caster $caster, Options $options) : null
    {
        return null;
    }
}
