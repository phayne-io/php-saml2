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
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Meta\TrustOptions\TrustOptions;
use Phayne\Saml\Model\Metadata\EntityDescriptor;
use Phayne\Saml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use Phayne\Saml\Store\TrustOptions\TrustOptionsStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ResolvePartyEntityIdAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class ResolvePartyEntityIdAction extends AbstractProfileAction
{
    public function __construct(
        LoggerInterface $logger,
        private readonly EntityDescriptorStoreInterface $spEntityDescriptorProvider,
        private readonly EntityDescriptorStoreInterface $idpEntityDescriptorProvider,
        protected TrustOptionsStoreInterface $trustOptionsProvider,
    ) {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $partyContext = $context->partyEntityContext();

        if (null !== $partyContext->entityDescriptor && null !== $partyContext->trustOptions) {
            $this->logger->debug(
                sprintf(
                    'Party EntityDescriptor and TrustOptions already set for "%s".',
                    $partyContext->entityDescriptor->entityID
                ),
                LogHelper::actionContext($context, $this, [
                    'partyEntityId' => $partyContext->entityDescriptor->entityID,
                ]),
            );

            return;
        }

        $entityId = $partyContext->entityDescriptor?->entityID;
        $entityId = $entityId ?: $partyContext->entityId;

        if (null === $entityId) {
            $message = 'Entity ID is not set in the party context.';
            $this->logger->critical($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        if (null === $partyContext->entityDescriptor) {
            $partyEntityDescriptor = $this->getPartyEntityDescriptor(
                $context,
                ProfileContext::ROLE_IDP === $context->ownRole
                    ? $this->spEntityDescriptorProvider
                    : $this->idpEntityDescriptorProvider,
                $context->partyEntityContext()->entityId
            );
            $partyContext->entityDescriptor = $partyEntityDescriptor;
            $this->logger->debug(
                sprintf('Known issuer resolved: "%s"', $partyEntityDescriptor->entityID),
                LogHelper::actionContext($context, $this, [
                    'partyEntityId' => $partyEntityDescriptor->entityID,
                ])
            );
        }

        if (null === $partyContext->trustOptions) {
            $trustOptions = $this->trustOptionsProvider->get($partyContext->entityDescriptor->entityID);

            if (null === $trustOptions) {
                $trustOptions = new TrustOptions();
            }
            $partyContext->trustOptions = $trustOptions;
        }
    }

    protected function getPartyEntityDescriptor(
        ProfileContext $context,
        EntityDescriptorStoreInterface $entityDescriptorProvider,
        string $entityId,
    ): EntityDescriptor {
        $partyEntityDescriptor = $entityDescriptorProvider->get($entityId);

        if (null === $partyEntityDescriptor) {
            $message = sprintf('Unknown issuer "%s"', $entityId);
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        return $partyEntityDescriptor;
    }
}
