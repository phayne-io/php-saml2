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

use Override;
use Phayne\Saml\Action\Assertion\AbstractAssertionAction;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Exception\SamlContextException;
use Psr\Log\LoggerInterface;

/**
 * Class AssertionIssuerFormatValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class AssertionIssuerFormatValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, private readonly string $expectedIssuerFormat)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        if (null === $context->assertion?->issuer) {
            $message = 'Assertion element must have an issuer element';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        if (
            $context->assertion->issuer->format !== null &&
            $context->assertion->issuer->format !== $this->expectedIssuerFormat
        ) {
            $message = sprintf(
                "Response Issuer Format if set must have value '%s' but it was '%s'",
                $this->expectedIssuerFormat,
                $context->assertion->issuer->format
            );
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this, [
                'actualFormat' => $context->assertion->issuer->format,
                'expectedFormat' => $this->expectedIssuerFormat,
            ]));
            throw new SamlContextException($context, $message);
        }
    }
}
