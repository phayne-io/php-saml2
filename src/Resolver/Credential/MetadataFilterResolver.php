<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Credential;

use Override;
use Phayne\Saml\Credential\Context\MetadataCredentialContext;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Model\Metadata\IdpSsoDescriptor;
use Phayne\Saml\Model\Metadata\SpSsoDescriptor;

/**
 * Class MetadataFilterResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class MetadataFilterResolver extends AbstractQueryableResolver
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        if (false === $criteriaSet->has(MetadataCriteria::class)) {
            return $credentials;
        }

        $result = [];

        /** @var MetadataCriteria $criteria */
        foreach ($criteriaSet->get(MetadataCriteria::class) as $criteria) {
            /** @var CredentialInterface $credential */
            foreach ($credentials as $credential) {
                /** @var MetadataCredentialContext $metadataContext */
                $metadataContext = $credential->credentialContext->get(MetadataCredentialContext::class);

                if (
                    null === $metadataContext ||
                    (
                        (MetadataCriteria::TYPE_IDP === $criteria->metadataType) &&
                        ($metadataContext->roleDescriptor instanceof IdpSsoDescriptor)
                    ) || (
                        (MetadataCriteria::TYPE_SP === $criteria->metadataType) &&
                        ($metadataContext->roleDescriptor instanceof SpSsoDescriptor)
                    )
                ) {
                    $result[] = $credential;
                }
            }
        }

        return $result;
    }
}
