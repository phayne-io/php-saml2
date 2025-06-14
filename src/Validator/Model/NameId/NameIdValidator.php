<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\NameId;

use Override;
use Phayne\Saml\Exception\SamlValidationException;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Assertion\AbstractNameID;
use Phayne\Saml\SamlConstant;

use function filter_var;
use function sprintf;
use function str_contains;
use function strlen;

use const FILTER_VALIDATE_EMAIL;

/**
 * Class NameIdValidator
 *
 * @package Phayne\Saml\Validator\Model\NameId
 */
class NameIdValidator implements NameIdValidatorInterface
{
    #[Override]
    public function validateNameId(AbstractNameID $nameId): void
    {
        if (null === $nameId->format) {
            return;
        }

        $this->validateFormat($nameId);

        $validatorMethod = SamlConstant::tryFrom($nameId->format)?->validatorMethod();

        if (null !== $validatorMethod) {
            $this->{$validatorMethod}($nameId);
        }
    }

    protected function validateFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateWellFormedUriString($nameId->format)) {
            throw new SamlValidationException(sprintf(
                "NameID element has Format attribute '%s' which is not a wellformed absolute uri",
                $nameId->format
            ));
        }
    }

    protected function validateEmailFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Email Format attribute MUST contain a Value that contains more than whitespace characters'
            );
        }

        if (false === filter_var($nameId->value, FILTER_VALIDATE_EMAIL)) {
            throw new SamlValidationException(
                'Value of NameID is not a valid email address according to the IETF RFC 2822 specification'
            );
        }
    }

    protected function validateX509SubjectNameFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with X509SubjectName Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }

        // TODO: Consider checking for correct encoding of the Value according to the
        // XML Signature Recommendation (http://www.w3.org/TR/xmldsig-core/) section 4.4.4
    }

    protected function validateWindowsFormat(AbstractNameID $nameId): void
    {
        // Required format is 'DomainName\UserName' but the domain name and the '\' are optional
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Windows Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }
    }

    protected function validateKerberosFormat(AbstractNameID $nameId): void
    {
        // Required format is 'name[/instance]@REALM'
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Kerberos Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }

        if (strlen($nameId->value) < 3) {
            throw new SamlValidationException(
                'NameID with Kerberos Format attribute MUST contain a Value with at least 3 characters'
            );
        }

        if (!str_contains($nameId->value, '@')) {
            throw new SamlValidationException(
                "NameID with Kerberos Format attribute MUST contain a Value that contains a '@'"
            );
        }
        // TODO: Consider implementing the rules for 'name', 'instance' and 'REALM' found in IETF RFC 1510 (http://www.ietf.org/rfc/rfc1510.txt) here
    }

    protected function validateEntityFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Entity Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }

        if (strlen($nameId->value) > 1024) {
            throw new SamlValidationException(
                'NameID with Entity Format attribute MUST have a Value that contains no more than 1024 characters'
            );
        }

        if (null !== $nameId->nameQualifier) {
            throw new SamlValidationException(
                'NameID with Entity Format attribute MUST NOT set the NameQualifier attribute'
            );
        }

        if (null !== $nameId->spNameQualifier) {
            throw new SamlValidationException(
                'NameID with Entity Format attribute MUST NOT set the SPNameQualifier attribute'
            );
        }

        if (null !== $nameId->spProviderId) {
            throw new SamlValidationException(
                'NameID with Entity Format attribute MUST NOT set the SPProvidedID attribute'
            );
        }
    }

    protected function validatePersistentFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Persistent Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }

        if (strlen($nameId->value) > 256) {
            throw new SamlValidationException(
                'NameID with Persistent Format attribute MUST have a Value that contains no more than 256 characters'
            );
        }
    }

    protected function validateTransientFormat(AbstractNameID $nameId): void
    {
        if (false === Helper::validateRequiredString($nameId->value)) {
            throw new SamlValidationException(
                'NameID with Transient Format attribute MUST contain a Value that contains 
                more than whitespace characters'
            );
        }

        if (strlen($nameId->value) > 256) {
            throw new SamlValidationException(
                'NameID with Transient Format attribute MUST have a Value that contains no more than 256 characters'
            );
        }

        if (false === Helper::validateIdString($nameId->value)) {
            throw new SamlValidationException(sprintf(
                "NameID '%s' with Transient Format attribute MUST have a value with at least 16 characters 
                (the equivalent of 128 bits)",
                $nameId->value
            ));
        }
    }
}
