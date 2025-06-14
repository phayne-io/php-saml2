<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Assertion;

use DOMElement;
use DOMNode;
use LogicException;
use Override;
use Phayne\Saml\Exception\SamlException;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\Context\SerializationContext;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class EncryptedElementWriter
 *
 * @package Phayne\Saml\Model\Assertion
 */
abstract class EncryptedElementWriter extends EncryptedElement
{
    protected ?DOMElement $encryptedElement = null;

    public function __construct(
        protected string $blockEncryptionAlgorithm = XMLSecurityKey::AES128_CBC,
        protected string $keyTransportEncryption = XMLSecurityKey::RSA_1_5,
    ) {
    }

    public function encrypt(AbstractSamlModel $object, XMLSecurityKey $key): Context\SerializationContext
    {
        $oldKey = $key;
        $key = new XMLSecurityKey($this->keyTransportEncryption, ['type' => 'public']);
        $key->loadKey($oldKey->key);

        $serializationContext = new SerializationContext();
        $object->serialize($serializationContext->document, $serializationContext);

        $enc = new XMLSecEnc();
        $enc->setNode($serializationContext->document->firstChild);
        $enc->type = XMLSecEnc::Element;

        switch ($key->type) {
            case XMLSecurityKey::TRIPLEDES_CBC:
            case XMLSecurityKey::AES128_CBC:
            case XMLSecurityKey::AES192_CBC:
            case XMLSecurityKey::AES256_CBC:
                $symmetricKey = $key;
                break;
            case XMLSecurityKey::RSA_1_5:
            case XMLSecurityKey::RSA_SHA1:
            case XMLSecurityKey::RSA_SHA256:
            case XMLSecurityKey::RSA_SHA384:
            case XMLSecurityKey::RSA_SHA512:
            case XMLSecurityKey::RSA_OAEP_MGF1P:
                $symmetricKey = new XMLSecurityKey($this->blockEncryptionAlgorithm);
                $symmetricKey->generateSessionKey();
                $enc->encryptKey($key, $symmetricKey);
                break;
            default:
                throw new SamlException(sprintf(
                    'Unknown key type for encryption: "%s"',
                    $key->type
                ));
        }

        $this->encryptedElement = $enc->encryptNode($symmetricKey);

        return $serializationContext;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        if (null === $this->encryptedElement) {
            throw new SamlException('Encrypted element missing');
        }

        $root = $this->createRootElement($parent, $context);
        $root->appendChild($context->document->importNode($this->encryptedElement, true));
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        throw new LogicException('EncryptedElementWriter can not be used for deserialization');
    }

    abstract protected function createRootElement(DOMNode $parent, Context\SerializationContext $context): DOMElement;
}
