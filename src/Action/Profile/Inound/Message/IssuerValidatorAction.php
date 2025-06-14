<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Message;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Exception\SamlValidationException;
use Phayne\Saml\Validator\Model\NameId\NameIdValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class IssuerValidatorAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class IssuerValidatorAction extends AbstractProfileAction
{
    public function __construct(
        LoggerInterface $logger,
        protected NameIdValidatorInterface $nameIdValidator,
        protected string $allowedFormat,
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $message = MessageContextHelper::asSamlMessage($context->inboundContext());

        if (null === $message->issuer) {
            $message = 'Inbound message issuer is required.';
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        if (
            null !== $message->issuer?->value &&
            null !== $message->issuer?->format &&
            $message->issuer?->format === $this->allowedFormat
        ) {
            $message = sprintf(
                'Response issuer format if set must have value "%s" but it was "%s"',
                $this->allowedFormat,
                $message->issuer?->format
            );
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        try {
            $this->nameIdValidator->validateNameId($message->issuer);
        } catch (SamlValidationException $e) {
            throw new SamlContextException($context, $e->getMessage(), 0, $e);
        }
    }
}
