<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile;

use Exception;

/**
 * Class ExceptionContext
 *
 * @package Phayne\Saml\Context\Profile
 */
class ExceptionContext extends AbstractProfileContext
{
    protected(set) ?ExceptionContext $nextExceptionContext = null;

    public ?Exception $lastException {
        get {
            if (null === $this->nextExceptionContext) {
                return $this->exception;
            }
            return $this->nextExceptionContext->exception;
        }
    }

    public function __construct(protected(set) ?Exception $exception = null)
    {
    }

    public function addException(Exception $exception): ExceptionContext
    {
        if (null !== $this->exception) {
            if (null === $this->nextExceptionContext) {
                $this->nextExceptionContext = new self($exception);
                return $this->nextExceptionContext;
            } else {
                return $this->nextExceptionContext->addException($exception);
            }
        } else {
            $this->exception = $exception;
        }

        return $this;
    }
}
