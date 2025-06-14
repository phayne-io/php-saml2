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

use DOMComment;
use DOMNode;
use LogicException;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context\DeserializationContext;
use Phayne\Saml\SamlConstant;

use function array_key_exists;
use function file_get_contents;
use function sprintf;

/**
 * Class Metadata
 *
 * @package Phayne\Saml\Model\Metadata
 */
abstract class Metadata extends AbstractSamlModel
{
    public static function fromFile(string $path): EntitiesDescriptor|EntityDescriptor
    {
        return self::fromXml(@file_get_contents($path) ?? '', new DeserializationContext());
    }

    public static function fromXml(string $xml, DeserializationContext $context): EntitiesDescriptor|EntityDescriptor
    {
        $context->document->loadXML($xml);
        $node = $context->document->firstChild;

        while ($node && $node instanceof DOMComment) {
            $node = $node->nextSibling;
        }

        if (false === ($node instanceof DOMNode)) {
            throw new SamlXmlException('Empty XML');
        }

        if (false === SamlConstant::is($node->namespaceURI, SamlConstant::NS_METADATA)) {
            throw new SamlXmlException(sprintf(
                "Invalid namespace '%s' of the root XML element, expected '%s'",
                $node->namespaceURI,
                SamlConstant::NS_METADATA->value
            ));
        }

        $map = [
            'EntityDescriptor' => EntityDescriptor::class,
            'EntitiesDescriptor' => EntitiesDescriptor::class,
        ];

        $rootElementName = $node->localName;

        if (array_key_exists($rootElementName, $map)) {
            $class = $map[$rootElementName];
            if ($class !== '0') {
                /** @var EntitiesDescriptor|EntityDescriptor $result */
                $result = new $class();
            } else {
                throw new LogicException('Deserialization of %s root element is not implemented');
            }
        } else {
            throw new SamlXmlException(sprintf("Unknown SAML metadata '%s'", $rootElementName));
        }

        $result->deserialize($node, $context);

        return $result;
    }
}
