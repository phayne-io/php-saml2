<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile;

use Override;
use Phayne\Saml\Action\ActionInterface;
use Phayne\Saml\Context\ContextInterface;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;
use Psr\Log\LoggerInterface;

use function compact;
use function sprintf;

/**
 * Class AbstractProfileAction
 *
 * @package Phayne\Saml\Action\Profile
 */
abstract class AbstractProfileAction implements ActionInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        if ($context instanceof ProfileContext) {
            $this->doExecute($context);
        } else {
            $message = sprintf('Expected ProfileContext, but got %s', $context::class);
            $this->logger->emergency($message, compact('context'));
            throw new SamlContextException($context, $message);
        }
    }

    abstract protected function doExecute(ProfileContext $context): void;
}
