<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Outbound\Message;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\ProfileContext;

/**
 * Class ForwardRelayStateAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class ForwardRelayStateAction extends AbstractProfileAction
{
    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        if (null === $context->inboundContext()->message) {
            return;
        }

        if ($context->inboundMessage()->relayState) {
            $context->outboundMessage()->relayState = $context->inboundMessage()->relayState;
            $this->logger->debug(
                sprintf('Forwarding relay state from inbound message "%s"', $context->inboundMessage()->relayState)
            );
        }
    }
}
