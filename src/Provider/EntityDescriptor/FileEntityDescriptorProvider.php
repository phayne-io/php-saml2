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
use Phayne\Saml\Model\Context\DeserializationContext;
use Phayne\Saml\Model\Metadata\EntityDescriptor;

/**
 * Class FileEntityDescriptorProvider
 *
 * @package Phayne\Saml\Provider\EntityDescriptor
 */
class FileEntityDescriptorProvider implements EntityDescriptorProviderInterface
{
    private ?EntityDescriptor $entityDescriptor = null;

    public function __construct(private readonly string $filename)
    {
    }

    #[Override]
    public function get(): EntityDescriptor
    {
        if (null === $this->entityDescriptor) {
            $this->entityDescriptor = new EntityDescriptor();
            $deserializationContext = new DeserializationContext();
            $deserializationContext->document->load($this->filename);
            $this->entityDescriptor->deserialize(
                $deserializationContext->document->firstChild,
                $deserializationContext
            );
        }

        return $this->entityDescriptor;
    }
}
