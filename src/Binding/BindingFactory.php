<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Binding;

use Fig\Http\Message\RequestMethodInterface;
use LogicException;
use Override;
use Phayne\Saml\Exception\SamlBindingException;
use Phayne\Saml\SamlConstant;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function sprintf;
use function strpos;
use function substr;

/**
 * Class BindingFactory
 *
 * @package Phayne\Saml\Binding
 */
class BindingFactory implements BindingFactoryInterface
{
    public function __construct(
        protected ?EventDispatcherInterface $dispatcher = null
    ) {
    }

    #[Override]
    public function bindingByRequest(ServerRequestInterface $request): AbstractBinding
    {
        $bindingType = $this->detectBindingType($request);

        return $this->create($bindingType);
    }

    #[Override]
    public function create(SamlConstant $bindingType): AbstractBinding
    {
        $binding = match ($bindingType) {
            SamlConstant::BINDING_SAML2_HTTP_REDIRECT => new HttpRedirectBinding(),
            SamlConstant::BINDING_SAML2_HTTP_POST => new HttpPostBinding(),
            SamlConstant::BINDING_SAML2_HTTP_ARTIFACT => throw new LogicException('Artifact binding not implemented'),
            SamlConstant::BINDING_SAML2_SOAP => throw new LogicException('SOAP binding not implemented'),
            default => null
        };

        if (null !== $binding) {
            $binding->eventDispatcher = $this->dispatcher;
            return $binding;
        }

        throw new SamlBindingException(sprintf("Unknown binding type '%s'", $bindingType->value));
    }

    #[Override]
    public function detectBindingType(ServerRequestInterface $request): ?SamlConstant
    {
        return match ($request->getMethod()) {
            RequestMethodInterface::METHOD_GET => $this->processGET($request),
            RequestMethodInterface::METHOD_POST => $this->processPOST($request),
            default => null,
        };
    }

    protected function processGET(ServerRequestInterface $request): ?SamlConstant
    {
        $query = $request->getQueryParams();

        if (
            array_key_exists('SAMLRequest', $query) ||
            array_key_exists('SAMLResponse', $query)
        ) {
            return SamlConstant::BINDING_SAML2_HTTP_REDIRECT;
        } elseif (array_key_exists('SAMLart', $query)) {
            return SamlConstant::BINDING_SAML2_HTTP_ARTIFACT;
        }

        return null;
    }

    protected function processPOST(ServerRequestInterface $request): ?SamlConstant
    {
        $body = (array)$request->getParsedBody();

        if (
            array_key_exists('SAMLRequest', $body) ||
            array_key_exists('SAMLResponse', $body)
        ) {
            return SamlConstant::BINDING_SAML2_HTTP_POST;
        } elseif (array_key_exists('SAMLart', $body)) {
            return SamlConstant::BINDING_SAML2_HTTP_ARTIFACT;
        } elseif (false === empty($contentType = $request->getHeaderLine('Content-Type'))) {
            if (false !== $pos = strpos($contentType, ';')) {
                $contentType = substr($contentType, 0, $pos);
            }
            if ('text/xml' === $contentType) {
                return SamlConstant::BINDING_SAML2_SOAP;
            }
        }

        return null;
    }
}
