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

use Phayne\Saml\Provider\EntitiesDescriptor\FileEntitiesDescriptorProvider;

/**
 * Class FileEntityDescriptorProviderFactory
 *
 * @package Phayne\Saml\Provider\EntityDescriptor
 */
class FileEntityDescriptorProviderFactory
{
    public static function fromEntityDescriptorFile(string $filename): FileEntityDescriptorProvider
    {
        return new FileEntityDescriptorProvider($filename);
    }

    public static function fromEntitiesDescriptorFile(
        string $filename,
        string $entityId
    ): EntitiesDescriptorEntityProvider {
        return new EntitiesDescriptorEntityProvider(
            new FileEntitiesDescriptorProvider($filename),
            $entityId
        );
    }
}
