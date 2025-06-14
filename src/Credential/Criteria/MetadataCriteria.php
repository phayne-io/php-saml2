<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Credential\Criteria;

use Phayne\Saml\SamlConstant;

/**
 * Class MetadataCriteria
 *
 * @package Phayne\Saml\Credential\Criteria
 */
readonly class MetadataCriteria implements TrustCriteriaInterface
{
    public const string TYPE_IDP = 'idp';
    public const string TYPE_SP = 'sp';

    public function __construct(
        public string $metadataType,
        public SamlConstant $protocol = SamlConstant::PROTOCOL_SAML2
    ) {
    }
}
