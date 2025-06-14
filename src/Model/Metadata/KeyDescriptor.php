<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Metadata;

use DOMElement;
use DOMNode;
use InvalidArgumentException;
use Override;
use Phayne\Saml\Credential\X509Certificate;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class KeyDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
final class KeyDescriptor extends AbstractSamlModel
{
    public const string USE_SIGNING = 'signing';
    public const string USE_ENCRYPTION = 'encryption';

    protected(set) ?string $use = null {
        set(null|string $value) {
            $value = trim($value);
            if ($value !== self::USE_ENCRYPTION && $value !== self::USE_SIGNING) {
                throw new InvalidArgumentException(sprintf("Invalid use value '%s'", $value));
            }
            $this->use = $value;
        }
    }

    private(set) ?X509Certificate $certificate = null;

    public function __construct(?string $use = null, ?X509Certificate $certificate = null)
    {
        if ($use) {
            $this->use = $use;
        }

        $this->certificate = $certificate;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('KeyDescriptor', SamlConstant::NS_METADATA->value, $parent, $context);
        $this->attributesToXml(['use'], $element);
        $keyInfo = $this->createElement('ds:KeyInfo', SamlConstant::NS_XMLDSIG->value, $element, $context);
        $xData = $this->createElement('ds:X509Data', SamlConstant::NS_XMLDSIG->value, $keyInfo, $context);
        $xCert = $this->createElement('ds:X509Certificate', SamlConstant::NS_XMLDSIG->value, $xData, $context);
        $xCert->nodeValue = $this->certificate->data;
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'KeyDescriptor', SamlConstant::NS_METADATA->value);
        $this->attributesFromXml($node, ['use']);

        $list = $context->xpath->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $node);

        if (1 !== $list->length) {
            throw new SamlXmlException('Missing X509Certificate node');
        }

        $x509Certificate = $list->item(0);
        $certifiedData = trim($x509Certificate->textContent);

        if (empty($certifiedData)) {
            throw new SamlXmlException('Missing certificate data');
        }

        $this->certificate = X509Certificate::fromData($certifiedData);
    }
}
