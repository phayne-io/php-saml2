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

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use LogicException;
use Override;
use Phayne\Saml\Credential\SignatureAlgorithm;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class SignatureXmlReader
 *
 * @package Phayne\Saml\Model\XmlDSig
 */
class SignatureXmlReader extends AbstractSignatureReader
{
    protected array $certificates = [];

    protected(set) SignatureAlgorithm $algorithm {
        get {
            $xpath = new DOMXPath(
                $this->signature->sigNode instanceof DOMDocument
                    ? $this->signature->sigNode
                    : $this->signature->sigNode->ownerDocument
            );
            $xpath->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);
            $list = $xpath->query('//ds:SignedInfo/ds:SignatureMethod', $this->signature->sigNode);

            if ($list->length === 0) {
                throw new SamlXmlException('Missing SignatureMethod element');
            }

            /** @var DOMElement $sigMethod */
            $sigMethod = $list->item(0);

            if (!$sigMethod->hasAttribute('Algorithm')) {
                throw new SamlXmlException('Missing Algorithm-attribute on SignatureMethod element.');
            }

            $this->algorithm = SignatureAlgorithm::tryFrom($sigMethod->getAttribute('Algorithm'));
        }
    }

    protected(set) ?XMLSecurityDSig $signature = null;

    #[Override]
    public function validate(XMLSecurityKey $key): bool
    {
        if (null === $this->signature) {
            return false;
        }

        try {
            $this->signature->validateReference();
        } catch (Exception $exception) {
            throw new SamlXmlException('Digest validation failed', $exception->getCode(), $exception);
        }

        $key = $this->castKeyIfNecessary($key);

        if (false === (bool)$this->signature->verify($key)) {
            throw new SamlXmlException('Unable to verify signature');
        }

        return true;
    }

    #[Override]
    public function serialize(DOMNode $parent, Context\SerializationContext $context): void
    {
        throw new LogicException('SignatureXmlReader can not be serialized');
    }

    #[Override]
    public function deserialize(DOMNode $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Signature', SamlConstant::NS_XMLDSIG->value);

        $this->signature = new XMLSecurityDSig();
        $this->signature->idKeys[] = $this->idName();
        $this->signature->sigNode = $node;
        $this->signature->canonicalizeSignedInfo();

        $this->key = null;
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'public']);
        XMLSecEnc::staticLocateKeyInfo($key, $node);

        if ($key->name || $key->key) {
            $this->key = $key;
        }

        $this->certificates = [];
        $list = $context->xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $node);

        foreach ($list as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(["\r", "\n", " "], '', $certData);
            $this->certificates[] = $certData;
        }
    }
}
