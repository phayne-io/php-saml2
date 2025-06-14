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

use Phayne\Saml\Exception\SamlXmlException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

use function openssl_pkey_get_details;

/**
 * Class KeyHelper
 *
 * @package Phayne\Saml\Credential
 */
class KeyHelper
{
    public static function createPrivateKey(
        string $key,
        ?string $passphrase = null,
        $isFile = false,
        string $type = XMLSecurityKey::RSA_SHA1
    ): XMLSecurityKey {
        $privateKey = new XMLSecurityKey($type, ['type' => 'private']);
        $privateKey->passphrase = $passphrase ?? '';
        $privateKey->loadKey($key, $isFile);

        return $privateKey;
    }

    public static function createPublicKey(X509Certificate $certificate): XMLSecurityKey
    {
        $key = new XMLSecurityKey($certificate->signatureAlgorithm(), ['type' => 'public']);
        $key->loadKey($certificate->toPem(), false, true);
        return $key;
    }

    public static function castKey(XMLSecurityKey $key, SignatureAlgorithm $algorithm): XMLSecurityKey
    {
        if ($key->type === $algorithm->value) {
            return $key;
        }

        $keyInfo = openssl_pkey_get_details($key->key);

        if (false === $keyInfo) {
            throw new SamlXmlException('Unable to get key details from XMLSecurityKey.');
        }

        if (! isset($keyInfo['key'])) {
            throw new SamlXmlException('Missing key in public key details.');
        }

        $newKey = new XMLSecurityKey($algorithm->value, ['type' => 'public']);
        $newKey->loadKey($keyInfo['key']);

        return $newKey;
    }
}
