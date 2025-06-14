<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Profile;

/**
 * Enum Profiles
 *
 * @package Phayne\Saml\Profile
 */
enum Profiles: string
{
    case METADATA = 'metadata';

    case SSO_IDP_RECEIVE_AUTHN_REQUEST = 'sso_idp_receive_authn_req';
    case SSO_IDP_SEND_RESPONSE = 'sso_idp_send_response';
    case SSO_SP_SEND_AUTHN_REQUEST = 'sso_sp_send_authn_req';
    case SSO_SP_RECEIVE_RESPONSE = 'sso_sp_receive_response';
}
