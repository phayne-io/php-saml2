<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Signature;

use Override;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\Criteria\EntityIdCriteria;
use Phayne\Saml\Credential\Criteria\MetadataCriteria;
use Phayne\Saml\Credential\Criteria\PublicKeyThumbprintCriteria;
use Phayne\Saml\Credential\Criteria\UsageCriteria;
use Phayne\Saml\Credential\UsageType;
use Phayne\Saml\Exception\SamlSecurityException;
use Phayne\Saml\Model\XmlDSig\AbstractSignatureReader;
use Phayne\Saml\Resolver\Credential\CredentialResolverInterface;
use Phayne\Saml\SamlConstant;

/**
 * Class SignatureValidator
 *
 * @package Phayne\Saml\Validator\Model\Signature
 */
class SignatureValidator implements SignatureValidatorInterface
{
    public function __construct(protected CredentialResolverInterface $credentialResolver)
    {
    }

    #[Override]
    public function validate(
        AbstractSignatureReader $signature,
        string $issuer,
        string $metadataType
    ): ?CredentialInterface {
        $query = $this->credentialResolver->query();
        $query
            ->add(new EntityIdCriteria($issuer))
            ->add(new MetadataCriteria($metadataType, SamlConstant::VERSION_20))
            ->add(new UsageCriteria(UsageType::SIGNING->value));

        if (null !== $signature->key && $signature->key->getX509Thumbprint()) {
            $query->add(new PublicKeyThumbprintCriteria($signature->key->getX509Thumbprint()));
        }

        $query->resolve();

        $credentialCandidates = $query->credentials;

        if (empty($credentialCandidates)) {
            throw new SamlSecurityException('No credentials resolved for signature verification.');
        }

        return $signature->validateMulti($credentialCandidates);
    }
}
