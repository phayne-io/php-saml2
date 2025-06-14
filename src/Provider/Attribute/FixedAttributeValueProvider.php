<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Provider\Attribute;

use Override;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Model\Assertion\Attribute;

/**
 * Class FixedAttributeValueProvider
 *
 * @package Phayne\Saml\Provider\Attribute
 */
class FixedAttributeValueProvider implements AttributeValueProviderInterface
{
    protected array $attributes = [];

    public function add(Attribute $attribute): FixedAttributeValueProvider
    {
        $this->attributes[] = $attribute;
        return $this;
    }

    #[Override]
    public function values(AssertionContext $context): array
    {
        return $this->attributes;
    }
}
