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
use Phayne\Saml\Context\Profile\ProfileContexts;
use Phayne\Saml\Context\Profile\RequestStateContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\State\Request\RequestState;
use Phayne\Saml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InResponseToValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class InResponseToValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStore)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        if (null === $context->assertion?->subject) {
            return;
        }

        foreach ($context->assertion->subject->subjectConfirmations() as $subjectConfirmation) {
            if (
                $subjectConfirmation->getSubjectConfirmationData()
                && $subjectConfirmation->getSubjectConfirmationData()->getInResponseTo()
            ) {
                $requestState = $this->validateInResponseTo(
                    $subjectConfirmation->getSubjectConfirmationData()->getInResponseTo(),
                    $context
                );

                /** @var RequestStateContext $requestStateContext */
                $requestStateContext = $context->subContext(
                    ProfileContexts::REQUEST_STATE,
                    RequestStateContext::class
                );
                $requestStateContext->requestState = $requestState;
            }
        }
    }

    protected function validateInResponseTo(string $inResponseTo, AssertionContext $context): RequestState
    {
        $requestState = $this->requestStore->get($inResponseTo);
        if (null == $requestState) {
            $message = sprintf("Unknown InResponseTo '%s'", $inResponseTo);
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        return $requestState;
    }
}
