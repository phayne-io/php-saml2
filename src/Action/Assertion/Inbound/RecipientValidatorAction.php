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
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Assertion\SubjectConfirmation;
use Phayne\Saml\Model\Metadata\AssertionConsumerService;
use Phayne\Saml\Model\Metadata\SpSsoDescriptor;
use Phayne\Saml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\LocationCriteria;
use Phayne\Saml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use Phayne\Saml\Resolver\Endpoint\EndpointResolverInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class RecipientValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class RecipientValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, private readonly EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        if ($context->assertion?->authnStatements && $context->assertion?->hasBearerSubject()) {
            $this->validateBearerAssertion($context);
        }
    }

    protected function validateBearerAssertion(AssertionContext $context): void
    {
        foreach ($context->assertion?->subject?->bearerConfirmations() as $subjectConfirmation) {
            $this->validateSubjectConfirmation($context, $subjectConfirmation);
        }
    }

    protected function validateSubjectConfirmation(
        AssertionContext $context,
        SubjectConfirmation $subjectConfirmation
    ): void {
        $recipient = $subjectConfirmation->subjectConfirmationData?->recipient;

        if (null === $recipient) {
            $message = 'Bearer SubjectConfirmation must contain Recipient attribute';
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        $criteriaSet = new CriteriaSet([
            new DescriptorTypeCriteria(SpSsoDescriptor::class),
            new ServiceTypeCriteria(AssertionConsumerService::class),
            new LocationCriteria($recipient),
        ]);
        $ownEntityDescriptor = $context->profileContext()->ownEntityDescriptor();
        $arrEndpoints = $this->endpointResolver->resolve($criteriaSet, $ownEntityDescriptor->endpoints());

        if (empty($arrEndpoints)) {
            $message = sprintf("Recipient '%s' does not match SP descriptor", $recipient);
            $this->logger->error($message, LogHelper::actionErrorContext($context, $this, [
                'recipient' => $recipient,
            ]));
            throw new SamlContextException($context, $message);
        }
    }
}
