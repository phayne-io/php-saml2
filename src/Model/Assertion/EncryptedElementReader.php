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

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use InvalidArgumentException;
use LogicException;
use Override;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\Context;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;

use function is_string;

/**
 * Class EncryptedElementReader
 *
 * @package Phayne\Saml\Model\Assertion
 */
class EncryptedElementReader extends EncryptedElement
{
    protected XMLSecEnc $xmlEnc;

    protected(set) XMLSecurityKey $symmetricKey;

    protected(set) XMLSecurityKey $symmetricKeyInfo;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        throw new LogicException('EncryptedElementReader can not be used for serialization');
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $list = $context->xpath->query('xenc:EncryptedData', $node);

        if ($list->length !== 1) {
            throw new SamlXmlException('More than one encrypted data element in <saml:EncryptedAssertion>');
        }

        /** @var DOMElement $encryptedData */
        $encryptedData = $list->item(0);
        $this->xmlEnc = new XMLSecEnc();
        $this->xmlEnc->setNode($encryptedData);
        $this->xmlEnc->type = $encryptedData->getAttribute('Type');

        $this->symmetricKey = $this->loadSymmetricKey();

        $this->symmetricKeyInfo = $this->loadSymmetricKeyInfo($this->symmetricKey);
    }

    public function decryptMulti(array $inputKeys): DOMElement
    {
        $lastException = null;

        foreach ($inputKeys as $key) {
            if ($key instanceof CredentialInterface) {
                $key = $key->privateKey;
            }

            if (false === ($key instanceof XMLSecurityKey)) {
                throw new InvalidArgumentException('Expected XMLSecurityKey');
            }

            try {
                return $this->decrypt($key);
            } catch (Exception $exception) {
                $lastException = $exception;
            }
        }

        if (null !== $lastException) {
            throw $lastException;
        }

        throw new SamlXmlException('No key provided for decryption');
    }

    public function decrypt(XMLSecurityKey $inputKey): DOMElement
    {
        $this->symmetricKey = $this->loadSymmetricKey();
        $this->symmetricKeyInfo = $this->loadSymmetricKeyInfo($this->symmetricKey);

        if ($this->symmetricKeyInfo->isEncrypted) {
            $this->decryptSymmetricKey($inputKey);
        } else {
            $this->symmetricKey = $inputKey;
        }

        return $this->buildXmlElement($this->decryptCypher());
    }

    protected function buildXmlElement(string $decrypted): DOMElement
    {
        $xml = sprintf(
            '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">%s</root>', //phpcs:ignore
            $decrypted
        );
        $newDoc = new DOMDocument();

        if (false === @$newDoc->loadXML($xml)) {
            throw new SamlXmlException('Failed to parse decrypted XML. Maybe wrong shared key was used?');
        }

        $decryptedElement = $newDoc->firstChild?->firstChild;

        if (null === $decryptedElement) {
            throw new SamlXmlException('Missing encrypted element.');
        }

        if (false === ($decryptedElement instanceof DOMElement)) {
            throw new SamlXmlException('Decrypted element was not actually a DOMElement.');
        }

        return $decryptedElement;
    }

    /**
     * @throws Exception
     */
    protected function decryptCypher(): string
    {
        $decrypted = $this->xmlEnc->decryptNode($this->symmetricKey, false);

        if (false === is_string($decrypted)) {
            throw new SamlXmlException('Decrypted data is not a string');
        }

        return $decrypted;
    }

    protected function decryptSymmetricKey(XMLSecurityKey $inputKey): void
    {
        $encKey = $this->symmetricKeyInfo->encryptedCtx;
        $this->symmetricKeyInfo->key = $inputKey->key;

        $keySize = $this->symmetricKey->getSymmetricKeySize();

        if (null === $keySize) {
            throw new SamlXmlException(
                sprintf("Unknown key size for encryption algorithm: '%s'", $this->symmetricKey->type)
            );
        }

        $key = $encKey->decryptKey($this->symmetricKeyInfo);

        if (false === is_string($key)) {
            throw new SamlXmlException('Decrypted data is not a string');
        }

        if (strlen($key) !== $keySize) {
            throw new SamlXmlException(sprintf(
                "Unexpected key size of '%s' bits for encryption algorithm '%s', expected '%s' bits size",
                strlen($key) * 8,
                $this->symmetricKey->type,
                $keySize
            ));
        }

        $this->symmetricKey->loadKey($key);
    }

    protected function loadSymmetricKey(): XMLSecurityKey
    {
        $symmetricKey = $this->xmlEnc->locateKey();

        if (null === $symmetricKey) {
            throw new SamlXmlException('Could not locate key algorithm in encrypted data');
        }

        return $symmetricKey;
    }

    protected function loadSymmetricKeyInfo(XMLSecurityKey $symmetricKey): XMLSecurityKey
    {
        $symmetricKeyInfo = $this->xmlEnc->locateKeyInfo($symmetricKey);

        if (null === $symmetricKeyInfo) {
            throw new SamlXmlException('Could not locate <dsig:KeyInfo> for the encrypted key');
        }

        return $symmetricKeyInfo;
    }
}
