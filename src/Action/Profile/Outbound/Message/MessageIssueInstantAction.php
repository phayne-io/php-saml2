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
use Phayne\Saml\Provider\TimeProvider\TimeProviderInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class MessageIssueInstantAction
 *
 * @package Phayne\Saml\Action\Profile\Outbound\Message
 */
class MessageIssueInstantAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected TimeProviderInterface $timeProvider)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        MessageContextHelper::asSamlMessage($context->outboundContext())
            ->issueInstant = $this->timeProvider->timeStamp;
        $this->logger->info(
            sprintf(
                'Message issueInstant set to "%d"',
                MessageContextHelper::asSamlMessage($context->outboundContext())->issueInstant
            ),
            LogHelper::actionContext($context, $this)
        );
    }
}
