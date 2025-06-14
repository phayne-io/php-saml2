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

use Phayne\Saml\Context\AbstractContext;
use Phayne\Saml\Exception\SamlContextException;

/**
 * Class AbstractProfileContext
 *
 * @package Phayne\Saml\Context\Profile
 * @template T
 * @extends AbstractContext<T>
 */
abstract class AbstractProfileContext extends AbstractContext
{
    public function profileContext(): ProfileContext
    {
        $context = $this;

        while ($context && false === $context instanceof ProfileContext) {
            $context = $context->parent;
        }

        if ($context instanceof ProfileContext) {
            return $context;
        }

        throw new SamlContextException($this, 'Missing ProfileContext');
    }
}
