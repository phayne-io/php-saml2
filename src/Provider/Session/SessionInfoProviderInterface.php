<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Provider\Session;

/**
 * Interface SessionInfoProviderInterface
 *
 * @package Phayne\Saml\Provider\Session
 */
interface SessionInfoProviderInterface
{
    public int $authnInstant {
        get;
        set;
    }

    public string $sessionIndex {
        get;
        set;
    }

    public string $authnContextClassRef {
        get;
        set;
    }
}
