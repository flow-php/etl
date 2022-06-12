<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Stream\Flysystem;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

final class AwsS3Stream extends FlysystemWrapper
{
    public const PROTOCOL = 'flow-aws-s3';

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
             * @var array{credentials: array{key: string, secret: string}, region: string, version: string} $clientOptions
             */
            $clientOptions = \array_merge(
                [
                    'credentials' => [
                        'key'    => '',
                        'secret' => '',
                    ],
                    'region' => '',
                    'version' => 'latest',
                ],
                $contextOptions[self::PROTOCOL]['client'] ?? []
            );

            /**
             * @psalm-suppress PossiblyNullArrayAccess
             * @psalm-suppress PossiblyNullArgument
             * @phpstan-ignore-next-line
             */
            $this->filesystem = (new Filesystem(new AwsS3V3Adapter(new S3Client($clientOptions), $this->url['host'])));
        }

        return $this->filesystem;
    }
}
