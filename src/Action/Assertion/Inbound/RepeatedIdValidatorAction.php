<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Assertion\Inbound;

use DateTime;
use LogicException;
use Override;
use Phayne\Saml\Action\Assertion\AbstractAssertionAction;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Assertion\SubjectConfirmation;
use Phayne\Saml\Store\Id\IdStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RepeatedIdValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class RepeatedIdValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected IdStoreInterface $idStore)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        if ($context->assertion->hasBearerSubject()) {
            $this->validateBearerAssertion($context);
        }
    }

    protected function validateBearerAssertion(AssertionContext $context): void
    {
        if (null === $context->assertion?->id) {
            $message = 'Bearer Assertion must have ID attribute';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        if (null === $context->assertion?->issuer) {
            $message = 'Bearer Assertion must have issuer element';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        if ($this->idStore->has($context->assertion?->issuer->value, $context->assertion?->id)) {
            $message = sprintf(
                'Repeated assertion id "%s" of issuer "%s"',
                $context->assertion?->id,
                $context->assertion?->issuer?->value,
            );
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this, [
                'id' => $context->assertion?->id,
                'issuer' => $context->assertion?->issuer->value,
            ]));
            throw new SamlContextException($context, $message);
        }

        $this->idStore->set(
            $context->assertion?->issuer->value,
            $context->assertion?->id,
            $this->getIdExpiryTime($context)
        );
    }

    protected function getIdExpiryTime(AssertionContext $context): DateTime
    {
        /** @var DateTime $expiryTime */
        $expiryTime = null;
        $bearerConfirmations = $context->assertion?->subject?->bearerConfirmations();

        if (null == $bearerConfirmations) {
            throw new LogicException('Bearer assertion must have bearer subject confirmations');
        }

        /** @var SubjectConfirmation $subjectConfirmation */
        foreach ($bearerConfirmations as $subjectConfirmation) {
            if (null == $subjectConfirmation->subjectConfirmationData) {
                $message = 'Bearer SubjectConfirmation must have SubjectConfirmationData element';
                $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
                throw new SamlContextException($context, $message);
            }

            if (null === $subjectConfirmation->subjectConfirmationData->notOnOrAfter) {
                $message = 'Bearer SubjectConfirmation must have NotOnOrAfter attribute';
                $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
                throw new SamlContextException($context, $message);
            } else {
                $dt = new DateTime('@' . $subjectConfirmation->subjectConfirmationData->notOnOrAfter);
            }

            if (null === $expiryTime || $expiryTime->getTimestamp() < $dt->getTimestamp()) {
                $expiryTime = $dt;
            }
        }

        if (null === $expiryTime) {
            $message = 'Unable to find NotOnOrAfter attribute in bearer assertion';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        return $expiryTime;
    }
}
