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
use Psr\Log\LoggerInterface;

/**
 * Class ActionLogWrapper
 *
 * @package Phayne\Saml\Action
 */
readonly class ActionLogWrapper implements ActionWrapperInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Override]
    public function wrap(ActionInterface $action): ActionInterface
    {
        return new LoggableAction($action, $this->logger);
    }
}
