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
use Phayne\Saml\Model\Assertion\AttributeStatement;
use Phayne\Saml\Model\Assertion\AudienceRestriction;
use Phayne\Saml\Model\Assertion\AuthnStatement;
use Phayne\Saml\Model\Assertion\Conditions;
use Phayne\Saml\Model\Assertion\OneTimeUse;
use Phayne\Saml\Model\Assertion\ProxyRestriction;
use Phayne\Saml\SamlConstant;
use Phayne\Saml\Validator\Model\NameId\NameIdValidatorInterface;
use Phayne\Saml\Validator\Model\Statement\StatementValidatorInterface;
use Phayne\Saml\Validator\Model\Subject\SubjectValidatorInterface;

/**
 * Class AssertionValidator
 *
 * @package Phayne\Saml\Validator\Model\Assertion
 */
class AssertionValidator implements AssertionValidatorInterface
{
    public function __construct(
        protected NameIdValidatorInterface $nameIdValidator,
        protected SubjectValidatorInterface $subjectValidator,
        protected StatementValidatorInterface $statementValidator)
    {
    }

    #[Override]
    public function validateAssertion(Assertion $assertion): void
    {
        $this->validateAssertionAttributes($assertion);
        $this->validateSubject($assertion);
        $this->validateConditions($assertion);
        $this->validateStatements($assertion);
    }

    protected function validateAssertionAttributes(Assertion $assertion): void
    {
        if (false === Helper::validateRequiredString($assertion->version->value)) {
            throw new SamlValidationException(
                'Assertion element must have the Version attribute set.'
            );
        }
        if (SamlConstant::VERSION_20 != $assertion->version) {
            throw new SamlValidationException(
                'Assertion element must have the Version attribute value equal to 2.0.'
            );
        }
        if (false === Helper::validateRequiredString($assertion->id)) {
            throw new SamlValidationException(
                'Assertion element must have the ID attribute set.'
            );
        }
        if (false === Helper::validateIdString($assertion->id)) {
            throw new SamlValidationException(
                'Assertion element must have an ID attribute with at least 16 characters (the equivalent of 128 bits).'
            );
        }
        if (null === $assertion->issueInstant) {
            throw new SamlValidationException(
                'Assertion element must have the IssueInstant attribute set.'
            );
        }
        if (null === $assertion->issuer) {
            throw new SamlValidationException(
                'Assertion element must have an issuer element.'
            );
        }
        $this->nameIdValidator->validateNameId($assertion->issuer);
    }

    /**
     * @throws SamlValidationException
     */
    protected function validateSubject(Assertion $assertion): void
    {
        if (null === $assertion->subject) {
            if (empty($assertion->items)) {
                throw new SamlValidationException('Assertion with no Statements must have a subject.');
            }
            foreach ($assertion->items as $item) {
                if ($item instanceof AuthnStatement || $item instanceof AttributeStatement) {
                    throw new SamlValidationException(
                        'AuthnStatement, AuthzDecisionStatement and AttributeStatement require a subject.'
                    );
                }
            }
        } else {
            $this->subjectValidator->validateSubject($assertion->subject);
        }
    }

    protected function validateConditions(Assertion $assertion): void
    {
        if (null === $assertion->conditions) {
            return;
        }

        $this->validateConditionsInterval($assertion->conditions);

        $oneTimeUseSeen = $proxyRestrictionSeen = false;

        foreach ($assertion->conditions?->items as $item) {
            if ($item instanceof OneTimeUse) {
                if ($oneTimeUseSeen) {
                    throw new SamlValidationException(
                        'Assertion contained more than one condition of type OneTimeUse'
                    );
                }
                $oneTimeUseSeen = true;
            } elseif ($item instanceof ProxyRestriction) {
                if ($proxyRestrictionSeen) {
                    throw new SamlValidationException(
                        'Assertion contained more than one condition of type ProxyRestriction'
                    );
                }
                $proxyRestrictionSeen = true;

                $this->validateProxyRestriction($item);
            } elseif ($item instanceof AudienceRestriction) {
                $this->validateAudienceRestriction($item);
            }
        }
    }

    protected function validateConditionsInterval(Conditions $conditions): void
    {
        if (
            null !== $conditions->notBefore
            && null !== $conditions->notOnOrAfter
            && $conditions->notBefore > $conditions->notOnOrAfter
        ) {
            throw new SamlValidationException('Conditions NotBefore MUST BE less than NotOnOrAfter');
        }
    }

    /**
     * @throws SamlValidationException
     */
    protected function validateProxyRestriction(ProxyRestriction $item): void
    {
        if (null === $item->count || $item->count < 0) {
            throw new SamlValidationException('Count attribute of ProxyRestriction MUST BE a non-negative integer');
        }

        if (false === empty($item->audience)) {
            foreach ($item->audience as $audience) {
                if (false === Helper::validateWellFormedUriString($audience)) {
                    throw new SamlValidationException(
                        'ProxyRestriction Audience MUST BE a wellformed uri'
                    );
                }
            }
        }
    }

    protected function validateAudienceRestriction(AudienceRestriction $item): void
    {
        if (empty($item->audience)) {
            return;
        }

        foreach ($item->audience as $audience) {
            if (false === Helper::validateWellFormedUriString($audience)) {
                throw new SamlValidationException(
                    'AudienceRestriction MUST BE a wellformed uri'
                );
            }
        }
    }

    protected function validateStatements(Assertion $assertion): void
    {
        if (empty($assertion->items)) {
            return;
        }

        foreach ($assertion->items as $statement) {
            $this->statementValidator->validateStatement($statement);
        }
    }
}
