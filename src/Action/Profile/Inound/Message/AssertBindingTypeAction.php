<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Profile\Inound\Message;

use Override;
use Phayne\Saml\Action\Profile\AbstractProfileAction;
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;
use Psr\Log\LoggerInterface;

use function in_array;
use function sprintf;

/**
 * Class AssertBindingTypeAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Message
 */
class AssertBindingTypeAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected array $expectedBindingTypes)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        if (false === in_array($context->inboundContext()->bindingType, $this->expectedBindingTypes, true)) {
            $message = sprintf(
                'Unexpected binding type "%s" - expected one of "%s"',
                $context->inboundContext()->bindingType,
                implode(', ', $this->expectedBindingTypes)
            );
            $this->logger->critical($message, LogHelper::actionErrorContext($context, $this, [
                'actualBindingType' => $context->inboundContext()->bindingType,
                'expectedBindingTypes' => $this->expectedBindingTypes,
            ]));

            throw new SamlContextException($context, $message);
        }
    }
}
