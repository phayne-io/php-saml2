<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;

use function bin2hex;
use function gmdate;
use function intval;
use function is_int;
use function is_string;
use function preg_match;
use function random_bytes;
use function rawurlencode;
use function strlen;
use function strtotime;
use function trim;

/**
 * Class Helper
 *
 * @package Phayne\Saml
 */
final class Helper
{
    public const string TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    public static function validateDurationString($duration): void
    {
        try {
            new DateInterval((string)$duration);
        } catch (Exception $ex) {
            throw new InvalidArgumentException(sprintf("Invalid duration '%s' format", $duration), 0, $ex);
        }
    }

    public static function time2string(int $time): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    public static function getTimestampFromValue($value): int
    {
        if (is_string($value)) {
            return self::parseSAMLTime($value);
        } elseif ($value instanceof DateTime) {
            return $value->getTimestamp();
        } elseif (is_int($value)) {
            return $value;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public static function parseSAMLTime($time): int|false
    {
        $matches = [];
        if (
            0 == preg_match(
                '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?(Z|[+-]\\d\\d:\\d\\d)$/D',
                $time,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Invalid SAML2 timestamp: ' . $time);
        }

        return strtotime($time);
    }

    public static function generateRandomBytes($length): string
    {
        $length = intval($length);
        if ($length <= 0) {
            throw new InvalidArgumentException();
        }

        return random_bytes($length);
    }

    public static function stringToHex(string $bytes): string
    {
        return bin2hex($bytes);
    }

    public static function generateID(): string
    {
        return '_' . self::stringToHex(self::generateRandomBytes(21));
    }


    public static function validateIdString(?string $id): bool
    {
        return null === $id || strlen(trim($id)) >= 16;
    }

    public static function validateRequiredString(?string $value): bool
    {
        return null === $value || strlen(trim($value)) > 0;
    }

    public static function validateOptionalString(?string $value): bool
    {
        return null === $value || self::validateRequiredString($value);
    }

    public static function validateWellFormedUriString(string $value): bool
    {
        $value = trim($value);

        if ('' === $value || strlen($value) > 65520) {
            return false;
        }

        if (preg_match('|\s|', $value)) {
            return false;
        }

        $parts = parse_url($value);
        if (isset($parts['scheme'])) {
            if ($parts['scheme'] !== rawurlencode($parts['scheme'])) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    public static function validateNotBefore(int $notBefore, int $now, int $allowedSecondsSkew): bool
    {
        return null == $notBefore || (($notBefore - $allowedSecondsSkew) <= $now);
    }

    public static function validateNotOnOrAfter(int $notOnOrAfter, int $now, int $allowedSecondsSkew): bool
    {
        return null == $notOnOrAfter || ($now < ($notOnOrAfter + $allowedSecondsSkew));
    }
}
