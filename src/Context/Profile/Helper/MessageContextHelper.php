<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile\Helper;

use Phayne\Saml\Context\Profile\MessageContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Protocol\AbstractRequest;
use Phayne\Saml\Model\Protocol\AuthnRequest;
use Phayne\Saml\Model\Protocol\LogoutRequest;
use Phayne\Saml\Model\Protocol\LogoutResponse;
use Phayne\Saml\Model\Protocol\Response;
use Phayne\Saml\Model\Protocol\SamlMessage;
use Phayne\Saml\Model\Protocol\StatusResponse;

/**
 * Class MessageContextHelper
 *
 * @package Phayne\Saml\Context\Profile\Helper
 */
abstract class MessageContextHelper
{
    public static function asSamlMessage(MessageContext $context): SamlMessage
    {
        $message = $context->message;

        if ($message) {
            return $message;
        }

        throw new SamlContextException($context, 'Missing SamlMessage');
    }

    public static function asAuthnRequest(MessageContext $context): AuthnRequest
    {
        $message = $context->message;

        if ($message instanceof AuthnRequest) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected AuthnRequest');
    }

    public static function asAbstractRequest(MessageContext $context): AbstractRequest
    {
        $message = $context->message;

        if ($message instanceof AbstractRequest) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected AbstractRequest');
    }

    public static function asResponse(MessageContext $context): Response
    {
        $message = $context->message;

        if ($message instanceof Response) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected Response');
    }

    public static function asStatusResponse(MessageContext $context): StatusResponse
    {
        $message = $context->message;

        if ($message instanceof StatusResponse) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected StatusResponse');
    }

    public static function asLogoutRequest(MessageContext $context): LogoutRequest
    {
        $message = $context->message;

        if ($message instanceof LogoutRequest) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected LogoutRequest');
    }

    public static function asLogoutResponse(MessageContext $context): LogoutResponse
    {
        $message = $context->message;

        if ($message instanceof LogoutResponse) {
            return $message;
        }

        throw new SamlContextException($context, 'Expected LogoutResponse');
    }
}
