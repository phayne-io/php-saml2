<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\XmlDSig;

use DOMNode;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\KeyHelper;
use Phayne\Saml\Credential\SignatureAlgorithm;
use Phayne\Saml\Exception\SamlSecurityException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class AbstractSignatureReader
 *
 * @package Phayne\Saml\Model\XmlDSig
 */
abstract class AbstractSignatureReader extends Signature
{
    protected(set) ?XMLSecurityKey $key = null;

    protected SignatureAlgorithm $algorithm;

    abstract public function validate(XMLSecurityKey $key): bool;

    public function validateMulti(array $credentialCandidates): ?CredentialInterface
    {
        $lastException = null;

        /** @var CredentialInterface $credential */
        foreach ($credentialCandidates as $credential) {
            if (null === $credential->publicKey) {
                continue;
            }

            try {
                $result = $this->validate($credential->publicKey);

                return $result ? $credential : null;
            } catch (SamlSecurityException $e) {
                $lastException = $e;
            }
        }

        if ($lastException instanceof SamlSecurityException) {
            throw $lastException;
        } else {
            throw new SamlSecurityException('No public key available for signature verification');
        }
    }

    protected function castKeyIfNecessary(XMLSecurityKey $key): XMLSecurityKey
    {
        if (false === $this->algorithm->supported()) {
            throw new SamlSecurityException(
                sprintf('Unsupported signing algorithm: "%s"', $this->algorithm->value)
            );
        }

        if ($this->algorithm->value != $key->type) {
            $key = KeyHelper::castKey($key, $this->algorithm);
        }

        return $key;
    }
}
