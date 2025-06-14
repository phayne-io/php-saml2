<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile;

/**
 * Enum ProfileContexts
 *
 * @package Phayne\Saml\Context\Profile
 */
enum ProfileContexts: string
{
    case INBOUND_MESSAGE = 'inbound_message';
    case OUTBOUND_MESSAGE = 'outbound_message';
    case OWN_ENTITY = 'own_entity';
    case PARTY_ENTITY = 'party_entity';
    case DESERIALIZATION = 'deserialization';
    case SERIALIZATION = 'serialization';
    case HTTP_REQUEST = 'http_request';
    case HTTP_RESPONSE = 'http_response';
    case ENDPOINT = 'endpoint';
    case REQUEST_STATE = 'request_state';
    case LOGOUT = 'logout';
    case EXCEPTION = 'exception';
}
