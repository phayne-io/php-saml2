<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Credential;

use InvalidArgumentException;
use Phayne\Saml\Utils\DataSet;
use RobRichards\XMLSecLibs\XMLSecurityKey;

use function chunk_split;
use function file_get_contents;
use function openssl_x509_parse;
use function openssl_x509_read;
use function preg_match;
use function preg_replace;

/**
 * Class X509Certificate
 *
 * @package Phayne\Saml\Credential
 */
class X509Certificate
{
    private(set) string $data {
        set(string $value) {
            $this->data = preg_replace('/\s+/', ' ', $value);
            $this->parse();
        }
    }

    protected DataSet $info;

    private SignatureAlgorithm $signatureAlgorithm;

    public static function fromFile(string $filename): X509Certificate
    {
        $self = new self();
        $self->loadFromFile($filename);
        return $self;
    }

    public static function fromData(string $data): X509Certificate
    {
        $self = new self();
        $self->loadPem($data);
        return $self;
    }

    private function __construct()
    {
    }

    public function info(): array
    {
        return $this->info->all();
    }

    public function name(): string
    {
        return $this->info?->get('name');
    }

    public function subject(): string
    {
        return $this->info?->get('subject');
    }

    public function issuer(): string
    {
        return $this->info->get('issuer');
    }

    public function validFromTimestamp(): int
    {
        return $this->info->get('validFrom_time_t');
    }

    public function validToTimestamp(): int
    {
        return $this->info->get('validTo_time_t');
    }

    public function fingerPrint(): string
    {
        return XMLSecurityKey::getRawThumbprint($this->toPem());
    }

    public function signatureAlgorithm(): string
    {
        return $this->signatureAlgorithm->value;
    }

    public function toPem(): string
    {
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split($this->data, 64) . "-----END CERTIFICATE-----\n";
    }

    private function loadPem(string $data): void
    {
        $pattern = '/^-----BEGIN CERTIFICATE-----([^-]*)^-----END CERTIFICATE-----/m';

        if (false === preg_match($pattern, $data, $matches)) {
            throw new InvalidArgumentException('Invalid PEM encoded certificate');
        }

        $this->data = $matches[1];
    }

    private function loadFromFile(string $filename): void
    {
        if (! is_file($filename)) {
            throw new InvalidArgumentException(sprintf("File not found '%s'", $filename));
        }
        $this->loadPem(file_get_contents($filename));
    }

    private function parse(): void
    {
        $res = openssl_x509_read($this->toPem());
        $this->info = new DataSet(openssl_x509_parse($res));
        $this->signatureAlgorithm = SignatureAlgorithm::fromX509($res);
    }
}
