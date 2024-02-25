<?php

declare(strict_types=1);

namespace StripeJS;

class FileUploadTest extends TestCase
{
    public function testCreateFile(): void
    {
        $fp = fopen(__DIR__.'/../data/test.png', 'r');
        self::authorizeFromEnv();
        $file = FileUpload::create(
            [
                'purpose' => 'dispute_evidence',
                'file' => $fp,
            ]
        );
        fclose($fp);
        static::assertSame(95, $file->size);
        static::assertSame('png', $file->type);
    }

    public function testCreateAndRetrieveCurlFile(): void
    {
        if (!class_exists('\CurlFile', false)) {
            // Older PHP versions don't support this
            return;
        }

        $curlFile = new \CURLFile(__DIR__.'/../data/test.png');
        self::authorizeFromEnv();
        $file = FileUpload::create(
            [
                'purpose' => 'dispute_evidence',
                'file' => $curlFile,
            ]
        );
        static::assertSame(95, $file->size);
        static::assertSame('png', $file->type);

        // Just check that we don't get exceptions
        $file = FileUpload::retrieve($file->id);
        $file->refresh();
    }
}
