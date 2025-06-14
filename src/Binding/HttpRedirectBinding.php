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

use Laminas\Diactoros\Response\RedirectResponse;
use Override;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\MessageContext;
use Phayne\Saml\Exception\SamlBindingException;
use Phayne\Saml\Model\Protocol\AbstractRequest;
use Phayne\Saml\Model\Protocol\SamlMessage;
use Phayne\Saml\Model\XmlDSig\SignatureStringReader;
use Phayne\Saml\Model\XmlDSig\SignatureWriter;
use Phayne\Saml\SamlConstant;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use function array_key_exists;
use function base64_decode;
use function base64_encode;
use function gzdeflate;
use function gzinflate;
use function sprintf;
use function str_contains;
use function urlencode;

/**
 * Class HttpRedirectBinding
 *
 * @package Phayne\Saml\Binding
 */
class HttpRedirectBinding extends AbstractBinding
{
    #[Override]
    public function send(MessageContext $context, ?string $destination = null): ResponseInterface
    {
        $destination = $context->message?->destination ?: $destination;
        $url = $this->redirectUrl($context, $destination);
        return new RedirectResponse($url);
    }

    #[Override]
    public function receive(ServerRequestInterface $request, MessageContext $context): void
    {
        $data = $this->parseQuery($request);
        $this->processData($data, $context);
    }

    protected function processData(array $data, MessageContext $context): void
    {
        $msg = $this->messageStringFromData($data);
        $encoding = $this->encodingFromData($data);
        $msg = $this->decodeMessageString($msg, $encoding);

        $this->dispatchReceive($msg);

        $deserializationContext = $context->deserializationContext();
        $message = SamlMessage::fromXML($msg, $deserializationContext);

        $this->loadRelayState($message, $data);
        $this->loadSignature($message, $data);

        $context->message = $message;
    }

    protected function messageStringFromData(array $data): string
    {
        if (array_key_exists('SAMLRequest', $data)) {
            return $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            return $data['SAMLResponse'];
        } else {
            throw new SamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }
    }

    protected function encodingFromData(array $data): string
    {
        return array_key_exists('SAMLEncoding', $data)
            ? $data['SAMLEncoding']
            : SamlConstant::ENCODING_DEFLATE->value;
    }

    protected function decodeMessageString(string $message, string $encoding): string
    {
        return match ($encoding) {
            SamlConstant::ENCODING_DEFLATE->value => @gzinflate(base64_decode($message, true)),
            default => throw new SamlBindingException(sprintf("Unknown encoding '%s'", $encoding)),
        };
    }

    protected function loadRelayState(SamlMessage $message, array $data): void
    {
        if (array_key_exists('RelayState', $data)) {
            $message->relayState = $data['RelayState'];
        }
    }

    protected function loadSignature(SamlMessage $message, array $data): void
    {
        if (array_key_exists('Signature', $data)) {
            if (false === array_key_exists('SigAlg', $data)) {
                throw new SamlBindingException('Missing signature algorithm');
            }
            $message->signature = new SignatureStringReader($data['Signature'], $data['SigAlg'], $data['SignedQuery']);
        }
    }

    protected function redirectUrl(MessageContext $context, ?string $destination): string
    {
        $message = MessageContextHelper::asSamlMessage($context);
        $signature = $message->signature;

        if ($signature && false === $signature instanceof SignatureWriter) {
            throw new SamlBindingException('Signature must be SignatureWriter');
        }

        $xml = $this->messageEncodedXml($message, $context);
        $msg = $this->addMessageToUrl($message, $xml);
        $this->addRelayStateToUrl($msg, $message);
        $this->addSignatureToUrl($msg, $signature);

        return $this->destinationUrl($msg, $message, $destination);
    }

    protected function messageEncodedXml(SamlMessage $message, MessageContext $context): string
    {
        $message->signature = null;

        $serializationContext = $context->serializationContext();
        $message->serialize($serializationContext->document, $serializationContext);
        $xml = $serializationContext->document->saveXML();

        $this->dispatchSend($xml);
        $xml = gzdeflate($xml);

        return base64_encode($xml);
    }

    protected function addMessageToUrl(SamlMessage $message, string $xml): string
    {
        $msg = $message instanceof AbstractRequest ? 'SAMLRequest=' : 'SAMLResponse=';
        return $msg . urlencode($xml);
    }

    protected function addRelayStateToUrl(string &$msg, SamlMessage $message): void
    {
        if (null !== $message->relayState) {
            $msg .= '&RelayState=' . urlencode($message->relayState);
        }
    }

    protected function addSignatureToUrl(string &$msg, ?SignatureWriter $signature = null): void
    {
        /** @var $key XMLSecurityKey */
        $key = $signature instanceof SignatureWriter ? $signature->xmlSecurityKey : null;

        if (null !== $key) {
            $msg .= '&SigAlg=' . urlencode($key->type);
            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }
    }

    protected function destinationUrl(string $msg, SamlMessage $message, ?string $destination): string
    {
        $destination = $message->destination ?: $destination;
        $destination .= false === str_contains($destination, '?')
            ? '?' . $msg
            : '&' . $msg;

        return $destination;
    }

    protected function parseQuery(ServerRequestInterface $request): array
    {
        $sigQuery = $relayState = $sigAlg = '';
        $data = $request->getQueryParams();
        $result = [];
        foreach ($data as $name => $value) {
            $result[$name] = urldecode($value);
            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name . '=' . $value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState=' . $value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg=' . $value;
                    break;
            }
        }
        $result['SignedQuery'] = $sigQuery . $relayState . $sigAlg;

        return $result;
    }
}
