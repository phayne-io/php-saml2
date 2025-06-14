<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\State\Request;

/**
 * Enum RequestStateParameters
 *
 * @package Phayne\Saml\State\Request
 */
enum RequestStateParameters: string
{
    case ID = 'id';
    case TYPE = 'type';
    case TIMESTAMP = 'ts';
    case PARTY = 'party';
    case RELAY_STATE = 'relay_state';
    case NAME_ID = 'name_id';
    case NAME_ID_FORMAT = 'name_id_format';
    case SESSION_INDEX = 'session_index';
}
