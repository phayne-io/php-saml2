<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Signature;

use LogicException;
use Override;
use Phayne\Saml\Context\Profile\AbstractProfileContext;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Credential\Criteria\EntityIdCriteria;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Credential\Criteria\UsageCriteria;
use Phayne\Saml\Credential\Criteria\X509CredentialCriteria;
use Phayne\Saml\Credential\UsageType;
use Phayne\Saml\Credential\X509CredentialInterface;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\XmlDSig\SignatureWriter;
use Phayne\Saml\Resolver\Credential\CredentialResolverInterface;
use Phayne\Saml\SamlConstant;

use function sprintf;

/**
 * Class OwnSignatureResolver
 *
 * @package Phayne\Saml\Resolver\Signature
 */
class OwnSignatureResolver implements SignatureResolverInterface
{
    public function __construct(protected CredentialResolverInterface $credentialResolver)
    {
    }

    #[Override]
    public function signature(AbstractProfileContext $context): ?SignatureWriter
    {
        $credential = $this->signingCredential($context);

        if (null === $credential) {
            throw new SamlContextException($context, 'Unable to find signing credential.');
        }

        $trustOptions = $context->profileContext()->trustOptions();

        return new SignatureWriter($credential->certificate, $credential->privateKey, $trustOptions->signatureDigestAlgorithm);
    }

    private function signingCredential(AbstractProfileContext $context): ?X509CredentialInterface
    {
        $profileContext = $context->profileContext();
        $entityDescriptor = $profileContext->ownEntityDescriptor();
        $query = $this->credentialResolver->query();
        $query->add(new EntityIdCriteria($entityDescriptor->entityID))
            ->add(new UsageCriteria(UsageType::SIGNING->value))
            ->add(new X509CredentialCriteria())
            ->addIf(ProfileContext::ROLE_IDP === $profileContext->ownRole, fn () => new MetadataCriteria(MetadataCriteria::TYPE_IDP, SamlConstant::VERSION_20))
            ->addIf(ProfileContext::ROLE_SP === $profileContext->ownRole, fn () => new MetadataCriteria(MetadataCriteria::TYPE_SP, SamlConstant::VERSION_20));
        $query->resolve();

        $result = $query->firstCredential();

        if ($result && false === $result instanceof X509CredentialInterface) {
            throw new LogicException(sprintf('Expected X509CredentialInterface but got %s', $result::class));
        }

        return $result;
    }
}
