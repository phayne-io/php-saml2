<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action\Assertion\Inbound;

use Override;
use Phayne\Saml\Action\Assertion\AbstractAssertionAction;
use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Validator\Model\Assertion\AssertionValidator;
use Psr\Log\LoggerInterface;

/**
 * Class AssertionValidatorAction
 *
 * @package Phayne\Saml\Action\Assertion\Inbound
 */
class AssertionValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected AssertionValidator $assertionValidator)
    {
        parent::__construct($logger);
    }

    #[Override]
    protected function doExecute(AssertionContext $context): void
    {
        $this->assertionValidator->validateAssertion($context->assertion);
    }
}
