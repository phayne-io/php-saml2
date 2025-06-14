<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile;

use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Meta\TrustOptions\TrustOptions;
use Phayne\Saml\Model\Metadata\Endpoint;
use Phayne\Saml\Model\Metadata\EntityDescriptor;
use Phayne\Saml\Model\Protocol\SamlMessage;
use Phayne\Saml\State\Sso\SsoSessionState;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ProfileContext
 *
 * @package Phayne\Saml\Context\Profile
 */
class ProfileContext extends AbstractProfileContext
{
    public const string ROLE_SP = 'sp';
    public const string ROLE_IDP = 'idp';
    public const string ROLE_NONE = 'none';

    public ?string $relayState = null;

    public function __construct(public readonly string $profileId, public readonly string $ownRole)
    {
    }

    public function inboundContext(): MessageContext
    {
        return $this->subContext(ProfileContexts::INBOUND_MESSAGE, MessageContext::class);
    }

    public function outboundContext(): MessageContext
    {
        return $this->subContext(ProfileContexts::OUTBOUND_MESSAGE, MessageContext::class);
    }

    public function httpRequestContext(): HttpRequestContext
    {
        return $this->subContext(ProfileContexts::HTTP_REQUEST, HttpRequestContext::class);
    }

    public function httpResponseContext(): HttpResponseContext
    {
        return $this->subContext(ProfileContexts::HTTP_RESPONSE, HttpResponseContext::class);
    }

    public function ownEntityContext(): EntityContext
    {
        return $this->subContext(ProfileContexts::OWN_ENTITY, EntityContext::class);
    }

    public function partyEntityContext(): EntityContext
    {
        return $this->subContext(ProfileContexts::PARTY_ENTITY, EntityContext::class);
    }

    public function logoutContext(): LogoutContext
    {
        return $this->subContext(ProfileContexts::PARTY_ENTITY, LogoutContext::class);
    }

    public function endpointContext(): EndpointContext
    {
        return $this->subContext(ProfileContexts::ENDPOINT, EndpointContext::class);
    }

    public function httpRequest(): ServerRequestInterface
    {
        $httpRequestContext = $this->httpRequestContext();

        if (null === $httpRequestContext->request) {
            throw new SamlContextException($this, 'Missing request in HTTP request context');
        }

        return $httpRequestContext->request;
    }

    public function inboundMessage(): SamlMessage
    {
        $inboundContext = $this->inboundContext();

        if (null === $inboundContext->message) {
            throw new SamlContextException($this, 'Missing message in inbound context');
        }

        return $inboundContext->message;
    }

    public function outboundMessage(): SamlMessage
    {
        $outboundContext = $this->outboundContext();

        if (null === $outboundContext->message) {
            throw new SamlContextException($this, 'Missing message in outbound context');
        }

        return $outboundContext->message;
    }

    public function endpoint(): Endpoint
    {
        $endpointContext = $this->endpointContext();

        if (null === $endpointContext->endpoint) {
            throw new SamlContextException($this, 'Missing endpoint in endpoint context');
        }

        return $endpointContext->endpoint;
    }

    public function ownEntityDescriptor(): EntityDescriptor
    {
        $ownEntityContext = $this->ownEntityContext();

        if (null === $ownEntityContext->entityDescriptor) {
            throw new SamlContextException($this, 'Missing entity descriptor in own entity context');
        }

        return $ownEntityContext->entityDescriptor;
    }

    public function partyEntityDescriptor(): EntityDescriptor
    {
        $partyEntityContext = $this->partyEntityContext();

        if (null === $partyEntityContext->entityDescriptor) {
            throw new SamlContextException($this, 'Missing entity descriptor in party entity context');
        }

        return $partyEntityContext->entityDescriptor;
    }

    public function trustOptions(): TrustOptions
    {
        $partyEntityContext = $this->partyEntityContext();

        if (null === $partyEntityContext->trustOptions) {
            throw new SamlContextException($this, 'Missing trust options in trust context');
        }

        return $partyEntityContext->trustOptions;
    }

    public function logoutSsoSessionState(): SsoSessionState
    {
        $logoutContext = $this->logoutContext();

        if (null === $logoutContext->ssoSessionState) {
            throw new SamlContextException($this, 'Missing SSO session state in logout context');
        }

        return $logoutContext->ssoSessionState;
    }
}
