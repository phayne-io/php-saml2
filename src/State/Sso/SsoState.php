<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\State\Sso;

use Override;
use Phayne\Saml\Meta\ParameterBag;
use Serializable;

/**
 * Class SsoState
 *
 * @package Phayne\Saml\State\Sso
 */
class SsoState implements Serializable
{
    public string $localSessionId;

    protected(set) ParameterBag $parameters;

    private array $ssoSessions = [];

    public function __construct()
    {
        $this->parameters = new ParameterBag();
    }

    public function addSsoSession(SsoSessionState $ssoSessionState): SsoState
    {
        $this->ssoSessions[] = $ssoSessionState;
        return $this;
    }

    public function filter(
        string $idpEntityId,
        string $spEntityId,
        string $nameId,
        string $nameIdFormat,
        string $sessionIndex
    ): array {
        $result = [];

        /** @var SsoSessionState $ssoSession */
        foreach ($this->ssoSessions as $ssoSession) {
            if (
                (!$idpEntityId || $ssoSession->idpEntityId === $idpEntityId)
                && (!$spEntityId || $ssoSession->spEntityId === $spEntityId)
                && (!$nameId || $ssoSession->nameId === $nameId)
                && (!$nameIdFormat || $ssoSession->nameIdFormat === $nameIdFormat)
                && (!$sessionIndex || $ssoSession->sessionIndex === $sessionIndex)
            ) {
                $result[] = $ssoSession;
            }
        }

        return $result;
    }

    public function modify(callable $callback): SsoState
    {
        $this->ssoSessions = array_values(array_filter($this->ssoSessions, $callback));
        return $this;
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
        return [
            $this->localSessionId,
            $this->ssoSessions,
            [],
            $this->parameters,
        ];
    }

    public function __unserialize(array $data): void
    {
        $data = array_merge($data, array_fill(0, 5, null));
        $oldOptions = null;

        [
            $this->localSessionId,
            $this->ssoSessions,
            $oldOptions,
            // old deprecated options
            $this->parameters,
        ] = $data;

        // in case it was serialized in old way, copy old options to parameters
        if ($oldOptions && 0 == $this->parameters?->count()) {
            $this->parameters?->add($oldOptions);
        }
    }
}
