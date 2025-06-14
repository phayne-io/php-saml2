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
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class Subject
 *
 * @package Phayne\Saml\Model\Assertion
 */
class Subject extends AbstractSamlModel
{
    protected(set) ?NameID $nameID = null;

    protected array $subjectConfirmation = [];

    public function addSubjectConfirmation(SubjectConfirmation $subjectConfirmation): Subject
    {
        $this->subjectConfirmation[] = $subjectConfirmation;
        return $this;
    }

    public function subjectConfirmations(): array
    {
        return $this->subjectConfirmation;
    }

    public function firstSubjectConfirmation(): ?SubjectConfirmation
    {
        return array_find($this->subjectConfirmation, fn($item) => $item instanceof SubjectConfirmation);
    }

    public function bearerConfirmations(): array
    {
        return array_filter(
            $this->subjectConfirmation,
            fn(SubjectConfirmation $item)
                => SamlConstant::tryFrom($item->method) === SamlConstant::CONFIRMATION_METHOD_BEARER
        );
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('Subject', SamlConstant::NS_ASSERTION, $parent, $context);

        $this->singleElementsToXml(['NameID'], $result, $context);
        $this->manyElementsToXml($this->subjectConfirmations(), $result, $context, null);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Subject', SamlConstant::NS_ASSERTION);

        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
        ]);

        $this->manyElementsFromXml(
            $node,
            $context,
            'SubjectConfirmation',
            'saml',
            SubjectConfirmation::class,
            'addSubjectConfirmation'
        );
    }
}
