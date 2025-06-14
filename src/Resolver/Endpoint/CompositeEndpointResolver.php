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

/**
 * Class CompositeEndpointResolver
 *
 * @package Phayne\Saml\Resolver\Endpoint
 */
class CompositeEndpointResolver implements EndpointResolverInterface
{
    public function __construct(protected array $resolvers)
    {
    }

    public function add(EndpointResolverInterface $resolver): CompositeEndpointResolver
    {
        $this->resolvers[] = $resolver;
        return $this;
    }

    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $candidates): array
    {
        $result = $candidates;

        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolve($criteriaSet, $result);
        }

        return $result;
    }
}
