<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Statement;

use Override;
use Phayne\Saml\Exception\SamlValidationException;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Assertion\AbstractStatement;
use Phayne\Saml\Model\Assertion\Attribute;
use Phayne\Saml\Model\Assertion\AttributeStatement;
use Phayne\Saml\Model\Assertion\AuthnContext;
use Phayne\Saml\Model\Assertion\AuthnStatement;

use function sprintf;

/**
 * Class StatementValidator
 *
 * @package Phayne\Saml\Validator\Model\Statement
 */
class StatementValidator implements StatementValidatorInterface
{
    #[Override]
    public function validateStatement(AbstractStatement $statement): void
    {
        if ($statement instanceof AuthnStatement) {
            $this->validateAuthnStatement($statement);
        } elseif ($statement instanceof AttributeStatement) {
            $this->validateAttributeStatement($statement);
        } else {
            throw new SamlValidationException(sprintf("Unsupported Statement type '%s'", $statement::class));
        }
    }

    private function validateAuthnStatement(AuthnStatement $statement): void
    {
        if (null ===  $statement->authnInstant) {
            throw new SamlValidationException('AuthnStatement MUST have an AuthnInstant attribute');
        }
        if (false === Helper::validateOptionalString($statement->sessionIndex)) {
            throw new SamlValidationException(
                'SessionIndex attribute of AuthnStatement must contain at least one non-whitespace character'
            );
        }
        if ($statement->subjectLocality) {
            if (false === Helper::validateOptionalString($statement->subjectLocality->address)) {
                throw new SamlValidationException(
                    'Address attribute of SubjectLocality must contain at least one non-whitespace character'
                );
            }
            if (false === Helper::validateOptionalString($statement->subjectLocality->dnsName)) {
                throw new SamlValidationException(
                    'DNSName attribute of SubjectLocality must contain at least one non-whitespace character'
                );
            }
        }
        if (null === $statement->authnContext) {
            throw new SamlValidationException('AuthnStatement MUST have an AuthnContext element');
        }
        $this->validateAuthnContext($statement->authnContext);
    }

    private function validateAuthnContext(AuthnContext $authnContext): void
    {
        if (
            null === $authnContext->authnContextClassRef
            && null === $authnContext->authnContextDecl
            && null === $authnContext->authnContextDeclRef
        ) {
            throw new SamlValidationException(
                'AuthnContext element MUST contain at least one AuthnContextClassRef, 
                AuthnContextDecl or AuthnContextDeclRef element'
            );
        }

        if (
            null !== $authnContext->authnContextClassRef
            && null !== $authnContext->authnContextDecl
            && null !== $authnContext->authnContextDeclRef
        ) {
            throw new SamlValidationException('AuthnContext MUST NOT contain more than two elements.');
        }

        if (
            null !== $authnContext->authnContextClassRef &&
            false === Helper::validateWellFormedUriString($authnContext->authnContextClassRef)
        ) {
            throw new SamlValidationException(
                'AuthnContextClassRef has a value which is not a wellformed absolute uri'
            );
        }
        if (
            null !== $authnContext->authnContextDeclRef &&
            false === Helper::validateWellFormedUriString($authnContext->authnContextDeclRef)
        ) {
            throw new SamlValidationException(
                'AuthnContextDeclRef has a value which is not a wellformed absolute uri'
            );
        }
    }

    private function validateAttributeStatement(AttributeStatement $statement): void
    {
        if (empty($statement->attributes)) {
            throw new SamlValidationException(
                'AttributeStatement MUST contain at least one Attribute or EncryptedAttribute'
            );
        }

        foreach ($statement->attributes as $attribute) {
            $this->validateAttribute($attribute);
        }
    }

    private function validateAttribute(Attribute $attribute): void
    {
        if (false === Helper::validateRequiredString($attribute->name)) {
            throw new SamlValidationException(
                'Name attribute of Attribute element MUST contain at least one non-whitespace character'
            );
        }
    }
}
