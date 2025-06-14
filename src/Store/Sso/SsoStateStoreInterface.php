<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Sso;

use Phayne\Saml\State\Sso\SsoState;

/**
 * Interface SsoStateStoreInterface
 *
 * @package Phayne\Saml\Store\Sso
 */
interface SsoStateStoreInterface
{
    public function get(): SsoState;

    public function set(SsoState $ssoState): void;
}
