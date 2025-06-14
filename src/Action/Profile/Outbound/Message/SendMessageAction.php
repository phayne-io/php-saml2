<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Outbound\Message;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Binding\BindingFactoryInterface;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\SamlConstant;
use Psr\Log\LoggerInterface;

/**
 * Class SendMessageAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class SendMessageAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected BindingFactoryInterface $bindingFactory)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $binding = $this->bindingFactory->create(SamlConstant::tryFrom($context->endpoint()->binding));
        $outboundContext = $context->outboundContext();

        $context->httpResponseContext()->response = $binding->send($outboundContext);

        $this->logger->info(
            'Sending message',
            LogHelper::actionContext(
                $context,
                $this,
                ['message' => $outboundContext->serializationContext()->document->saveXML()]
            )
        );
    }
}
