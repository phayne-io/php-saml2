<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Outbound\AuthnRequest;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Metadata\AssertionConsumerService;
use Phayne\Saml\Model\Metadata\EndpointReference;
use Phayne\Saml\Model\Metadata\SpSsoDescriptor;
use Phayne\Saml\Resolver\Endpoint\Criteria\BindingCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\EndpointResolverInterface;
use Phayne\Saml\SamlConstant;
use Psr\Log\LoggerInterface;

/**
 * Class ACSUrlAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\AuthnRequest
 */
class ACSUrlAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private readonly EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $ownEntityDescriptor = $context->ownEntityDescriptor();

        $criteriaSet = new CriteriaSet([
            new DescriptorTypeCriteria(SpSsoDescriptor::class),
            new ServiceTypeCriteria(AssertionConsumerService::class),
            new BindingCriteria([SamlConstant::BINDING_SAML2_HTTP_POST->value]),
        ]);

        $endpoints = $this->endpointResolver->resolve($criteriaSet, $ownEntityDescriptor->endpoints());

        if (empty($endpoints)) {
            $message = 'Missing ACS Service with HTTP POST binding in own SP SSO Descriptor';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        /** @var EndpointReference $endpoint */
        $endpointReference = current($endpoints);

        MessageContextHelper::asAuthnRequest(
            $context->outboundContext()
        )->assertionConsumerServiceURL = $endpointReference->endpoint->location;
    }
}
