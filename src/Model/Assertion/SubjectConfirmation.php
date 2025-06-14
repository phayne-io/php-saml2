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
 * Class SubjectConfirmation
 *
 * @package Phayne\Saml\Model\Assertion
 */
class SubjectConfirmation extends AbstractSamlModel
{
    protected(set) string $method;

    protected(set) ?NameID $nameID = null;

    protected(set) ?EncryptedElement $encryptedElement = null;

    protected(set) ?SubjectConfirmationData $subjectConfirmationData = null;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('SubjectConfirmation', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(['Method'], $element);
        $this->singleElementsToXml(
            ['NameID', 'EncryptedID', 'SubjectConfirmationData'],
            $element,
            $context
        );
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SubjectConfirmation', SamlConstant::NS_ASSERTION);

        $this->attributesFromXml($node, ['Method']);

        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
            'EncryptedID' => ['saml', 'LightSaml\Model\Assertion\EncryptedID'],
            'SubjectConfirmationData' => ['saml', SubjectConfirmationData::class],
        ]);
    }
}
