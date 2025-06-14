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

use DateTime;
use Override;
use Phayne\Saml\Exception\SamlException;
use Phayne\Saml\Meta\ParameterBag;
use Serializable;

use function array_fill;
use function array_merge;
use function serialize;
use function sprintf;
use function unserialize;

/**
 * Class SsoSessionState
 *
 * @package Phayne\Saml\State\Sso
 */
class SsoSessionState implements Serializable
{
    public string $idpEntityId;

    public string $spEntityId;

    public string $nameId;

    public string $nameIdFormat;

    public string $sessionIndex;

    public DateTime $firstAuthOn;

    public DateTime $lastAuthOn;

    public DateTime $sessionInstant;

    protected(set) ParameterBag $parameters;

    public function __construct()
    {
        $this->parameters = new ParameterBag();
    }

    public function options(): array
    {
        return $this->parameters->all();
    }

    public function addOption(string $name, mixed $value): SsoSessionState
    {
        $this->parameters->set($name, $value);
        return $this;
    }

    public function removeOption(string $name): SsoSessionState
    {
        $this->parameters->remove($name);
        return $this;
    }

    public function hasOption(string $name): bool
    {
        return $this->parameters->has($name);
    }

    public function otherPartyId(string $partyId): string
    {
        if ($partyId === $this->idpEntityId) {
            return $this->spEntityId;
        } elseif ($partyId === $this->spEntityId) {
            return $this->idpEntityId;
        }

        throw new SamlException(sprintf(
            'Party "%s" is not included in sso session between "%s" and "%s"',
            $partyId,
            $this->idpEntityId,
            $this->spEntityId
        ));
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
        return[
            $this->idpEntityId,
            $this->spEntityId,
            $this->nameId,
            $this->nameIdFormat,
            $this->sessionIndex,
            $this->sessionInstant,
            $this->firstAuthOn,
            $this->lastAuthOn,
            [],
            $this->parameters,
        ];
    }

    public function __unserialize(array $data): void
    {
        $data = array_merge($data, array_fill(0, 5, null));

        [
            $this->idpEntityId,
            $this->spEntityId,
            $this->nameId,
            $this->nameIdFormat,
            $this->sessionIndex,
            $this->sessionInstant,
            $this->firstAuthOn,
            $this->lastAuthOn,
            $options,
            $this->parameters
        ] = $data;

        // if deserialized from old format, set old options to new parameters
        if ($options && 0 == $this->parameters->count()) {
            $this->parameters->replace($options);
        }
    }
}
