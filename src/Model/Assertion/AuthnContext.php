<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Assertion;

use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class AuthnContext
 *
 * @package Phayne\Saml\Model\Assertion
 */
class AuthnContext extends AbstractSamlModel
{
    protected(set) ?string $authnContextClassRef = null;

    protected(set) ?string $authnContextDecl = null;

    protected(set) ?string $authnContextDeclRef = null;

    protected(set) ?string $authenticatingAuthority = null;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('AuthnContext', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->singleElementsToXml(
            ['AuthnContextClassRef', 'AuthnContextDecl', 'AuthnContextDeclRef', 'AuthenticatingAuthority'],
            $result,
            $context,
            SamlConstant::NS_ASSERTION
        );
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnContext', SamlConstant::NS_ASSERTION);
        $this->singleElementsFromXml($node, $context, [
            'AuthnContextClassRef' => ['saml', null],
            'AuthnContextDecl' => ['saml', null],
            'AuthnContextDeclRef' => ['saml', null],
            'AuthenticatingAuthority' => ['saml', null],
        ]);
    }
}
