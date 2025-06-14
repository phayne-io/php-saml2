<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Entity;

use Laminas\Diactoros\Response\XmlResponse;
use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Context\Profile\ProfileContexts;
use Phayne\Saml\Model\Context\SerializationContext;

/**
 * Class SerializeOwnEntityAction
 *
 * @package Phayne\Saml\Action\Profile\Entity
 */
class SerializeOwnEntityAction extends AbstractProfileAction
{
    protected array $supportedContextTypes = ['application/samlmetadata+xml', 'application/xml', 'text/xml'];

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $ownEntityDescriptor = $context->ownEntityDescriptor();

        /** @var SerializationContext $serializationContext */
        $serializationContext = $context->subContext(ProfileContexts::SERIALIZATION, SerializationContext::class);
        $serializationContext->document->formatOutput = true;

        $ownEntityDescriptor->serialize($serializationContext->document, $serializationContext);

        $xml = $serializationContext->document->saveXML();

        $response = new XmlResponse($xml);

        $contentType = 'text/xml';
        $acceptableContentTypes = $context->httpRequest()->getHeader('Content-Type');

        foreach ($this->supportedContextTypes as $supportedContentType) {
            if (in_array($supportedContentType, $acceptableContentTypes, true)) {
                $contentType = $supportedContentType;
            }
        }

        $context->httpResponseContext()->response = $response->withHeader('Content-Type', $contentType);
    }
}
