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

use Phayne\Saml\Model\Context\DeserializationContext;
use Phayne\Saml\Model\Context\SerializationContext;
use Phayne\Saml\Model\Protocol\AuthnRequest;
use Phayne\Saml\Model\Protocol\LogoutRequest;
use Phayne\Saml\Model\Protocol\LogoutResponse;
use Phayne\Saml\Model\Protocol\Response;
use Phayne\Saml\Model\Protocol\SamlMessage;

/**
 * Class MessageContext
 *
 * @package Phayne\Saml\Context\Profile
 */
class MessageContext extends AbstractProfileContext
{
    public string $bindingType;

    public ?SamlMessage $message = null;

    public function asAuthnRequest(): ?AuthnRequest
    {
        return $this->message instanceof AuthnRequest
            ? $this->message
            : null;
    }

    public function asLogoutRequest(): ?LogoutRequest
    {
        return $this->message instanceof LogoutRequest
            ? $this->message
            : null;
    }

    public function asResponse(): ?Response
    {
        return $this->message instanceof Response
            ? $this->message
            : null;
    }

    public function asLogoutResponse(): ?LogoutResponse
    {
        return $this->message instanceof LogoutResponse
            ? $this->message
            : null;
    }

    public function serializationContext(): SerializationContext
    {
        return $this->subContext(ProfileContexts::SERIALIZATION, SerializationContext::class);
    }

    public function deserializationContext(): DeserializationContext
    {
        return $this->subContext(ProfileContexts::DESERIALIZATION, DeserializationContext::class);
    }
}
