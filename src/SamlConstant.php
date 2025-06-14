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

/**
 * Enum SamlConstant
 *
 * @package Phayne\Saml
 */
enum SamlConstant: string
{
    case PROTOCOL_SAML2 = 'urn:oasis:names:tc:SAML:2.0:protocol';
    case PROTOCOL_SAML1 = 'urn:oasis:names:tc:SAML:1.0:protocol';
    case PROTOCOL_SAML11 = 'urn:oasis:names:tc:SAML:1.1:protocol';
    case PROTOCOL_SHIB1 = 'urn:mace:shibboleth:1.0';
    case PROTOCOL_WS_FED = 'http://schemas.xmlsoap.org/ws/2003/07/secext???';

    case VERSION_20 = '2.0';
    case NS_METADATA = 'urn:oasis:names:tc:SAML:2.0:metadata';
    case NS_ASSERTION = 'urn:oasis:names:tc:SAML:2.0:assertion';
    case NS_XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';

    case NAME_ID_FORMAT_NONE = '';
    case NAME_ID_FORMAT_ENTITY = 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity';
    case NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    case NAME_ID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    case NAME_ID_FORMAT_EMAIL = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
    case NAME_ID_FORMAT_SHIB_NAME_ID = 'urn:mace:shibboleth:1.0:nameIdentifier';
    case NAME_ID_FORMAT_X509_SUBJECT_NAME = 'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName';
    case NAME_ID_FORMAT_WINDOWS = 'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName';
    case NAME_ID_FORMAT_KERBEROS = 'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos';
    case NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    case BINDING_SAML2_HTTP_REDIRECT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    case BINDING_SAML2_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    case BINDING_SAML2_HTTP_ARTIFACT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
    case BINDING_SAML2_SOAP = 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP';
    case BINDING_SAML2_HTTP_POST_SIMPLE_SIGN = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign';
    case BINDING_SHIB1_AUTHN_REQUEST = 'urn:mace:shibboleth:1.0:profiles:AuthnRequest';
    case BINDING_SAML1_BROWSER_POST = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
    case BINDING_SAML1_ARTIFACT1 = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';
    case BINDING_WS_FED_WEB_SVC = 'http://schemas.xmlsoap.org/ws/2003/07/secext';

    case STATUS_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';
    case STATUS_REQUESTER = 'urn:oasis:names:tc:SAML:2.0:status:Requester';
    case STATUS_RESPONDER = 'urn:oasis:names:tc:SAML:2.0:status:Responder';
    case STATUS_VERSION_MISMATCH = 'urn:oasis:names:tc:SAML:2.0:status:VersionMismatch';
    case STATUS_NO_PASSIVE = 'urn:oasis:names:tc:SAML:2.0:status:NoPassive';
    case STATUS_PARTIAL_LOGOUT = 'urn:oasis:names:tc:SAML:2.0:status:PartialLogout';
    case STATUS_PROXY_COUNT_EXCEEDED = 'urn:oasis:names:tc:SAML:2.0:status:ProxyCountExceeded';
    case STATUS_INVALID_NAME_ID_POLICY = 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy';
    case STATUS_UNSUPPORTED_BINDING = 'urn:oasis:names:tc:SAML:2.0:status:UnsupportedBinding';

    case XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';

    case CONSENT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:consent:unspecified';

    case CONFIRMATION_METHOD_BEARER = 'urn:oasis:names:tc:SAML:2.0:cm:bearer';
    case CONFIRMATION_METHOD_HOK = 'urn:oasis:names:tc:SAML:2.0:cm:holder-of-key';
    case CONFIRMATION_METHOD_SENDER_VOUCHES = 'urn:oasis:names:tc:SAML:2.0:cm:sender-vouches';

    case AUTHN_CONTEXT_PASSWORD = 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password';
    case AUTHN_CONTEXT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified';
    case AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT = 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport';
    case AUTHN_CONTEXT_WINDOWS = 'urn:federation:authentication:windows';

    case ENCODING_DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';

    case LOGOUT_REASON_USER = 'urn:oasis:names:tc:SAML:2.0:logout:user';
    case LOGOUT_REASON_ADMIN = 'urn:oasis:names:tc:SAML:2.0:logout:admin';
    case LOGOUT_REASON_GLOBAL_TIMEOUT = 'urn:oasis:names:tc:SAML:2.0:logout:global-timeout';
    case LOGOUT_REASON_SP_TIMEOUT = 'urn:oasis:names:tc:SAML:2.0:logout:sp-timeout';

    case XMLDSIG_DIGEST_MD5 = 'http://www.w3.org/2001/04/xmldsig-more#md5';

