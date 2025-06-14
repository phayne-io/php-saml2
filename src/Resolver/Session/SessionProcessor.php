<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Session;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Override;
use Phayne\Saml\Model\Assertion\Assertion;
use Phayne\Saml\Provider\TimeProvider\TimeProviderInterface;
use Phayne\Saml\State\Sso\SsoSessionState;
use Phayne\Saml\State\Sso\SsoState;
use Phayne\Saml\Store\Sso\SsoStateStoreInterface;

/**
 * Class SessionProcessor
 *
 * @package Phayne\Saml\Resolver\Session
 */
class SessionProcessor implements SessionProcessorInterface
{
    public function __construct(protected SsoStateStoreInterface $ssoStateStore, protected TimeProviderInterface $timeProvider)
    {
    }

    #[Override]
    public function processAssertions(array $assertions, string $ownEntityId, string $partyEntityId): void
    {
        $now = $this->timeProvider->dateTime->setTimezone(new DateTimeZone('UTC'));
        $ssoState = $this->ssoStateStore->get();

        foreach ($assertions as $assertion) {
            if ($assertion instanceof Assertion) {
                if ($this->supportsSession($assertion)) {
                    $this->checkSession($ownEntityId, $partyEntityId, $ssoState, $assertion, $now);
                }
            } else {
                throw new InvalidArgumentException('Expected Assertion');
            }
        }
    }

    protected function supportsSession(Assertion $assertion): bool
    {
        return $assertion->hasBearerSubject()
            && null !== $assertion->subject
            && null !== $assertion->subject->nameID;
    }

    protected function checkSession(
        string $ownEntityId,
        string $partyEntityId,
        SsoState $ssoState,
        Assertion $assertion,
        DateTime $now
    ): void {
        $sessions = $this->filterSessions($ssoState, $assertion, $ownEntityId, $partyEntityId);

        if (empty($sessions)) {
            $this->createSession($ssoState, $assertion, $now, $ownEntityId, $partyEntityId);
        } else {
            $this->updateLastAuthn($sessions, $now);
        }
    }

    protected function createSession(
        SsoState $ssoState,
        Assertion $assertion,
        DateTime $now,
        string $ownEntityId,
        string $partyEntityId
    ): SsoSessionState {
        $ssoSession = new SsoSessionState();
        $ssoSession->idpEntityId = $partyEntityId;
        $ssoSession->spEntityId = $ownEntityId;
        $ssoSession->nameId = $assertion->subject?->nameID?->value;
        $ssoSession->nameIdFormat = $assertion->subject?->nameID?->format;
        $ssoSession->sessionIndex = $assertion->firstAuthnStatement?->sessionIndex;
        $ssoSession->sessionInstant = new DateTime('@' . $assertion->firstAuthnStatement?->authnInstant);
        $ssoSession->firstAuthOn = $now;
        $ssoSession->lastAuthOn = $now;
        $ssoState->addSsoSession($ssoSession);

        return $ssoSession;
    }

    protected function updateLastAuthn(array $sessions, DateTime $now): void
    {
        /** @var SsoSessionState $session */
        foreach ($sessions as $session) {
            $session->lastAuthOn = $now;
        }
    }

    protected function filterSessions(SsoState $ssoState, Assertion $assertion, $ownEntityId, $partyEntityId): array
    {
        return $ssoState->filter(
            $partyEntityId,
            $ownEntityId,
            $assertion->subject?->nameID?->value,
            $assertion->subject?->nameID?->format,
            $assertion->firstAuthnStatement?->sessionIndex
        );
    }
}
