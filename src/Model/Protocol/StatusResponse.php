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
use Phayne\Saml\Model\Context;

/**
 * Class StatusResponse
 *
 * @package Phayne\Saml\Model\Protocol
 */
abstract class StatusResponse extends SamlMessage
{
    public ?string $inResponseTo = null;

    public ?Status $status = null;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        parent::serialize($parent, $context);
        $this->attributesToXml(['InResponseTo'], $parent);
        $this->singleElementsToXml(['Status'], $parent, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['InResponseTo']);
        $this->singleElementsFromXml($node, $context, [
            'Status' => ['samlp', Status::class],
        ]);
        parent::deserialize($node, $context);
    }
}
