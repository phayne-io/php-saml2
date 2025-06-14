<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Provider\TimeProvider;

use DateTime;
use Override;

use function time;

/**
 * Class SystemTimeProvider
 *
 * @package Phayne\Saml\Provider\TimeProvider
 */
class SystemTimeProvider implements TimeProviderInterface
{
    public int $timeStamp {
        #[Override]
        get {
            return time();
        }
    }
    public DateTime $dateTime {
        #[Override]
        get {
            return new DateTime();
        }
    }
}
