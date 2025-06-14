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
 * Class Organization
 *
 * @package Phayne\Saml\Model\Metadata
 */
class Organization extends AbstractSamlModel
{
    public string $lang = 'en-US';

    public string $organizationName;

    public string $organizationDisplayName;

    public string $organizationUrl;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('Organization', SamlConstant::NS_METADATA, $parent, $context);

        $elements = ['OrganizationName', 'OrganizationDisplayName', 'OrganizationURL'];
        $this->singleElementsToXml(
            $elements,
            $element,
            $context,
            SamlConstant::NS_METADATA
        );

        /** @var DOMNode $node */
        foreach ($element->childNodes as $node) {
            if ($node instanceof DOMElement && in_array($node->tagName, $elements, true)) {
                $node->setAttribute('xml:lang', $this->lang);
            }
        }
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Organization', SamlConstant::NS_METADATA);

        $this->singleElementsFromXml($node, $context, [
            'OrganizationName' => ['md', null],
            'OrganizationDisplayName' => ['md', null],
            'OrganizationURL' => ['md', null],
        ]);
    }
}
