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
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Model\Protocol\LogoutRequest;
use Phayne\Saml\State\Request\RequestState;
use Phayne\Saml\State\Request\RequestStateParameters;
use Phayne\Saml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SaveRequestStateAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class SaveRequestStateAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStateStore)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $message = MessageContextHelper::asSamlMessage($context->outboundContext());
        $state = new RequestState();
        $state->id = $message->id;

        $partyEntityId = $context->partyEntityContext()
            ? $context->partyEntityContext()->entityId
            : '';

        if (null !== $context->partyEntityContext() && null !== $context->partyEntityContext()->entityDescriptor) {
            $partyEntityId = $context->partyEntityContext()->entityDescriptor->entityID;
        }

        $state->parameters->add([
            RequestStateParameters::ID->value => $message->id,
            RequestStateParameters::TYPE->value => $message::class,
            RequestStateParameters::TIMESTAMP->value => $message->issueInstant,
            RequestStateParameters::PARTY->value => $partyEntityId,
            RequestStateParameters::RELAY_STATE->value => $message->relayState,
        ]);

        if( $message instanceof LogoutRequest) {
            $state->parameters->add([
                RequestStateParameters::NAME_ID->value => $message->nameID?->value,
                RequestStateParameters::NAME_ID_FORMAT->value => $message->nameID?->format,
                RequestStateParameters::SESSION_INDEX->value => $message->sessionIndex,
            ]);
        }

        $this->requestStateStore->set($state);
    }
}
