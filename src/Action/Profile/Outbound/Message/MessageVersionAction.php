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
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

/**
 * Class MessageVersionAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class MessageVersionAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private string $version)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        MessageContextHelper::asSamlMessage($context->outboundContext())->version = $this->version;

        $this->logger->debug(
            sprintf('Message Version set to "%s"', $this->version),
            LogHelper::actionContext($context, $this)
        );
    }
}
