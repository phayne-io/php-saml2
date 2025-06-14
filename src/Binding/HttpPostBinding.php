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

use Override;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\MessageContext;
use Phayne\Saml\Exception\SamlBindingException;
use Phayne\Saml\Model\Protocol\AbstractRequest;
use Phayne\Saml\Model\Protocol\SamlMessage;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use function array_key_exists;
use function base64_decode;

/**
 * Class HttpPostBinding
 *
 * @package Phayne\Saml\Binding
 */
class HttpPostBinding extends AbstractBinding
{
    #[Override]
    public function send(MessageContext $context, ?string $destination = null): ResponseInterface
    {
        $message = MessageContextHelper::asSamlMessage($context);
        $destination = $message->destination ?: $destination;

        $serializationContext = $context->serializationContext();
        $message->serialize($serializationContext->document, $serializationContext);
        $messageString = $serializationContext->document->saveXML();

        $this->dispatchSend($messageString);

        $messageString = base64_decode($messageString);

        $type = $message instanceof AbstractRequest ? 'SAMLRequest' : 'SAMLResponse';

        $data = [$type => $messageString];

        if (null !== $message->relayState) {
            $data['RelayState'] = $message->relayState;
        }

        $response = new SamlPostResponse($destination, $data);

        return $response->render();
    }

    #[Override]
    public function receive(ServerRequestInterface $request, MessageContext $context): void
    {
        $post = (array)$request->getParsedBody();
        if (array_key_exists('SAMLRequest', $post)) {
            $msg = $post['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $post)) {
            $msg = $post['SAMLResponse'];
        } else {
            throw new SamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }

        $msg = base64_decode($msg, true);

        $msg_decoded = @gzinflate($msg);

        if ($msg_decoded === false) {
            $msg_decoded = $msg;
        }

        $this->dispatchReceive($msg_decoded);

        $deserializationContext = $context->deserializationContext();
        $result = SamlMessage::fromXML($msg_decoded, $deserializationContext);

        if (array_key_exists('RelayState', $post)) {
            $result->relayState = $post['RelayState'];
        }

        $context->message = $result;
    }
}
