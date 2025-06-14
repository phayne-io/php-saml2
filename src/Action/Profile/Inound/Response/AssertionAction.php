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
use Phayne\Saml\Action\ActionInterface;
use Phayne\Saml\Action\DebugPrintTreeActionInterface;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

use function array_merge;
use function sprintf;

/**
 * Class AssertionAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Response
 */
class AssertionAction extends AbstractProfileAction implements DebugPrintTreeActionInterface
{
    public function __construct(LoggerInterface $logger, private readonly ActionInterface $assertionAction)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->inboundContext());

        foreach ($response->assertions as $index => $assertion) {
            $name = sprintf('assertion_%s', $index);
            /** @var AssertionContext $assertionContext */
            $assertionContext = $context->subContext($name, AssertionContext::class);
            $assertionContext->assertion = $assertion;
            $assertionContext->id = $name;

            $this->assertionAction->execute($assertionContext);
        }
    }

    #[Override]
    public function debugPrintTree(): array
    {
        $tree = [];

        if ($this->assertionAction instanceof DebugPrintTreeActionInterface) {
            $tree = array_merge($tree, $this->assertionAction->debugPrintTree());
        } else {
            $tree[$this->assertionAction::class] = [];
        }

        return [
            static::class => $tree
        ];
    }
}
