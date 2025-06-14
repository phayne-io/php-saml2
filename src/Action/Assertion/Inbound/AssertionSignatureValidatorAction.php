<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Assertion\Inbound;

use Override;
use Phayne\Saml\Action\Assertion\AbstractAssertionAction;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Exception\SamlModelException;
use Phayne\Saml\Model\XmlDSig\AbstractSignatureReader;
use Phayne\Saml\Validator\Model\Signature\SignatureValidatorInterface;
use Psr\Log\LoggerInterface;

use function compact;
use function implode;
use function sprintf;

/**
 * Class AssertionSignatureValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class AssertionSignatureValidatorAction extends AbstractAssertionAction
{
    public function __construct(
        LoggerInterface $logger,
        protected SignatureValidatorInterface $signatureValidator,
        protected bool $requireSignature = true
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        $signature = $context->assertion?->signature;

        if (null === $signature) {
            if (true === $this->requireSignature) {
                $message = 'Assertions must be signed';
                $this->logger->critical($message, LogHelper::actionErrorContext($context, $this));
                throw new SamlContextException($context, $message);
            } else {
                $this->logger->debug('Assertion is not be signed', LogHelper::actionContext($context, $this));
                return;
            }
        }

        if ($signature instanceof AbstractSignatureReader) {
            $metadataType = ProfileContext::ROLE_IDP === $context->profileContext()->ownRole
                ? MetadataCriteria::TYPE_SP
                : MetadataCriteria::TYPE_IDP;
            $credential = $this->signatureValidator->validate(
                $signature,
                $context->assertion->issuer->value,
                $metadataType
            );

            if (null !== $credential) {
                $keyNames = $credential->keyNames;
                $this->logger->debug(
                    sprintf('Assertion signature validated with key "%s"', implode(', ', $keyNames)),
                    LogHelper::actionContext($context, $this, compact('credential'))
                );
            } else {
                $this->logger->warning('Assertion signature was not performed', LogHelper::actionContext($context, $this));
            }
        } else {
            $message = 'Excepted AbstractSignatureReader';
            $this->logger->critical($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlModelException($message);
        }
    }
}
