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

use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\Assertion\Assertion;
use Phayne\Saml\Model\Assertion\EncryptedAssertionReader;
use Phayne\Saml\Model\Assertion\EncryptedElement;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class Response
 *
 * @package Phayne\Saml\Model\Protocol
 */
class Response extends StatusResponse
{
    protected(set) array $assertions = [];

    public ?Assertion $firstAssertion {
        get {
            return ! empty($this->assertions)
                ? $this->assertions[0]
                : null;
        }
    }

    protected(set) array $encryptedAssertions = [];

    public ?EncryptedElement $firstEncryptedAssertion {
        get {
            return ! empty($this->encryptedAssertions)
                ? $this->encryptedAssertions[0]
                : null;
        }
    }

    public function bearerAssertions(): array
    {
        $assertions = [];

        /** @var Assertion $assertion */
        foreach ($this->assertions as $assertion) {
            if ($assertion->hasBearerSubject()) {
                $assertions[] = $assertion;
            }
        }

        return $assertions;
    }

    public function addAssertion(Assertion $assertion): Response
    {
        $this->assertions[] = $assertion;
        return $this;
    }

    public function addEncryptedAssertion(EncryptedElement $encryptedElement): Response
    {
        $this->encryptedAssertions[] = $encryptedElement;
        return $this;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('samlp:Response', SamlConstant::PROTOCOL_SAML2, $parent, $context);
        parent::serialize($parent, $context);
        $this->manyElementsToXml($this->assertions, $element, $context);
        $this->manyElementsToXml($this->encryptedAssertions, $element, $context);
        $this->singleElementsToXml(['Signature'], $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Response', SamlConstant::PROTOCOL_SAML2);
        parent::deserialize($node, $context);
        $this->assertions = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'Assertion',
            'saml',
            Assertion::class,
            'addAssertion'
        );
        $this->encryptedAssertions = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'EncryptedAssertion',
            'saml',
            EncryptedAssertionReader::class,
            'addEncryptedAssertion'
        );
    }
}
