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

use Mezzio\Session\SessionInterface;
use Override;
use Phayne\Saml\State\Sso\SsoState;

/**
 * Class SsoStateSessionStore
 *
 * @package Phayne\Saml\Store\Sso
 */
class SsoStateSessionStore implements SsoStateStoreInterface
{
    public function __construct(protected SessionInterface $session, protected string $key)
    {
    }

    #[Override]
    public function get(): SsoState
    {
        $item = $this->session->get($this->key);

        if (null === $item) {
            $this->set($item = new SsoState());
        }

        return $item;
    }

    #[Override]
    public function set(SsoState $ssoState): void
    {
        $ssoState->localSessionId = $this->session->getId();
        $this->session->set($this->key, $ssoState);
    }
}
