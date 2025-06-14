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
use Phayne\Saml\Context\Profile\Helper\LogHelper;
use Phayne\Saml\Context\Profile\Helper\MessageContextHelper;
use Phayne\Saml\Context\Profile\ProfileContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Assertion\Assertion;

use function array_any;

/**
 * Class HasAuthnStatementValidatorAction
 *
 * @package Phayne\Saml\Action\Profile\Inound\Response
 */
class HasAuthnStatementValidatorAction extends AbstractProfileAction
{
    #[Override]
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->inboundContext());

        if (array_any($response->assertions, fn(Assertion $assertion) => $assertion->authnStatements)) {
            return;
        }

        $message = 'Response must have at least one Assertion containing AuthnStatement element';
        $this->logger->error($message, LogHelper::actionErrorContext($context, $this));
        throw new SamlContextException($context, $message);
    }
}
