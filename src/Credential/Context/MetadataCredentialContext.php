<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Credential\Context;

use Phayne\Saml\Model\Metadata\EntityDescriptor;
use Phayne\Saml\Model\Metadata\KeyDescriptor;
use Phayne\Saml\Model\Metadata\RoleDescriptor;

/**
 * Class MetadataCredentialContext
 *
 * @package Phayne\Saml\Credential\Context
 */
readonly class MetadataCredentialContext implements CredentialContextInterface
{
    public function __construct(
        public KeyDescriptor $keyDescriptor,
        public RoleDescriptor $roleDescriptor,
        public EntityDescriptor $entityDescriptor
    ) {
    }
}
