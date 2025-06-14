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

/**
 * Class Endpoint
 *
 * @package Phayne\Saml\Model\Metadata
 */
class Endpoint extends AbstractSamlModel
{
    public ?string $responseLocation = null;

    public function __construct(protected(set) ?string $location = null, public ?string $binding = null)
    {
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $this->attributesToXml(['Binding', 'Location', 'ResponseLocation'], $parent);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['Binding', 'Location', 'ResponseLocation']);
    }
}
