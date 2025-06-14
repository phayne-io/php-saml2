<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Meta;

use Phayne\Saml\Credential\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class SigningOptions
 *
 * @package Phayne\Saml\Meta
 */
class SigningOptions
{
    public const string CERTIFICATE_SUBJECT_NAME = 'subjectName';
    public const string CERTIFICATE_ISSUER_SERIAL = 'issuerSerial';

    public bool $enabled = true;

    public readonly ParameterBag $certificateOptions;

    public function __construct(
        public ?XMLSecurityKey $privateKey = null,
        public ?X509Certificate $certificate = null,
    ) {
        $this->certificateOptions = new ParameterBag();
    }
}
