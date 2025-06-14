<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Protocol;

use DateTime;
use DOMComment;
use DOMElement;
use DOMNode;
use LogicException;
use Override;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Assertion\Issuer;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\SamlElementInterface;
use Phayne\Saml\Model\XmlDSig\Signature;
use Phayne\Saml\Model\XmlDSig\SignatureXmlReader;
use Phayne\Saml\SamlConstant;

use function array_key_exists;
use function sprintf;

/**
 * Class SamlMessage
 *
 * @package Phayne\Saml\Model\Protocol
 */
abstract class SamlMessage extends AbstractSamlModel
{
    public string $id;

    public string $version = SamlConstant::VERSION_20->value;

    public int $issueInstant {
        set(int | string | DateTime $value) {
            $this->issueInstant = Helper::getTimestampFromValue($value);
        }
    }

    public ?string $destination = null;

    public ?Issuer $issuer = null;

    public ?string $consent = null;

    public ?Signature $signature = null;

    public ?string $relayState = null;

    public static function fromXML(
        string $xml,
        Context\DeserializationContext $context
    ): AuthnRequest | LogoutRequest | LogoutResponse | Response | SamlMessage {
        $context->document->loadXML($xml);
        $node = $context->document->firstChild;

        while ($node instanceof DOMComment) {
            $node = $node->nextSibling;
        }

        if (!$node instanceof DOMNode) {
            throw new SamlXmlException('Empty XML');
        }

        if (false === SamlConstant::is($node->namespaceURI, SamlConstant::PROTOCOL_SAML2)) {
            throw new SamlXmlException(sprintf(
                "Invalid namespace '%s' of the root XML element, expected '%s'",
                $context->document->namespaceURI,
                SamlConstant::PROTOCOL_SAML2->value
            ));
        }

        $map = [
            'AttributeQuery' => null,
            'AuthnRequest' => AuthnRequest::class,
            'LogoutResponse' => LogoutResponse::class,
            'LogoutRequest' => LogoutRequest::class,
            'Response' => Response::class,
            'ArtifactResponse' => null,
            'ArtifactResolve' => null,
        ];

        $rootElementName = $node->localName;

        if (array_key_exists($rootElementName, $map)) {
            if ($class = $map[$rootElementName]) {
                /** @var SamlElementInterface $result */
                $result = new $class();
            } else {
                throw new LogicException('Deserialization of %s root element is not implemented');
            }
        } else {
            throw new SamlXmlException(sprintf("Unknown SAML message '%s'", $rootElementName));
        }

        $result->deserialize($node, $context);

        return $result;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        if ($parent instanceof DOMElement) {
            $this->attributesToXml(['ID', 'Version', 'IssueInstant', 'Destination', 'Consent'], $parent);
            $this->singleElementsToXml(['Issuer'], $parent, $context);
        }
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        if ($node instanceof DOMElement) {
            $this->attributesFromXml($node, ['ID', 'Version', 'IssueInstant', 'Destination', 'Consent']);
            $this->singleElementsFromXml($node, $context, [
                'Issuer' => ['saml', Issuer::class],
                'Signature' => ['ds', SignatureXmlReader::class],
            ]);
        }
    }
}
