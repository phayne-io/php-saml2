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

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Stream;

use function htmlspecialchars;
use function sprintf;

/**
 * Class SamlPostResponse
 *
 * @package Phayne\Saml\Binding
 */
class SamlPostResponse extends HtmlResponse
{
    public function __construct(public readonly string $destination, public readonly array $data)
    {
        parent::__construct('', StatusCodeInterface::STATUS_OK);
    }

    public function render(): SamlPostResponse
    {
        $content = <<<'EOT'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>POST data</title>
</head>
<body onload="document.getElementById('saml-input').click();">

    <noscript>
        <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once to proceed.</p>
    </noscript>

    <form method="post" action="%s">
        <input id="saml-input" type="submit" style="display:none;"/>
        %s
        
        <noscript>
            <input type="submit" value="Submit" />
        </noscript>

    </form>
</body>
</html>
EOT;
        $fields = '';
        foreach ($this->data as $name => $value) {
            $fields .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars($name),
                htmlspecialchars($value)
            );
        }

        $content = sprintf($content, htmlspecialchars($this->destination ?? ''), $fields);

        $body = new Stream('php://temp', 'wb+');
        $body->write($content);
        $body->rewind();

        return $this->withBody($body);
    }
}
