<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Exception;

use Override;
use Stringable;

use function sprintf;
use function trim;

/**
 * Class XsdError
 *
 * @package Phayne\Saml\Exception
 */
class XsdError implements Stringable
{
    public const string WARNING = 'Warning';
    public const string ERROR = 'Error';
    public const string FATAL = 'Fatal';

    public function __construct(
        public readonly string $level,
        public readonly int $code,
        public readonly string $message,
        public readonly int $line,
        public readonly int $column
    ) {
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf(
            '%s %d: %s on line %d column %d',
            $this->level,
            $this->code,
            trim($this->message),
            $this->line,
            $this->column
        );
    }
}
