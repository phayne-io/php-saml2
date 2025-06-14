<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Message;

use Override;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Model\Metadata\SingleSignOnService;
use Phayne\Saml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;

/**
 * Class DestinationValidatorAuthnRequestAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class DestinationValidatorAuthnRequestAction extends AbstractDestinationValidatorAction
{
    #[Override]
    protected function buildCriteriaSet(ProfileContext $context, string $location): CriteriaSet
    {
        $criteriaSet = parent::buildCriteriaSet($context, $location);
        $criteriaSet->add(new ServiceTypeCriteria(SingleSignOnService::class));
        return $criteriaSet;
    }
}
