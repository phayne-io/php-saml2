<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Subject;

use Override;
use Phayne\Saml\Exception\SamlValidationException;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Assertion\Subject;
use Phayne\Saml\Model\Assertion\SubjectConfirmation;
use Phayne\Saml\Model\Assertion\SubjectConfirmationData;
use Phayne\Saml\Validator\Model\NameId\NameIdValidatorInterface;

/**
 * Class SubjectValidator
 *
 * @package Phayne\Saml\Validator\Model\Subject
 */
class SubjectValidator implements SubjectValidatorInterface
{
    public function __construct(protected NameIdValidatorInterface $nameIdValidator)
    {
    }

    #[Override]
    public function validateSubject(Subject $subject): void
    {
        if (null === $subject->nameID && null === $subject->subjectConfirmations()) {
            throw new SamlValidationException('Subject MUST contain either an identifier or a subject confirmation');
        }

        if (null !== $subject->nameID) {
            $this->nameIdValidator->validateNameId($subject->nameID);
        }

        foreach ($subject->subjectConfirmations() as $subjectConfirmation) {
            $this->validateSubjectConfirmation($subjectConfirmation);
        }
    }

    protected function validateSubjectConfirmation(SubjectConfirmation $subjectConfirmation): void
    {
        if (false === Helper::validateRequiredString($subjectConfirmation->method)) {
            throw new SamlValidationException(
                'Method attribute of SubjectConfirmation MUST contain at least one non-whitespace character'
            );
        }
        if (false === Helper::validateWellFormedUriString($subjectConfirmation->method)) {
            throw new SamlValidationException(
                'SubjectConfirmation element has Method attribute which is not a wellformed absolute uri.'
            );
        }

        if (null !== $subjectConfirmation->nameID) {
            $this->nameIdValidator->validateNameId($subjectConfirmation->nameID);
        }

        if (null !== $subjectConfirmation->subjectConfirmationData) {
            $this->validateSubjectConfirmationData($subjectConfirmation->subjectConfirmationData);
        }
    }

    protected function validateSubjectConfirmationData(SubjectConfirmationData $subjectConfirmationData): void
    {
        if (
            null !== $subjectConfirmationData->recipient &&
            false === Helper::validateWellFormedUriString($subjectConfirmationData->recipient)
        ) {
            throw new SamlValidationException(
                'Recipient of SubjectConfirmationData must be a wellformed absolute URI.'
            );
        }

        if (
            $subjectConfirmationData->notBefore
            && $subjectConfirmationData->notOnOrAfter
            && $subjectConfirmationData->notBefore >= $subjectConfirmationData->notOnOrAfter
        ) {
            throw new SamlValidationException('SubjectConfirmationData NotBefore MUST be less than NotOnOrAfter');
        }
    }
}
