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
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Exception\SamlModelException;
use Phayne\Saml\Model\XmlDSig\AbstractSignatureReader;
use Phayne\Saml\Validator\Model\Signature\SignatureValidatorInterface;
use Psr\Log\LoggerInterface;

use function compact;
use function implode;
use function sprintf;

/**
 * Class MessageSignatureValidatorAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class MessageSignatureValidatorAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected SignatureValidatorInterface $signatureValidator)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $message = MessageContextHelper::asSamlMessage($context->inboundContext());

        $signature = $message->signature;

        if (null === $signature) {
            $this->logger->debug('Message is not signed', LogHelper::actionContext($context, $this));
            return;
        }

        if ($signature instanceof AbstractSignatureReader) {
            $metadataType = ProfileContext::ROLE_IDP === $context->ownRole
                ? MetadataCriteria::TYPE_SP
                : MetadataCriteria::TYPE_IDP;
            $credential = $this->signatureValidator->validate($signature, $message->issuer->value, $metadataType);

            if (null !== $credential) {
                $this->logger->debug(
                    sprintf('Message signature validated with key "%s"', implode(', ', $credential->keyNames)),
                    LogHelper::actionContext($context, $this, compact('credential'))
                );
            } else {
                $this->logger->warning(
                    'Signature verification was not performed',
                    LogHelper::actionContext($context, $this)
                );
            }
        } else {
            $message = 'Expected AbstractSignatureReader';
            $this->logger->critical($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlModelException($message);
        }
    }
}
