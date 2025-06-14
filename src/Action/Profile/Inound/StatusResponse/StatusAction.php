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
use Phayne\Saml\Exception\SamlAuthenticationException;
use Phayne\Saml\Exception\SamlContextException;

use function compact;

/**
 * Class StatusAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\StatusResponse
 */
class StatusAction extends AbstractProfileAction
{
    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $statusResponse = MessageContextHelper::asStatusResponse($context->inboundContext());

        if (null !== $statusResponse->status && $statusResponse->status->isSuccess()) {
            return;
        }

        if (null === $statusResponse->status) {
            $message = 'Status response does not have Status set.';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        $status = $statusResponse->status->statusCode->value;
        $status .= "\n" . $statusResponse->status->statusMessage;

        if (null !== $statusResponse->status->statusCode?->statusCode) {
            $status .= "\n" . $statusResponse->status->statusCode->statusCode->value;
        }

        $message = 'Unsuccessful SAML response: ' . $status;
        $this->logger->error($message, LogHelper::actionErrorContext($context, $this, compact('status')));

        throw new SamlAuthenticationException($statusResponse, $message);
    }
}
