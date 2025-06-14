<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Assertion;

use Override;
use Phayne\Saml\Exception\SamlValidationException;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Assertion\Assertion;
use Phayne\Saml\Model\Assertion\AuthnStatement;
use Phayne\Saml\Model\Assertion\SubjectConfirmation;

/**
 * Class AssertionTimeValidator
 *
 * @package Phayne\Saml\Validator\Model\Assertion
 */
class AssertionTimeValidator implements AssertionTimeValidatorInterface
{
    #[Override]
    public function validateTimeRestrictions(Assertion $assertion, int $now, int $allowedSecondsSkew): void
    {
        if ($allowedSecondsSkew < 0) {
            $allowedSecondsSkew = -1 * $allowedSecondsSkew;
        }

        $this->validateConditions($assertion, $now, $allowedSecondsSkew);
        $this->validateAuthnStatements($assertion, $now, $allowedSecondsSkew);
        $this->validateSubject($assertion, $now, $allowedSecondsSkew);
    }

    protected function validateConditions(Assertion $assertion, int $now, int $allowedSecondsSkew): void
    {
        if (null === $assertion->conditions) {
            return;
        }

        if (false === Helper::validateNotBefore($assertion->conditions->notBefore, $now, $allowedSecondsSkew)) {
            throw new SamlValidationException('Conditions.NotBefore must not be in the future');
        }

        if (false === Helper::validateNotOnOrAfter($assertion->conditions->notOnOrAfter, $now, $allowedSecondsSkew)) {
            throw new SamlValidationException('Conditions.NotOnOrAfter must not be in the past');
        }
    }

    protected function validateAuthnStatements(Assertion $assertion, int $now, int $allowedSecondsSkew): void
    {
        if (empty($assertion->authnStatements)) {
            return;
        }

        /** @var AuthnStatement $authnStatement */
        foreach ($assertion->authnStatements as $authnStatement) {
            if (false === Helper::validateNotOnOrAfter($authnStatement->sessionNotOnOrAfter, $now, $allowedSecondsSkew)) {
                throw new SamlValidationException(
                    'AuthnStatement attribute SessionNotOnOrAfter MUST be in the future'
                );
            }
            // TODO: Consider validating that authnStatement.AuthnInstant is in the past
        }
    }

    protected function validateSubject(Assertion $assertion, int $now, int $allowedSecondsSkew): void
    {
        if (null === $assertion->subject) {
            return;
        }

        /** @var SubjectConfirmation $subjectConfirmation */
        foreach ($assertion->subject->subjectConfirmations() as $subjectConfirmation) {
            if (null !== $subjectConfirmation->subjectConfirmationData) {
                if (
                    false === Helper::validateNotBefore(
                        $subjectConfirmation->subjectConfirmationData->notBefore, 
                        $now, 
                        $allowedSecondsSkew
                    )
                ) {
                    throw new SamlValidationException('SubjectConfirmationData.NotBefore must not be in the future');
                }
                if (
                    false === Helper::validateNotOnOrAfter(
                        $subjectConfirmation->subjectConfirmationData->notOnOrAfter,
                        $now,
                        $allowedSecondsSkew
                    )
                ) {
                    throw new SamlValidationException('SubjectConfirmationData.NotOnOrAfter must not be in the past');
                }
            }
        }
    }
}
