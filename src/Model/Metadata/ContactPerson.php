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
use Override;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class ContactPerson
 *
 * @package Phayne\Saml\Model\Metadata
 */
class ContactPerson extends AbstractSamlModel
{
    public const string TYPE_TECHNICAL = 'technical';
    public const string TYPE_SUPPORT = 'support';
    public const string TYPE_ADMINISTRATIVE = 'administrative';
    public const string TYPE_BILLING = 'billing';
    public const string TYPE_OTHER = 'other';

    public string $contactType;

    public ?string $company = null;

    public ?string $givenName = null;

    public ?string $surname = null;

    public ?string $emailAddress = null;

    public ?string $telephoneNumber = null;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('ContactPerson', SamlConstant::NS_METADATA, $parent, $context);

        $this->attributesToXml(['contactType'], $result);

        $this->singleElementsToXml(
            ['Company', 'GivenName', 'SurName', 'EmailAddress', 'TelephoneNumber'],
            $result,
            $context,
            SamlConstant::NS_METADATA
        );
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'ContactPerson', SamlConstant::NS_METADATA);

        $this->attributesFromXml($node, ['contactType']);

        $this->singleElementsFromXml($node, $context, [
            'Company' => ['md', null],
            'GivenName' => ['md', null],
            'SurName' => ['md', null],
            'EmailAddress' => ['md', null],
            'TelephoneNumber' => ['md', null],
        ]);
    }
}
