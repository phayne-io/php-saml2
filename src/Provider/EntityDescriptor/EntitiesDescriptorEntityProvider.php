<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Provider\EntityDescriptor;

use Override;
use Phayne\Saml\Model\Metadata\EntityDescriptor;
use Phayne\Saml\Provider\EntitiesDescriptor\EntitiesDescriptorProviderInterface;

/**
 * Class EntitiesDescriptorEntityProvider
 *
 * @package Phayne\Saml\Provider\EntityDescriptor
 */
class EntitiesDescriptorEntityProvider implements EntityDescriptorProviderInterface
{
    private ?EntityDescriptor $entityDescriptor = null;

    public function __construct(
        private readonly EntitiesDescriptorProviderInterface $entitiesDescriptorProvider,
        private readonly string $entityId
    ) {
    }

    #[Override]
    public function get(): EntityDescriptor
    {
        if (null === $this->entityDescriptor) {
            $this->entityDescriptor = $this->entitiesDescriptorProvider->get()->entityById($this->entityId);
        }

        return $this->entityDescriptor;
    }
}
