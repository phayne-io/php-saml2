<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\XmlDSig;

use DOMElement;
use DOMNode;
use LogicException;
use Override;
use Phayne\Saml\Credential\X509Certificate;
use Phayne\Saml\Meta\SigningOptions;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class SignatureWriter
 *
 * @package Phayne\Saml\Model\XmlDSig
 */
class SignatureWriter extends Signature
{
    protected ?SigningOptions $signingOptions;

    protected string $canonicalMethod = XMLSecurityDSig::EXC_C14N;

    public static function create(SigningOptions $options): self
    {
        $self = new self();
        $self->signingOptions = $options;
        return $self;
    }

    public static function createByKeyAndCertificate(X509Certificate $certificate, XMLSecurityKey $xmlSecurityKey): self
    {
        $signingOptions = new SigningOptions($xmlSecurityKey, $certificate);

        return self::create($signingOptions);
    }

    public function __construct(
        protected(set) ?X509Certificate $certificate = null,
        protected(set) ?XMLSecurityKey $xmlSecurityKey = null,
        protected string $digestAlgorithm = XMLSecurityDSig::SHA1
    ) {
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        if (null !== $this->signingOptions && false === $this->signingOptions->enabled) {
            return;
        }

        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod($this->canonicalMethod);
        $key = $this->xmlSecurityKey;

        $objXMLSecDSig->addReferenceList(
            [$parent],
            $this->digestAlgorithm,
            [SamlConstant::XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE->value, XMLSecurityDSig::EXC_C14N],
            ['id_name' => $this->idName(), 'overwrite' => false]
        );
        $objXMLSecDSig->sign($key);

        $objXMLSecDSig->add509Cert(
            $this->certificate->data,
            false,
            false,
            $this->signingOptions?->certificateOptions->all()
        );

        $firstChild = $parent->hasChildNodes() ? $parent->firstChild : null;

        if ($firstChild !== null && 'Issuer' === $firstChild->nodeName) {
            $firstChild = $firstChild->nextSibling;
        }

        $objXMLSecDSig->insertSignature($parent, $firstChild);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        throw new LogicException('SignatureWriter can not be deserialized');
    }
}
