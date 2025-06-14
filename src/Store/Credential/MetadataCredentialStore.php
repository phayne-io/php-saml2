<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Credential;

use Override;
use Phayne\Saml\Credential\Context\CredentialContextSet;
use Phayne\Saml\Credential\Context\MetadataCredentialContext;
use Phayne\Saml\Credential\X509Credential;
use Phayne\Saml\Model\Metadata\EntityDescriptor;
use Phayne\Saml\Model\Metadata\SSODescriptor;
use Phayne\Saml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

/**
 * Class MetadataCredentialStore
 *
 * @package Phayne\Saml\Store\Credential
 */
class MetadataCredentialStore implements CredentialStoreInterface
{
    public function __construct(protected EntityDescriptorStoreInterface $entityDescriptorProvider)
    {
    }

    #[Override]
    public function entityById(string $entityId): array
    {

    }

    protected function extractCredentials(EntityDescriptor $entityDescriptor): array
    {
        $credentials = [];

        foreach ($entityDescriptor->idpSsoDescriptors() as $idpDescriptor) {
            $this->handleDescriptor($idpDescriptor, $entityDescriptor, $credentials);
        }

        foreach ($entityDescriptor->spSsoDescriptors() as $spDescriptor) {
            $this->handleDescriptor($spDescriptor, $entityDescriptor, $credentials);
        }

        return $credentials;
    }

    protected function handleDescriptor(
        SSODescriptor $ssoDescriptor,
        EntityDescriptor $entityDescriptor,
        array &$result
    ): void {
        foreach ($ssoDescriptor->keyDescriptors as $keyDescriptor) {
            $credential = new X509Credential($keyDescriptor->certificate);
            $credential->entityId = $entityDescriptor->entityID;
            $credential->addKeyName($keyDescriptor->certificate?->name());
            $credential->credentialContext = new CredentialContextSet([
                new MetadataCredentialContext($keyDescriptor, $ssoDescriptor, $entityDescriptor),
            ]);
            $credential->usageType = $keyDescriptor->use;

            $result[] = $credential;
        }
    }
}
