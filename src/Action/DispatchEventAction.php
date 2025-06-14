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
use Phayne\Saml\Event\ActionOccurred;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class DispatchEventAction
 *
 * @package Phayne\Saml\Action
 */
readonly class DispatchEventAction implements ActionInterface
{
    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        $this->eventDispatcher->dispatch(new ActionOccurred($context));
    }
}
