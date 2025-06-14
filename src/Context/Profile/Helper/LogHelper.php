<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile\Helper;

use Phayne\Saml\Action\ActionInterface;
use Phayne\Saml\Context\ContextInterface;
use Phayne\Saml\Context\Profile\ProfileContext;

use function array_merge;
use function spl_object_hash;

/**
 * Class LogHelper
 *
 * @package Phayne\Saml\Context\Profile\Helper
 */
abstract class LogHelper
{
    public static function actionContext(
        ContextInterface $context,
        ActionInterface $action,
        ?array $extraData = null
    ): array {
        return self::context($context, $action, $extraData);
    }

    public static function actionErrorContext(
        ContextInterface $context,
        ActionInterface $action,
        ?array $extraData = null
    ): array {
        return self::context($context, $action, $extraData, true);
    }

    private static function context(
        ContextInterface $context,
        ?ActionInterface $action = null,
        ?array $extraData = null,
        bool $logWholeContext = false
    ): array {
        $topContext = $context->topParent();
        $result = [];

        if ($topContext instanceof ProfileContext) {
            $result['profile_id'] = $topContext->profileId;
            $result['own_role'] = $topContext->ownRole;
        }

        if ($action instanceof ActionInterface) {
            $result['action'] = $action::class;
        }

        $result['top_context_id'] = spl_object_hash($topContext);

        if ($logWholeContext) {
            $result['top_context'] = $topContext;
        }

        if ($extraData) {
            $result = array_merge($result, $extraData);
        }

        return $result;
    }
}
