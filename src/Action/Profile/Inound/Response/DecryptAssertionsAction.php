<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Response;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\Criteria\EntityIdCriteria;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Credential\Criteria\UsageCriteria;
use Phayne\Saml\Credential\UsageType;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Assertion\EncryptedAssertionReader;
use Phayne\Saml\Model\Context\DeserializationContext;
use Phayne\Saml\Resolver\Credential\CredentialResolverInterface;
use Phayne\Saml\SamlConstant;
use Psr\Log\LoggerInterface;

use function array_map;
use function count;
use function sprintf;

/**
 * Class DecryptAssertionsAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Response
 */
class DecryptAssertionsAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected CredentialResolverInterface $credentialResolver)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->inboundContext());

        if (0 === count($response->encryptedAssertions)) {
            $this->logger->debug('Response has no encrypted assertions.', LogHelper::actionContext($context, $this));
            return;
        }

        $ownEntityDescriptor = $context->ownEntityDescriptor();
        $query = $this->credentialResolver->query();
        $query
            ->add(new EntityIdCriteria($ownEntityDescriptor->entityID))
            ->add(new MetadataCriteria(
                ProfileContext::ROLE_IDP === $context->ownRole
                    ? MetadataCriteria::TYPE_IDP
                    : MetadataCriteria::TYPE_SP,
                SamlConstant::PROTOCOL_SAML2
            ))
            ->add(new UsageCriteria(UsageType::ENCRYPTION->value));
        $query->resolve();

        $privateKeys = $query->privateKeys();

        if (empty($privateKeys)) {
            $message = 'No credentials resolved for assertion decryption.';
            $this->logger->emergency($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlContextException($context, $message);
        }

        $this->logger->info('Trusted decryption candidates', LogHelper::actionContext($context, $this, [
            'credentials' => array_map(
                fn (CredentialInterface $credential) => sprintf(
                    'Entity: "%s"; PK 509 Thumb : "%s"',
                    $credential->entityId,
                    $credential->publicKey
                        ? $credential->publicKey->getX509Thumbprint()
                        : ''
                ),
                $privateKeys),
        ]));

        foreach ($response->encryptedAssertions as $index => $encryptedAssertion) {
            if ($encryptedAssertion instanceof EncryptedAssertionReader) {
                $name = sprintf('assertion_encrypted_%s', $index);
                /** @var DeserializationContext $deserializationContext */
                $deserializationContext = $context->inboundContext()->subContext($name, DeserializationContext::class);
                $assertion = $encryptedAssertion->decryptMultiAssertion($privateKeys, $deserializationContext);
                $response->addAssertion($assertion);
                $this->logger->info(
                    'Assertion decrypted',
                    LogHelper::actionContext($context, $this, [
                        'assertion' => $deserializationContext->document->saveXML(),
                    ])
                );
            }
        }
    }
}