    case ATTRIBUTE_NAME_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified';

    public function isProtocolValid(): bool
    {
        return
            $this === self::PROTOCOL_SAML2 ||
            $this === self::PROTOCOL_SAML1 ||
            $this === self::PROTOCOL_SAML11 ||
            $this === self::PROTOCOL_SHIB1 ||
            $this === self::PROTOCOL_WS_FED;
    }

    public function isNsValid(): bool
    {
        return
            $this === self::NS_METADATA ||
            $this === self::NS_ASSERTION ||
            $this === self::NS_XMLDSIG ||
            $this === self::PROTOCOL_SAML2;
    }

    public function isNameIdFormatValid(): bool
    {
        return
            $this === self::NAME_ID_FORMAT_NONE ||
            $this === self::NAME_ID_FORMAT_ENTITY ||
            $this === self::NAME_ID_FORMAT_PERSISTENT ||
            $this === self::NAME_ID_FORMAT_TRANSIENT ||
            $this === self::NAME_ID_FORMAT_EMAIL ||
            $this === self::NAME_ID_FORMAT_SHIB_NAME_ID ||
            $this === self::NAME_ID_FORMAT_X509_SUBJECT_NAME ||
            $this === self::NAME_ID_FORMAT_WINDOWS ||
            $this === self::NAME_ID_FORMAT_KERBEROS ||
            $this === self::NAME_ID_FORMAT_UNSPECIFIED;
    }

    public function isBindingValid(): bool
    {
        return
            $this === self::BINDING_SAML2_HTTP_REDIRECT ||
            $this === self::BINDING_SAML2_HTTP_POST ||
            $this === self::BINDING_SAML2_HTTP_ARTIFACT ||
            $this === self::BINDING_SAML2_SOAP ||
            $this === self::BINDING_SAML2_HTTP_POST_SIMPLE_SIGN ||
            $this === self::BINDING_SHIB1_AUTHN_REQUEST ||
            $this === self::BINDING_SAML1_BROWSER_POST ||
            $this === self::BINDING_SAML1_ARTIFACT1 ||
            $this === self::BINDING_WS_FED_WEB_SVC;
    }

    public function isStatusValid(): bool
    {
        return
            $this === self::STATUS_SUCCESS ||
            $this === self::STATUS_REQUESTER ||
            $this === self::STATUS_RESPONDER ||
            $this === self::STATUS_VERSION_MISMATCH ||
            $this === self::STATUS_NO_PASSIVE ||
            $this === self::STATUS_PARTIAL_LOGOUT ||
            $this === self::STATUS_PROXY_COUNT_EXCEEDED ||
            $this === self::STATUS_INVALID_NAME_ID_POLICY ||
            $this === self::STATUS_UNSUPPORTED_BINDING;
    }

    public function isConfirmationMethodValid(): bool
    {
        return
            $this === self::CONFIRMATION_METHOD_BEARER ||
            $this === self::CONFIRMATION_METHOD_HOK ||
            $this === self::CONFIRMATION_METHOD_SENDER_VOUCHES;
    }

    public function isAuthnContextValid(): bool
    {
        return
            $this === self::AUTHN_CONTEXT_PASSWORD ||
            $this === self::AUTHN_CONTEXT_UNSPECIFIED ||
            $this === self::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT ||
            $this === self::AUTHN_CONTEXT_WINDOWS;
    }

    public function isLogoutReasonValid(): bool
    {
        return
            $this === self::LOGOUT_REASON_USER ||
            $this === self::LOGOUT_REASON_ADMIN ||
            $this === self::LOGOUT_REASON_GLOBAL_TIMEOUT ||
            $this === self::LOGOUT_REASON_SP_TIMEOUT;
    }

    public static function is(string $value, SamlConstant $type): bool
    {
        return self::tryFrom($value) === $type;
    }

    public function validatorMethod(): ?string
    {
        return match ($this) {
            self::NAME_ID_FORMAT_EMAIL => 'validateEmailFormat',
            self::NAME_ID_FORMAT_X509_SUBJECT_NAME => 'validateX509SubjectNameFormat',
            self::NAME_ID_FORMAT_WINDOWS => 'validateWindowsFormat',
            self::NAME_ID_FORMAT_KERBEROS  => 'validateKerberosFormat',
            self::NAME_ID_FORMAT_ENTITY => 'validateEntityFormat',
            self::NAME_ID_FORMAT_PERSISTENT => 'validatePersistentFormat',
            self::NAME_ID_FORMAT_TRANSIENT => 'validateTransientFormat',
            default => null
        };
    }
}
