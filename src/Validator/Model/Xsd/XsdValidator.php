<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Xsd;

use LiteSaml\Error;
use LiteSaml\Schema;
use LiteSaml\UnexpectedSchemaException;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Exception\XsdError;

/**
 * Class XsdValidator
 *
 * @package Phayne\Saml\Validator\Model\Xsd
 */
class XsdValidator
{
    public function validateProtocol(string $xml): array
    {
        return $this->validate($xml, 'saml-schema-protocol-2.0.xsd');
    }

    /**
     * @param string $xml
     * @return XsdError[]
     */
    public function validateMetadata(string $xml): array
    {
        return $this->validate($xml, 'saml-schema-metadata-2.0.xsd');
    }

    /**
     * @param string $xml
     * @param string $schema
     * @return XsdError[]
     */
    private function validate(string $xml, string $schema): array
    {
        try {
            $errorBag = Schema::validate($xml, $schema);

            return array_map(function (Error $error) {
                $level = match ($error->level) {
                    LIBXML_ERR_FATAL => XsdError::FATAL,
                    LIBXML_ERR_ERROR => XsdError::ERROR,
                    LIBXML_ERR_WARNING => XsdError::WARNING,
                    default => 'Unknown',
                };

                return new XsdError($level, $error->code, $error->message, $error->line, $error->column);
            }, $errorBag->getErrors());
        } catch (UnexpectedSchemaException $e) {
            throw new SamlXmlException($e->getMessage());
        }
    }
}
