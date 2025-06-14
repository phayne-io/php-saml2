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

/**
 * Class WrappedAction
 *
 * @package Phayne\Saml\Action
 */
abstract class WrappedAction implements ActionInterface
{
    public function __construct(protected ActionInterface $action)
    {
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        $this->beforeAction($context);
        $this->action->execute($context);
        $this->afterAction($context);
    }

    abstract protected function beforeAction(ContextInterface $context): void;

    abstract protected function afterAction(ContextInterface $context): void;
}
