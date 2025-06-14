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
use Override;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\XmlDSig\Signature;
use Phayne\Saml\Model\XmlDSig\SignatureXmlReader;
use Phayne\Saml\SamlConstant;

use function array_any;
use function array_filter;
use function array_find;

/**
 * Class Assertion
 *
 * @package Phayne\Saml\Model\Assertion
 */
class Assertion extends AbstractSamlModel
{
    protected(set) ?string $id = null;

    protected(set) SamlConstant $version = SamlConstant::VERSION_20;

    protected(set) ?int $issueInstant = null;

    protected(set) ?Issuer $issuer = null;

    protected(set) ?Signature $signature = null;

    protected(set) ?Subject $subject = null;

    protected(set) ?Conditions $conditions = null;

    protected(set) array $items = [];

    public array $authnStatements {
        get {
            return array_filter($this->items, fn ($item) => $item instanceof AuthnStatement);
        }
    }

    protected array $attributesStatements {
        get {
            return array_filter($this->items, fn ($item) => $item instanceof AttributeStatement);
        }
    }

    public ?AuthnStatement $firstAuthnStatement {
        get {
            return array_find($this->items, fn ($item) => $item instanceof AuthnStatement);
        }
    }

    public ?AttributeStatement $firstAttributeStatement {
        get {
            return array_find($this->items, fn ($item) => $item instanceof AttributeStatement);
        }
    }

    public function equals(string $nameId, ?string $format): bool
    {
        if (null === $this->subject?->nameID) {
            return false;
        }

        if ($this->subject->nameID->value !== $nameId) {
            return false;
        }

        return $this->subject->nameID->value === $format;
    }

    public function hasSessionIndex(string $sessionIndex): bool
    {
        return array_any(
            $this->authnStatements,
            fn (AuthnStatement $authnStatement) => $authnStatement->sessionIndex === $sessionIndex
        );
    }

    public function hasAnySessionIndex(): bool
    {
        return array_any(
            $this->authnStatements,
            fn (AuthnStatement $authnStatement) => null !== $authnStatement->sessionIndex
        );
    }

    public function hasBearerSubject(): bool
    {
        return ! empty($this->authnStatements) &&
            null !== $this->subject &&
            ! empty($this->subject->bearerConfirmations());
    }

    public function prepareForXml(): void
    {
        if (null === $this->id) {
            $this->id = Helper::generateID();
        }

        if (null === $this->issueInstant) {
            $this->issueInstant = time();
        }
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $this->prepareForXml();
        $result = $this->createElement('Assertion', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(['ID', 'Version', 'IssueInstant'], $result);
        $this->singleElementsToXml(
            ['Issuer', 'Subject', 'Conditions'],
            $result,
            $context
        );

        foreach ($this->items as $item) {
            $item->serialize($result, $context);
        }

        // must be added at the end
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Assertion', SamlConstant::NS_ASSERTION);

        $this->attributesFromXml($node, ['ID', 'Version', 'IssueInstant']);

        $this->singleElementsFromXml($node, $context, [
            'Issuer' => ['saml', Issuer::class],
            'Subject' => ['saml', Subject::class],
            'Conditions' => ['saml', Conditions::class],
        ]);

        $this->manyElementsFromXml(
            $node,
            $context,
            'AuthnStatement',
            'saml',
            AuthnStatement::class,
            'addItem'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'AttributeStatement',
            'saml',
            AttributeStatement::class,
            'addItem'
        );

        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
    }
}
