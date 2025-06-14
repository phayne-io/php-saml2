<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Binding;

use Phayne\Saml\Context\Profile\MessageContext;
use Phayne\Saml\Event\MessageReceived;
use Phayne\Saml\Event\MessageSent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractBinding
 *
 * @package Phayne\Saml\Binding
 */
abstract class AbstractBinding
{
    public ?EventDispatcherInterface $eventDispatcher = null;

    protected function dispatchReceive(string $messageString): void
    {
        $this->eventDispatcher?->dispatch(new MessageReceived($messageString));
    }

    public function dispatchSend(string $messageString): void
    {
        $this->eventDispatcher?->dispatch(new MessageSent($messageString));
    }

    abstract public function send(MessageContext $context, ?string $destination = null): ResponseInterface;

    abstract public function receive(ServerRequestInterface $request, MessageContext $context): void;
}
