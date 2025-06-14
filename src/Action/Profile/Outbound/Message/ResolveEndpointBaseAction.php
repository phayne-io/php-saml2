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

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Metadata\EndpointReference;
use Phayne\Saml\Model\Metadata\IdpSsoDescriptor;
use Phayne\Saml\Model\Metadata\SpSsoDescriptor;
use Phayne\Saml\Model\Protocol\AuthnRequest;
use Phayne\Saml\Resolver\Endpoint\Criteria\BindingCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\IndexCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\LocationCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\EndpointResolverInterface;
use Phayne\Saml\SamlConstant;
use Psr\Log\LoggerInterface;

use function array_shift;
use function sprintf;

/**
 * Class ResolveEndpointBaseAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
abstract class ResolveEndpointBaseAction extends AbstractProfileAction
{
    private static array $BINDINGS = [
        SamlConstant::BINDING_SAML2_HTTP_POST,
        SamlConstant::BINDING_SAML2_HTTP_REDIRECT,
    ];

    public function __construct(LoggerInterface $logger, protected EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        if (null !== $context->endpointContext()->endpoint) {
            $this->logger->debug(
                sprintf(
                    'Endpoint already set with location "%s" and binding "%s"',
                    $context->endpoint()->location,
                    $context->endpoint()->binding,
                ),
                LogHelper::actionContext($context, $this, [
                    'endpointLocation' => $context->endpoint()->location,
                    'endpointBinding' => $context->endpoint()->binding,
                ])
            );

            return;
        }

        $criteriaSet = $this->buildCriteriaSet($context);
        $message = $context->inboundContext()->message;

        if ($message instanceof AuthnRequest) {
            if (null !== $message->assertionConsumerServiceIndex) {
                $criteriaSet->add(new IndexCriteria((string)$message->assertionConsumerServiceIndex));
            }
            if (null !== $message->assertionConsumerServiceURL) {
                $criteriaSet->add(new LocationCriteria($message->assertionConsumerServiceURL));
            }
        }

        $candidates = $this->endpointResolver->resolve($criteriaSet, $context->partyEntityDescriptor()->endpoints());
        /** @var EndpointReference|null $endpointReference */
        $endpointReference = array_shift($candidates);

        if (null === $endpointReference) {
            $message = sprintf(
                "Unable to determine endpoint for entity '%s'",
                $context->partyEntityDescriptor()->entityID
            );
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        $this->logger->debug(
            sprintf(
                'Endpoint resolved to location "%s" and binding "%s"',
                $endpointReference->endpoint?->location,
                $endpointReference->endpoint?->binding
            ),
            LogHelper::actionContext($context, $this, [
                'endpointLocation' => $endpointReference->endpoint?->location,
                'endpointBinding' => $endpointReference->endpoint?->binding,
            ])
        );

        $context->endpointContext()->endpoint = $endpointReference->endpoint;
    }

    protected function buildCriteriaSet(ProfileContext $context): CriteriaSet
    {
        $criteriaSet = new CriteriaSet();
        $criteriaSet->add(new BindingCriteria([
            SamlConstant::BINDING_SAML2_HTTP_POST->value,
            SamlConstant::BINDING_SAML2_HTTP_REDIRECT->value
        ]));
        $criteriaSet->add(new DescriptorTypeCriteria($this->retrieveDescriptorType($context)));
        $criteriaSet->add(new ServiceTypeCriteria($this->serviceType($context)));

        return $criteriaSet;
    }

    protected function retrieveDescriptorType(ProfileContext $context): string
    {
        return ProfileContext::ROLE_IDP === $context->ownRole
            ? SpSsoDescriptor::class
            : IdpSsoDescriptor::class;
    }

    abstract protected function serviceType(ProfileContext $context): string;
}
