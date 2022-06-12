<?php

declare(strict_types=1);

namespace Flow\ETL\DSL;

use Aws\S3\S3Client;
use Flow\ETL\Adapter\Stream\Flysystem\AwsS3Stream;
use Flow\ETL\Adapter\Stream\Flysystem\AzureBlobStream;
use Flow\ETL\Dependency;
use Flow\ETL\Exception\InvalidArgumentException;
use Flow\ETL\Exception\MissingDependencyException;
use Flow\ETL\Stream\LocalFile;
use Flow\ETL\Stream\RemoteFile;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemReader;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class Stream
{
    /**
     * @param string $bucket
     * @param string $path
     * @param string $file_extension
     * @param array<RemoteFile> $client_options
     *
     * @throws \League\Flysystem\FilesystemException
     * @throws MissingDependencyException
     * @throws InvalidArgumentException
     *
     * @return array<RemoteFile>
     */
    final public static function aws_s3_directory(string $bucket, string $path, string $file_extension, array $client_options) : array
    {
        Dependency::assertClassExists('League\Flysystem\Filesystem', 'League Flysystem', 'league/flysystem');
        Dependency::assertClassExists('League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter', 'Flysystem Azure Blob Adapter', 'league/flysystem-azure-blob-storage');

        AwsS3Stream::register();

        $fs = (new Filesystem(new AwsS3V3Adapter(new S3Client($client_options), $bucket)));

        $filter = static function (FileAttributes $file) use ($file_extension) : bool {
            if ($file->type() === 'file') {
                /**
                 * @psalm-suppress PossiblyUndefinedArrayOffset
                 * @phpstan-ignore-next-line
                 */
                return \pathinfo($file->path())['extension'] === $file_extension;
            }

            return false;
        };

        $files = [];

        /** @var FileAttributes $file */
        foreach ($fs->listContents($path, FilesystemReader::LIST_DEEP)->filter($filter) as $file) {
            $files[] = new RemoteFile(
                AwsS3Stream::PROTOCOL . '://' . \ltrim($bucket, '/') . '/' . $file->path(),
                [
                    'client' => $client_options,
                ]
            );
        }

        return $files;
    }

    /**
     * @param string $path
     * @param array<string, mixed> $client_options
     *
     * @throws MissingDependencyException
     * @throws InvalidArgumentException
     *
     * @return RemoteFile
     */
    final public static function aws_s3_file(string $bucket, string $path, array $client_options) : RemoteFile
    {
        Dependency::assertClassExists('League\Flysystem\Filesystem', 'League Flysystem', 'league/flysystem');
        Dependency::assertClassExists('League\Flysystem\AwsS3V3\AwsS3V3Adapter', 'Flysystem AWS S3 Adapter', 'league/flysystem-aws-s3-v3');

        AwsS3Stream::register();

        return new RemoteFile(
            AwsS3Stream::PROTOCOL . '://' . \trim($bucket, '/') . '/' . \ltrim($path, '/'),
            [
                'client' => $client_options,
            ]
        );
    }

    /**
     * @param string $container
     * @param string $path
     * @param string $file_extension
     * @param string $connection_string
     *
     * @throws \League\Flysystem\FilesystemException
     * @throws MissingDependencyException
     * @throws InvalidArgumentException
     *
     * @return array<RemoteFile>
     */
    final public static function azure_blob_directory(string $container, string $path, string $file_extension, string $connection_string) : array
    {
        Dependency::assertClassExists('League\Flysystem\Filesystem', 'League Flysystem', 'league/flysystem');
        Dependency::assertClassExists('League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter', 'Flysystem Azure Blob Adapter', 'league/flysystem-azure-blob-storage');

        AzureBlobStream::register();

        $fs = (new Filesystem(
            new AzureBlobStorageAdapter(
                BlobRestProxy::createBlobService($connection_string),
                $container
            )
        ));

        $filter = static function (FileAttributes $file) use ($file_extension) : bool {
            if ($file->type() === 'file') {
                /**
                 * @psalm-suppress PossiblyUndefinedArrayOffset
                 * @phpstan-ignore-next-line
                 */
                return \pathinfo($file->path())['extension'] === $file_extension;
            }

            return false;
        };

        $files = [];

        /** @var FileAttributes $file */
        foreach ($fs->listContents($path, FilesystemReader::LIST_DEEP)->filter($filter) as $file) {
            $files[] = new RemoteFile(
                AzureBlobStream::PROTOCOL . '://' . \ltrim($container, '/') . '/' . $file->path(),
                [
                    'client' => $connection_string,
                ]
            );
        }

        return $files;
    }

    /**
     * @throws MissingDependencyException
     * @throws InvalidArgumentException
     */
    final public static function azure_blob_file(string $container, string $path, string $connection_string) : RemoteFile
    {
        Dependency::assertClassExists('League\Flysystem\Filesystem', 'League Flysystem', 'league/flysystem');
        Dependency::assertClassExists('League\Flysystem\AwsS3V3\AwsS3V3Adapter', 'Flysystem AWS S3 Adapter', 'league/flysystem-aws-s3-v3');

        AzureBlobStream::register();

        return new RemoteFile(
            AzureBlobStream::PROTOCOL . '://' . \trim($container, '/') . '/' . \ltrim($path, '/'),
            [
                'client' => [
                    'connection-string' => $connection_string,
                ],
            ]
        );
    }

    /**
     * @param string $path
     *
     * @return array<LocalFile>
     */
    final public static function local_directory(string $path, string $file_extension) : array
    {
        $directoryIterator = new \RecursiveDirectoryIterator($path);
        $directoryIterator->setFlags(\RecursiveDirectoryIterator::SKIP_DOTS);

        $regexIterator = new \RegexIterator(
            new \RecursiveIteratorIterator($directoryIterator),
            '/^.+\.' . $file_extension . '$/i',
            \RegexIterator::GET_MATCH
        );

        $files = [];
        /** @var array<string> $filePath */
        foreach ($regexIterator as $filePath) {
            /** @phpstan-ignore-next-line */
            $files[] = new LocalFile(\current($filePath));
        }

        return $files;
    }

    /**
     * @param string $path
     *
     * @return LocalFile
     */
    final public static function local_file(string $path) : LocalFile
    {
        return new LocalFile($path);
    }
}
