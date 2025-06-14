<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action;

use Override;
use Phayne\Saml\Context\ContextInterface;
use Psr\Log\LoggerInterface;

use function sprintf;

/**
 * Class LoggableAction
 *
 * @package Phayne\Saml\Action
 */
class LoggableAction extends WrappedAction
{
    public function __construct(ActionInterface $action, private readonly LoggerInterface $logger)
    {
        parent::__construct($action);
    }

    #[Override]
    protected function beforeAction(ContextInterface $context): void
    {
        $this->logger->debug(sprintf('Executing action "%s"', $this->action::class), [
            'context' => $context,
            'action' => $this->action,
        ]);
    }

    #[Override]
    protected function afterAction(ContextInterface $context): void
    {
    }
}
