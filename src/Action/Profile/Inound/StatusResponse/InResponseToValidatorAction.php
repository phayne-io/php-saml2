<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\StatusResponse;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Context\Profile\ProfileContexts;
use Phayne\Saml\Context\Profile\RequestStateContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\State\Request\RequestStateParameters;
use Phayne\Saml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class InResponseToValidatorAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\StatusResponse
 */
class InResponseToValidatorAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStateStore)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asStatusResponse($context->inboundContext());
        $inResponseTo = $response->inResponseTo;

        if (null !== $inResponseTo) {
            $requestState = $this->requestStateStore->get($inResponseTo);

            if (null == $requestState) {
                $message = sprintf("Unknown InResponseTo '%s'", $inResponseTo);
                $this->logger->critical($message, LogHelper::actionErrorContext($context, $this, [
                    'in_response_to' => $inResponseTo,
                ]));
                throw new SamlContextException($context, $message);
            }
            $sentToParty = $requestState->parameters->get(RequestStateParameters::PARTY);

            if (null !== $sentToParty && null !== $response->issuer && $response->issuer->value !== $sentToParty) {
                $message = sprintf(
                    'AuthnRequest with id "%s" sent to party "%s" but 
                    StatusResponse for that request issued by party "%s"',
                    $inResponseTo,
                    $sentToParty,
                    $response->issuer->value
                );
                $this->logger->critical($message, LogHelper::actionErrorContext($context, $this, [
                    'sent_to' => $sentToParty,
                    'received_from' => $response->issuer->value,
                ]));
                throw new SamlContextException($context, $message);
            }

            /** @var RequestStateContext $requestStateContext */
            $requestStateContext = $context->inboundContext()->subContext(
                ProfileContexts::REQUEST_STATE,
                RequestStateContext::class
            );
            $requestStateContext->requestState = $requestState;
        }
    }
}
