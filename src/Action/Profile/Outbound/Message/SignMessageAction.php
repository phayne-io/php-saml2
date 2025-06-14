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

use LogicException;
use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Model\Protocol\AuthnRequest;
use Phayne\Saml\Model\Protocol\LogoutRequest;
use Phayne\Saml\Model\Protocol\Response;
use Phayne\Saml\Resolver\Signature\SignatureResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SignMessageAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class SignMessageAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected SignatureResolverInterface $signatureResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        if ($this->shouldSignMessage($context)) {
            $signature = $this->signatureResolver->signature($context);

            if (null !== $signature) {
                MessageContextHelper::asSamlMessage($context->outboundContext())->signature = $signature;
                $this->logger->debug(
                    sprintf('Message signed with fingerprint "%s"', $signature->certificate?->fingerPrint()),
                    LogHelper::actionContext($context, $this, ['certificate' => $signature->certificate?->info()])
                );
            } else {
                $this->logger->critical(
                    'No signature resolved, although signing enabled',
                    LogHelper::actionErrorContext($context, $this, [])
                );
            }
        } else {
            $this->logger->debug('Signing disabled', LogHelper::actionContext($context, $this));
        }
    }

    private function shouldSignMessage(ProfileContext $context): bool
    {
        $message = $context->outboundMessage();

        if ($message instanceof LogoutRequest) {
            return true;
        }

        $trustOptions = $context->trustOptions();

        if ($message instanceof AuthnRequest) {
            return $trustOptions->signAuthnRequest;
        } elseif ($message instanceof Response) {
            return $trustOptions->signResponse;
        }

        throw new LogicException(sprintf('Unexpected message type "%s"', $message::class));
    }
}
