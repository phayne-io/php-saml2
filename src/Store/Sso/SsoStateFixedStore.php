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

use Override;
use Phayne\Saml\State\Sso\SsoState;

/**
 * Class SsoStateFixedStore
 *
 * @package Phayne\Saml\Store\Sso
 */
class SsoStateFixedStore implements SsoStateStoreInterface
{
    protected ?SsoState $ssoState = null;

    #[Override]
    public function get(): SsoState
    {
        return $this->ssoState ?? new SsoState();
    }

    #[Override]
    public function set(SsoState $ssoState): void
    {
        $this->ssoState = $ssoState;
    }
}
