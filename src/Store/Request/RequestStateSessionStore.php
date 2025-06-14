<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Request;

use Mezzio\Session\SessionInterface;
use Phayne\Saml\Store\Request\AbstractRequestStateArrayStore;

/**
 * Class RequestStateSessionStore
 *
 * @package Phayne\Saml\Store\Request
 */
class RequestStateSessionStore extends AbstractRequestStateArrayStore
{
    protected array $array {
        get => $this->session->get($this->key(), []);
        set(array $value) {
            $this->session->set($this->key(), $value);
        }
    }
    public function __construct(
        protected(set) SessionInterface $session,
        protected string $providerId,
        protected string $prefix = 'saml_request_state'
    ) {
    }

    protected function key(): string
    {
        return sprintf('%s_%s', $this->providerId, $this->prefix);
    }
}
