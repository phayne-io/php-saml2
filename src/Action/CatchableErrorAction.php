<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action;

use Override;
use Phayne\Saml\Context\ContextInterface;
use Phayne\Saml\Context\Profile\ExceptionContext;
use Phayne\Saml\Context\Profile\ProfileContexts;
use Throwable;

/**
 * Class CatchableErrorAction
 *
 * @package Phayne\Saml\Action
 */
class CatchableErrorAction implements ActionInterface
{
    public function __construct(protected ActionInterface $mainAction, protected ActionInterface $errorAction)
    {
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        try {
            $this->mainAction->execute($context);
        } catch (Throwable $ex) {
            /** @var ExceptionContext $exceptionContext */
            $exceptionContext = $context->subContext(ProfileContexts::EXCEPTION, ExceptionContext::class);
            $exceptionContext->addException($ex);

            $this->errorAction->execute($context);
        }
    }
}
