# phlib/xss-sanitizer

PHP XSS sanitizer tool for HTML

## Disclaimer

Use [HTML Purifier](http://htmlpurifier.org/).

This library was created to try to solve the problem of XSS sanitization without using a whitelist, since the HTML which is being sanitized may contain non-standard or unusual syntax (e.g. HTML for emails).

This library is also intended for a limited use case whereby it is assumed that the sanitized HTML is only going to be displayed in a limited set of supported browsers (e.g. no need to strip 'vbscript:' code).

## Usage

Create a sanitizer and sanitize some input

``` php
$sanitizer = new \Phlib\XssSanitizer\Sanitizer();
$sanitized = $sanitizer->sanitize($htmlInput);

```
