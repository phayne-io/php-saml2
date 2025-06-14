<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Response;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Resolver\Session\SessionProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SpSsoStateAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Response
 */
class SpSsoStateAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private readonly SessionProcessorInterface $sessionProcessor)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->inboundContext());

        $this->sessionProcessor->processAssertions(
            $response->assertions,
            $context->ownEntityDescriptor()->entityID,
            $context->partyEntityDescriptor()->entityID
        );
    }
}
