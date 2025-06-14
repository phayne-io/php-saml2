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

use Override;
use Phayne\Saml\Meta\ParameterBag;
use Serializable;

/**
 * Class RequestState
 *
 * @package Phayne\Saml\State\Request
 */
class RequestState implements Serializable
{
    protected(set) ParameterBag $parameters;

    public string $nonce {
        get {
            return $this->parameters->get('nonce');
        }
        set(string $value) {
            $this->parameters->set('nonce', $value);
        }
    }

    public function __construct(public ?string $id = null, mixed $nonce = null)
    {
        $this->parameters = new ParameterBag();

        if (null !== $nonce) {
            $this->parameters->set('nonce', $nonce);
        }
    }

    #[Override]
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    #[Override]
    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    public function __serialize(): array
    {
        $nonce = $this->parameters->get('nonce');

        return [$this->id, $nonce, $this->parameters->__serialize()];
    }

    public function __unserialize(array $data): void
    {
        $nonce = null;
        $this->parameters = new ParameterBag();
        [$this->id, $nonce, $parameters] = $data;
        $this->parameters->__unserialize($parameters);
    }
}
