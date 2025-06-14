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
use Phayne\Saml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class KnownAssertionIssuerAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class KnownAssertionIssuerAction extends AbstractAssertionAction
{
    public function __construct(
        LoggerInterface $logger,
        private readonly EntityDescriptorStoreInterface $idpEntityDescriptorProvider
    ) {
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

        if (false === $this->idpEntityDescriptorProvider->has($context->assertion?->issuer?->value)) {
            $message = sprintf("Unknown issuer '%s'", $context->assertion?->issuer?->value);
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this, [
                'messageIssuer' => $context->assertion?->issuer?->value,
            ]));
            throw new SamlContextException($context, $message);
        }

        $this->logger->debug(
            sprintf('Known assertion issuer: "%s"', $context->assertion?->issuer?->value),
            LogHelper::actionContext($context, $this)
        );
    }
}
