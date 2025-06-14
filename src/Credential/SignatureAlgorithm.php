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

use OpenSSLCertificate;
use Phayne\Saml\Exception\SamlSecurityException;
use Phayne\Saml\SamlConstant;
use RobRichards\XMLSecLibs\XMLSecurityKey;

use function openssl_x509_export;
use function openssl_x509_parse;

/**
 * Enum SignatureAlgorithm
 *
 * @package Phayne\Saml\Credential
 */
enum SignatureAlgorithm: string
{
    case RSA_SHA1 = XMLSecurityKey::RSA_SHA1;
    case RSA_SHA256 = XMLSecurityKey::RSA_SHA256;
    case RSA_SHA384 = XMLSecurityKey::RSA_SHA384;
    case RSA_SHA512 = XMLSecurityKey::RSA_SHA512;
    case DIGEST_MD5 = SamlConstant::XMLDSIG_DIGEST_MD5->value;

    public static function fromX509(OpenSSLCertificate $certificate): self
    {
        $info = openssl_x509_parse($certificate);
        $knownSignatureTypes = [
            'RSA-SHA1' => self::RSA_SHA1,
            'RSA-SHA256' => self::RSA_SHA256,
            'RSA-SHA384' => self::RSA_SHA384,
            'RSA-SHA512' => self::RSA_SHA512,
        ];

        $match = $knownSignatureTypes[$info['signatureTypeSN'] ?? ''] ?? null;

        if (null === $match) {
            openssl_x509_export($certificate, $out, false);

            if (preg_match('/^\s+Signature Algorithm:\s*(.*)\s*$/m', $out, $matches)) {
                return match ($matches[1]) {
                    'sha1WithRSAEncryption', 'sha1WithRSA' => self::RSA_SHA1,
                    'sha256WithRSAEncryption', 'sha256WithRSA' => self::RSA_SHA256,
                    'sha384WithRSAEncryption', 'sha384WithRSA' => self::RSA_SHA384,
                    'sha512WithRSAEncryption', 'sha512WithRSA' => self::RSA_SHA512,
                    'md5WithRSAEncryption', 'md5WithRSA' => self::DIGEST_MD5,
                    default => throw new SamlSecurityException('Unknown signature algorithm: ' . $matches[1]),
                };
            }
        } else {
            return $match;
        }
    }

    public function supported(): bool
    {
        return static::isSupported($this);
    }

    public static function isSupported(self $value): bool
    {
        return match($value) {
            self::RSA_SHA1, self::RSA_SHA256, self::RSA_SHA384, self::RSA_SHA512 => true,
            default => false,
        };
    }
}
