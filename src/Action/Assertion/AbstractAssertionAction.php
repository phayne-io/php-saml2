<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Assertion;

use Override;
use Phayne\Saml\Action\ActionInterface;
use Phayne\Saml\Context\ContextInterface;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Exception\SamlContextException;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractAssertionAction
 *
 * @package Phayne\Saml\Action\Assertion
 */
abstract class AbstractAssertionAction implements ActionInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        if ($context instanceof AssertionContext) {
            $this->doExecute($context);
        } else {
            throw new SamlContextException($context, 'Expected AssertionContext object');
        }
    }

    abstract protected function doExecute(AssertionContext $context): void;
}
