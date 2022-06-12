<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Stream\Flysystem;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

final class AzureBlobStream extends FlysystemWrapper
{
    public const PROTOCOL = 'flow-azure-blob';

    public static function register() : void
    {
        if (!\in_array(self::PROTOCOL, \stream_get_wrappers(), true)) {
            \stream_wrapper_register(self::PROTOCOL, self::class);
        }
    }

    protected function filesystem() : Filesystem
    {
        if ($this->filesystem === null) {
            /**
             * @psalm-suppress PossiblyNullArgument
             * @psalm-suppress UndefinedThisPropertyFetch
             * @psalm-suppress MixedArgument
             */
            $contextOptions = \stream_context_get_options($this->context);

            /**
             * @psalm-suppress MixedArgument
             *
             * @var array{connection-string: string} $clientOptions
             */
            $clientOptions = \array_merge(
                ['connection-string' => ''],
                $contextOptions[self::PROTOCOL]['client'] ?? []
            );

            /**
             * @psalm-suppress PossiblyNullArrayAccess
             * @psalm-suppress PossiblyNullArgument
             */
            $this->filesystem = (new Filesystem(
                new AzureBlobStorageAdapter(
                    BlobRestProxy::createBlobService($clientOptions['connection-string']),
                    /** @phpstan-ignore-next-line */
                    $this->url['host']
                )
            ));
        }

        return $this->filesystem;
    }
}
