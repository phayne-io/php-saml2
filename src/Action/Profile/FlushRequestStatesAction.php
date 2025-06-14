<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile;

use Override;
use Phayne\Saml\Context\ContextInterface;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Context\Profile\ProfileContexts;
use Phayne\Saml\Context\Profile\RequestStateContext;
use Phayne\Saml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FlushRequestStatesAction
 *
 * @package Phayne\Saml\Action\Profile
 */
class FlushRequestStatesAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStateStore)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $this->flush($context->inboundContext()->subContext(ProfileContexts::REQUEST_STATE));

        foreach ($context as $child) {
            if ($child instanceof AssertionContext) {
                $this->flush($child->subContext(ProfileContexts::REQUEST_STATE));
            }
        }
    }

    protected function flush(?ContextInterface $requestStateContext = null): void
    {
        if (
            $requestStateContext instanceof RequestStateContext &&
            null !== $requestStateContext->requestState &&
            null !== $requestStateContext->requestState->id
        ) {
            $existed = $this->requestStateStore->remove($requestStateContext->requestState->id);

            if (true === $existed) {
                $this->logger->debug(
                    sprintf('Removed request state "%s".', $requestStateContext->requestState->id),
                    LogHelper::actionContext($requestStateContext, $this)
                );
            } else {
                $this->logger->warning(
                    sprintf('Request state "%s" does not exist.', $requestStateContext->requestState->id),
                    LogHelper::actionContext($requestStateContext, $this)
                );
            }
        }
    }
}
