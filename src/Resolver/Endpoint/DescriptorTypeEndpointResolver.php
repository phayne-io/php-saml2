<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Endpoint;

use Override;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Model\Metadata\EndpointReference;
use Phayne\Saml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;

/**
 * Class DescriptorTypeEndpointResolver
 *
 * @package Phayne\Saml\Resolver\Endpoint
 */
class DescriptorTypeEndpointResolver implements EndpointResolverInterface
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $candidates): array
    {
        if (false === $criteriaSet->has(DescriptorTypeCriteria::class)) {
            return $candidates;
        }

        $result = [];

        /** @var DescriptorTypeCriteria $criteria */
        foreach ($criteriaSet->get(DescriptorTypeCriteria::class) as $criteria) {
            /** @var EndpointReference $endpointReference */
            foreach ($candidates as $endpointReference) {
                if ($endpointReference->descriptor instanceof $criteria->descriptorType) {
                    $result[] = $endpointReference;
                }
            }
        }

        return $result;
    }
}
