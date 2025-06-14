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

use DOMElement;
use DOMNode;
use LogicException;
use Override;
use Phayne\Saml\Credential\SignatureAlgorithm;
use Phayne\Saml\Exception\SamlSecurityException;
use Phayne\Saml\Model\Context;
use RobRichards\XMLSecLibs\XMLSecurityKey;

use function base64_decode;

/**
 * Class SignatureStringReader
 *
 * @package Phayne\Saml\Model\XmlDSig
 */
class SignatureStringReader extends AbstractSignatureReader
{
    public function __construct(
        protected(set) string $signature {
            get {
                return $this->signature;
            }
        },
        protected(set) SignatureAlgorithm $algorithm {
            get {
                return $this->algorithm;
            }
        },
        protected(set) string $data {
            get {
                return $this->data;
            }
        },
    ) {
    }

    #[Override]
    public function validate(XMLSecurityKey $key): bool
    {
        $key = $this->castKeyIfNecessary($key);
        $signature = base64_decode($this->signature, true);

        if (false === $key->verifySignature($this->data, $signature)) {
            throw new SamlSecurityException('Unable to validate signature');
        }

        return true;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        throw new LogicException('SignatureStringReader can not be serialized');
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        throw new LogicException('SignatureStringReader can not be deserialized');
    }
}
