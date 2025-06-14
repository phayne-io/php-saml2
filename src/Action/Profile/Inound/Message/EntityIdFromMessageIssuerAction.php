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
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;

/**
 * Class EntityIdFromMessageIssuerAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class EntityIdFromMessageIssuerAction extends AbstractProfileAction
{
    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $message = MessageContextHelper::asSamlMessage($context->inboundContext());

        if (null === $message->issuer) {
            throw new SamlContextException($context, 'Inbound message issuer is required.');
        }

        $context->partyEntityContext()->entityId = $message->issuer->value;
    }
}
