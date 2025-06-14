<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Context;

use DOMDocument;
use DOMXPath;
use Phayne\Saml\SamlConstant;
use RobRichards\XMLSecLibs\XMLSecEnc;

/**
 * Class DeserializationContext
 *
 * @package Phayne\Saml\Model\Context
 */
final class DeserializationContext
{
    private(set) DOMXPath $xpath {
        get {
            if (! isset($this->xpath)) {
                $this->xpath = new DOMXPath($this->document);
                $this->xpath->registerNamespace('saml', SamlConstant::NS_ASSERTION->value);
                $this->xpath->registerNamespace('samlp', SamlConstant::PROTOCOL_SAML2->value);
                $this->xpath->registerNamespace('md', SamlConstant::NS_METADATA->value);
                $this->xpath->registerNamespace('ds', SamlConstant::NS_XMLDSIG->value);
                $this->xpath->registerNamespace('xenc', XMLSecEnc::XMLENCNS);
            }

            return $this->xpath;
        }
    }

    public function __construct(public DOMDocument $document = new DOMDocument())
    {
    }
}
