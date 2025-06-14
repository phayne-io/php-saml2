<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Message;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Binding\BindingFactoryInterface;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlBindingException;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class ReceiveMessageAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class ReceiveMessageAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected BindingFactoryInterface $bindingFactory)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $bindingType = $this->bindingFactory->detectBindingType($context->httpRequest());

        if (null === $bindingType) {
            $message = 'Unable to resolve binding type, invalid or unsupported http request';
            $this->logger->critical($message, LogHelper::actionErrorContext($context, $this));
            throw new SamlBindingException($message);
        }

        $this->logger->debug(
            sprintf('Detected binding type: %s', $bindingType->value),
            LogHelper::actionContext($context, $this)
        );

        $binding = $this->bindingFactory->create($bindingType);
        $binding->receive($context->httpRequest(), $context->inboundContext());
        $context->inboundContext()->bindingType = $bindingType->value;

        $this->logger->info(
            'Received message',
            LogHelper::actionContext($context, $this, [
                'message' => $context->inboundContext()->deserializationContext()->document->saveXML(),
            ])
        );
    }
}
