<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model;

use BackedEnum;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use LogicException;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\Context\DeserializationContext;
use Phayne\Saml\Model\Context\SerializationContext;

use function is_string;
use function sprintf;
use function str_contains;
use function strpos;
use function substr;

/**
 * Class AbstractSamlModel
 *
 * @package Phayne\Saml\Model
 */
abstract class AbstractSamlModel implements SamlElementInterface
{
    protected function createElement(
        string $name,
        BackedEnum | string | null $namespace,
        DOMNode $parent,
        SerializationContext $context
    ): DOMElement|bool {
        if ($namespace instanceof BackedEnum) {
            $namespace = $namespace->value;
        }

        $element = $namespace
            ? $context->document->createElementNS($namespace, $name)
            : $context->document->createElement($name);
        $parent->appendChild($element);

        return $element;
    }

    private function oneElementToXml(
        string $name,
        DOMNode $parent,
        SerializationContext $context,
        BackedEnum | string | null $namespace = null
    ): void {
        $value = $this->retrievePropertyValue($name);

        if (null === $value) {
            return;
        }

        if ($namespace instanceof BackedEnum) {
            $namespace = $namespace->value;
        }

        if ($value instanceof SamlElementInterface) {
            $value->serialize($parent, $context);
        } elseif (is_string($value)) {
            $node = $namespace
                ? $context->document->createElementNS($namespace, $name, $value)
                : $context->document->createElement($name, $value);
            $parent->appendChild($node);
        } else {
            throw new LogicException(
                sprintf("Element '%s' must implement SamlElementInterface or be a string", $name)
            );
        }
    }

    protected function singleElementsToXml(
        array $names,
        DOMNode $parent,
        SerializationContext $context,
        BackedEnum | string | null $namespace = null
    ): void {
        foreach ($names as $name) {
            $this->oneElementToXml($name, $parent, $context, $namespace);
        }
    }

    protected function manyElementsToXml(
        ?array $value,
        DOMNode $node,
        SerializationContext $context,
        ?string $nodeName = null,
        BackedEnum | string | null $namespaceUri = null
    ): void {
        if (null === $value) {
            return;
        }

        if ($namespaceUri instanceof BackedEnum) {
            $namespaceUri = $namespaceUri->value;
        }

        foreach ($value as $element) {
            if ($element instanceof SamlElementInterface) {
                if (null !== $nodeName) {
                    throw new LogicException(
                        'nodeName should not be specified when serializing array of SamlElementInterface'
                    );
                }
                $element->serialize($node, $context);
            } elseif (null !== $nodeName) {
                $child = null !== $namespaceUri
                    ? $context->document->createElementNS($namespaceUri, $nodeName, (string)$element)
                    : $context->document->createElement($nodeName, (string)$element);
                $node->appendChild($child);
            } else {
                throw new LogicException(
                    'Can handle only array of AbstractSamlModel or strings with nodeName parameter specified'
                );
            }
        }
    }

    protected function manyElementsFromXml(
        DOMElement $node,
        DeserializationContext $context,
        string $nodeName,
        ?string $namespacePrefix,
        ?string $class,
        string $methodName
    ): void {
        $query = $namespacePrefix ? sprintf('%s:%s', $namespacePrefix, $nodeName) : $nodeName;

        foreach ($context->xpath->query($query, $node) as $xml) {
            if (null !== $class) {
                $object = new $class();
                if (false === $object instanceof SamlElementInterface) {
                    throw new LogicException(
                        sprintf("Node '%s' class '%s' must implement SamlElementInterface", $nodeName, $class)
                    );
                }
                $object->deserialize($xml, $context);
            } else {
                $object = $xml->textContent;
            }
            $this->{$methodName}($object);
        }
    }

    protected function singleAttributeToXml(string $name, DOMElement $element): bool
    {
        $value = $this->retrievePropertyValue($name);

        if (null !== $value && $value !== '') {
            if (is_bool($value)) {
                $element->setAttribute($name, $value ? 'true' : 'false');
            } else {
                $element->setAttribute($name, $value);
            }
            return true;
        }

        return false;
    }

    protected function attributesToXml(array $names, DOMElement $element): void
    {
        foreach ($names as $name) {
            $this->singleAttributeToXml($name, $element);
        }
    }

    protected function checkXmlNodeName(
        DOMNode &$node,
        string $expectedName,
        BackedEnum | string $expectedNamespaceUri
    ): void {
        if ($node instanceof DOMDocument) {
            $node = $node->firstChild;
        }

        if ($expectedNamespaceUri instanceof BackedEnum) {
            $expectedNamespaceUri = $expectedNamespaceUri->value;
        }

        while ($node instanceof DOMComment) {
            $node = $node->nextSibling;
        }

        if (false === $node instanceof DOMNode) {
            throw new SamlXmlException(sprintf(
                "Unable to find expected '%s' xml node and '%s' namespace",
                $expectedName,
                $expectedNamespaceUri
            ));
        } elseif ($node->localName !== $expectedName || $node->namespaceURI !== $expectedNamespaceUri) {
            throw new SamlXmlException(sprintf(
                "Expected '%s' xml node and '%s' namespace but got node '%s' and namespace '%s'",
                $expectedName,
                $expectedNamespaceUri,
                $node->localName,
                $node->namespaceURI
            ));
        }
    }

    protected function singleAttributeFromXml(DOMElement $node, string $attributeName): void
    {
        $value = $node->getAttribute($attributeName);

        if ('' !== $value) {
            $property = lcfirst($attributeName);

            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }
    }

    protected function oneElementFromXml(
        DOMElement $node,
        DeserializationContext $context,
        string $elementName,
        string $class,
        string $namespacePrefix
    ): void {
        $query = $namespacePrefix
            ? sprintf('./%s:%s', $namespacePrefix, $elementName)
            : sprintf('./%s', $elementName);
        $arr = $context->xpath->query($query, $node);
        $value = $arr->length > 0 ? $arr->item(0) : null;

        if ($value) {
            $property = lcfirst($elementName);

            if (! property_exists($this, $property)) {
                throw new LogicException(sprintf(
                    "Unable to set property for element '%s' in class '%s'",
                    $elementName,
                    static::class
                ));
            }

            if ($class) {
                /** @var AbstractSamlModel $object */
                $object = new $class();

                if (! $object instanceof SamlElementInterface) {
                    throw new LogicException(sprintf(
                        "Specified class '%s' for element '%s' must implement SamlElementInterface",
                        $class,
                        $elementName
                    ));
                }

                $object->deserialize($value, $context);
            } else {
                $object = $value->textContent;
            }

            $this->{$property} = $object;
        }
    }

    protected function singleElementsFromXml(DOMElement $node, DeserializationContext $context, array $options): void
    {
        foreach ($options as $elementName => $info) {
            $this->oneElementFromXml($node, $context, $elementName, $info[1], $info[0]);
        }
    }

    protected function attributesFromXml(DOMElement $node, array $attributeNames): void
    {
        foreach ($attributeNames as $attributeName) {
            $this->singleAttributeFromXml($node, $attributeName);
        }
    }

    private function retrievePropertyValue(string $name): mixed
    {
        if (str_contains($name, ':')) {
            $name = substr($name, strpos($name, ':') + 1);
        }

        $property = lcfirst($name);

        if (property_exists($this, $property)) {
            return $this->{$property};
        } elseif (method_exists($this, $property)) {
            return $this->{$property}();
        } else {
            throw new LogicException(
                sprintf("Unable to retrieve property or method method for '%s' on '%s'.", $name, static::class)
            );
        }
    }
}
