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
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Metadata\IdpSsoDescriptor;
use Phayne\Saml\Model\Metadata\SpSsoDescriptor;
use Phayne\Saml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\LocationCriteria;
use Phayne\Saml\Resolver\Endpoint\EndpointResolverInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class AbstractDestinationValidatorAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
abstract class AbstractDestinationValidatorAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $message = MessageContextHelper::asSamlMessage($context->inboundContext());
        $destination = $message->destination;

        if (null === $destination) {
            return;
        }

        $criteriaSet = $this->buildCriteriaSet($context, $destination);
        $endpoints = $this->endpointResolver->resolve($criteriaSet, $context->ownEntityDescriptor()->endpoints());

        if (! empty($endpoints)) {
            return;
        }

        $message = sprintf('Invalid inbound message destination "%s"', $destination);
        $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
        throw new SamlContextException($context, $message);
    }

    protected function buildCriteriaSet(ProfileContext $context, string $location): CriteriaSet
    {
        return new CriteriaSet([
            new DescriptorTypeCriteria(
                ProfileContext::ROLE_IDP === $context->ownRole
                    ? IdpSsoDescriptor::class
                    : SpSsoDescriptor::class
            ),
            new LocationCriteria($location),
        ]);
    }
}
