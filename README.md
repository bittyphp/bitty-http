# Bitty HTTP

[![Build Status](https://travis-ci.org/bittyphp/http.svg?branch=master)](https://travis-ci.org/bittyphp/http)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/114f808c31844c099eba857edd84287b)](https://www.codacy.com/app/bittyphp/http)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Mutation Score](https://badge.stryker-mutator.io/github.com/bittyphp/http/master)](https://infection.github.io)
[![Total Downloads](https://poser.pugx.org/bittyphp/http/downloads)](https://packagist.org/packages/bittyphp/http)
[![License](https://poser.pugx.org/bittyphp/http/license)](https://packagist.org/packages/bittyphp/http)

A complete [PSR-7](https://www.php-fig.org/psr/psr-7/) HTTP and [PSR-17](https://www.php-fig.org/psr/psr-17/) HTTP Factory implementation.

## Installation

It's best to install using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/http
```

## Outline

- [Abstract Messages](#abstract-messages)
- [Requests](#requests)
- [Responses](#responses)
- [File Uploads](#file-uploads)
- [Streams](#streams)
- [URIs](#uris)

## Abstract Messages

All `Request` and `Response` classes share a base `AbstractMessage` class that provides methods for interacting with the headers and body of a message.

### Available Methods

The following methods are available on all `Request` and `Response` objects:

#### `getProtocolVersion()`

Gets the HTTP protocol version as a string (e.g., "1.0" or "1.1").

#### `withProtocolVersion($version)`

Returns a new instance of the message with the given HTTP protocol version as a string (e.g., "1.0" or "1.1").

#### `getHeaders()`

Returns an array of the headers tied to the message. The array keys are the header names and each value is an array of strings for that header.

#### `hasHeader($name)`

Makes a case-insensitive comparison to see if the header name given exists in the headers of the message. Returns `true` if found, `false` if not.

#### `getHeader($name)`

Returns an array of strings for the values of the given case-insensitive header. If the header does not exist, it will return an empty array.

#### `getHeaderLine($name)`

Returns a comma-separated string of all the values of the given case-insensitive header. If the header does not exist, it will return an empty string.

#### `withHeader($name, $value)`

Returns a new instance of the message while replacing the given header with the value or values specified.

```php
<?php

use Bitty\Http\ServerRequest;

$request = new ServerRequest(...);

$newRequest = $request->withHeader(
    'Content-Type',
    'text/html'
);

$newRequest = $request->withHeader(
    'Accept',
    ['application/json', 'application/xml']
);
```

#### `withAddedHeader($name, $value)`

Returns a new instance of the message while adding the given header with the value or values specified. Very similar to `withHeader()`, except it maintains all existing headers.

#### `withoutHeader($name)`

Returns a new instance of the message while completely removing the given header.

#### `getBody()`

Gets the body of the message in a [`Psr\Http\Message\StreamInterface`](#streams) format.

#### `withBody($body)`

Returns a new instance of the message using the given body. The body must be an instance of [`Psr\Http\Message\StreamInterface`](#streams).

## Requests

There are two types of requests: `Request` and `ServerRequest`. The `Request` class is used for outgoing requests, e.g. you send a request to another server. The `ServerRequest` class is used for incoming requests, e.g. someone makes a request to your website for you to process and respond to.

Unless you're building an HTTP client, you'll most likely only use the `ServerRequest`. Both are included because this library is a complete PSR-7 implementation.

- [Request](#request) (outgoing)
- [ServerRequest](#serverrequest) (incoming)

### `Request`

The `Request` class is used to build an outgoing, client-side request. Requests are considered immutable; all methods that change the state of the request return a new instance that contains the changes. The original request is always left unchanged.

#### Building a `Request`

The `RequestFactory` is the most consistent way to build a request, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\RequestFactory;
use Psr\Http\Message\RequestInterface;

$factory = new RequestFactory();

/** @var RequestInterface */
$request = $factory->createRequest('GET', '/some/path?foo=bar');

```

Alternatively, you can build the request manually.

```php
<?php

use Bitty\Http\Request;

$method = 'GET';
$uri = 'http://example.com/';
$headers = ['Content-Type' => 'application/json'];
$body = '{"ping": "pong"}';
$protocolVersion = '1.1';

// All of the parameters are optional.
$request = new Request(
    $method,
    $uri,
    $headers,
    $body,
    $protocolVersion
);

```

#### Available Methods

In addition to all of the methods inherited from `AbstractMessage`, the following methods are available:

##### `getRequestTarget()`

Gets the message's request target as it will be seen for clients. In most cases, this will be the origin-form of the URI, unless a specific value has been provided. For example, if you request "http://example.com/search?q=test" then this will contain "/search?q=test").

##### `withRequestTarget($requestTarget)`

Returns a new instance with the message's request target, as given.

##### `getMethod()`

Gets the HTTP method of the request.

##### `withMethod($method)`

Returns a new instance with the message's HTTP method set as given. The method name should be uppercase, however it will not correct the capitalization for you.

##### `getUri()`

Gets the URI of the request as a [`Psr\Http\Message\UriInterface`](#uris).

##### `withUri($uri, $preserveHost = false)`

Returns a new instance with the message's URI set as given. It must be given a [`Psr\Http\Message\UriInterface`](#uris). If preserve host is set to `true`, it will not change the hostname of the request unless there isn't one already set.

### `ServerRequest`

The `ServerRequest` class extends `Request` and is used to build an incoming, server-side request. Requests are considered immutable; all methods that change the state of the request return a new instance that contains the changes. The original request is always left unchanged.

#### Building a `ServerRequest`

The `ServerRequestFactory` is the most consistent way to build a request, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;

$factory = new ServerRequestFactory();

/** @var ServerRequestInterface */
$request = $factory->createServerRequest('GET', '/some/path?foo=bar');
$request = $factory->createServerRequest('GET', '/some/path?foo=bar', $serverParams);

```

#### Available Methods

In addition to all of the methods inherited from `Request`, the following methods are available:

##### `getServerParams()`

Gets the server parameters for the request. Typically this is the contents of the `$_SERVER` variable, but doesn't have to be.

##### `getCookieParams()`

Gets the cookie parameters for the request. The return structure matches the format of what `$_COOKIE` provides.

##### `withCookieParams($cookies)`

Returns a new instance of the request with the updated cookie parameters. The `$cookies` parameter must match the structure that `$_COOKIE` provides.

##### `getQueryParams()`

Gets the query string parameters for the request. Typically this is the contents of the `$_GET` variable, but doesn't have to be. It's also possible for the query parameters to be out of sync with the URI query parameters, as setting one does not automatically set the other.

##### `withQueryParams($query)`

Returns a new instance of the request with the updated query parameters. Updating the query parameters will not automatically update the URI of the request.

##### `getUploadedFiles()`

Gets an array of normalized file uploads where each node of the array is a [`Psr\Http\Message\UploadedFileInterface`](#uploaded-files).

##### `withUploadedFiles($uploadedFiles)`

Returns a new instance of the request with the given file tree. Each node of the array must be a [`Psr\Http\Message\UploadedFileInterface`](#uploaded-files).

```php
<?php

use Bitty\Http\ServerRequest;

$request = new ServerRequest(...);

// A simple list.
$newRequest = $request->withUploadedFiles(
    [
        'fileA' => $fileA,
        'fileB' => $fileB,
    ]
);

// A nested list.
$newRequest = $request->withUploadedFiles(
    [
        'images' => [
            'small' => $fileA,
            'large' => $fileB,
        ],
        'foo' => [
            'bar' => [
                'baz' => $fileC,
            ],
        ],
    ]
);
```

##### `getParsedBody()`

Gets the parameters of the request body. If the request Content-Type is either `application/x-www-form-urlencoded` or `multipart/form-data`, and the request method is `POST`, this method will return an array similar to `$_POST`. For other methods, such as `PUT` or `PATCH`, it will only parse the body if the Content-Type is `application/x-www-form-urlencoded` or `application/json` and then return the resulting array.

##### `withParsedBody($body)`

Returns a new instance of the request with the given parsed body. It only accepts `array`, `object`, or `null` values.

##### `getAttributes()`

Gets all custom attributes associated with the request. Attributes are application-specific data added to a request and can be anything, such as routing data or authentication flags.

##### `getAttribute($name, $default = null)`

Gets the given attribute for the request. If the attribute is not set, the default value will be returned.

##### `withAttribute($name, $value)`

Returns a new instance of the request with the given attribute set.

```php
<?php

use Bitty\Http\ServerRequest;

$request = new ServerRequest(...);

// If you have a route such as /product/{id}
// And a request for /product/123
// You can set the 'id' attribute to the product ID
$newRequest = $request->withAttribute('id', 123);

// Some controller for the route
$controller = function ($request) {
    // Look up product data
    $productId = $request->getAttribute('id');
    $product = $someRepository->find($productId);

    // Do something with $product
};

$controller($newRequest);
```

##### `withoutAttribute($name)`

Returns a new instance of the request without the given attribute.

## Responses

There are three response classes available, mainly for convenience, but they all extend `Response`.

- [Response](#response)
- [JsonResponse](#jsonresponse)
- [RedirectResponse](#redirectresponse)

### `Response`

The `Response` class is used to return data to the client, typically in the form of HTML.

#### Building a `Response`

The `ResponseFactory` is the most consistent way to build a response, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

$factory = new ResponseFactory();

/** @var ResponseInterface */
$response = $factory->createResponse();
$response = $factory->createResponse(404);
$response = $factory->createResponse(404, 'Not Found');

```

Or you can build one manually.

```php
<?php

use Bitty\Http\Response;

// Defaults to a 200 OK response.
$response = new Response('Hello, world!');

// Use a given status code.
$response = new Response('', 204);

// Send custom headers.
$response = new Response(
    'Goodbye, world!',
    302,
    ['Location' => '/bye-bye']
);

```

#### Available Methods

In addition to all of the methods inherited from `AbstractMessage`, the following methods are available:

##### `getStatusCode()`

Can be used to get the HTTP status code of the response (e.g., `200` or `404`).

##### `getReasonPhrase()`

Can be used to get the associated text for the status code (e.g., `OK` or `Not Found`).

##### `withStatus()`

Allows you to set the status and, optionally, the reason phrase of the response and returns the changes in a new response object.

```php
<?php

use Bitty\Http\Response;

$response = new Response(...);

$newResponse = $response->withStatus(204);
$newResponse = $response->withStatus(204, 'No Content');
```

### `JsonResponse`

The `JsonResponse` is a convenience extension of the `Response` class to make returning JSON data easier. It automatically encodes whatever data is given to it as JSON and sets the `Content-Type` header to `application/json`.

```php
<?php

use Bitty\Http\JsonResponse;

// Defaults to a 200 OK response.
$response = new JsonResponse(['message' => 'Hello, world!']);

// Custom 404 response.
$response = new JsonResponse(
    ['error' => 'Page not found'],
    404
);

// Include additional headers.
$response = new JsonResponse(
    ['error' => 'Invalid credentials'],
    401,
    ['X-Auth' => 'Failed']
);

```

### `RedirectResponse`

The `RedirectResponse` is a convenience extension of the `Response` class to make redirects easier. It automatically sets the `Location` header and includes a link in the body for the URI being redirected to.

```php
<?php

use Bitty\Http\RedirectResponse;

// Defaults to a 302 redirect.
$redirect = new RedirectResponse('/some/path');

// Use a given status code.
$redirect = new RedirectResponse('/some/path', 301);

// Send custom headers.
$redirect = new RedirectResponse(
    '/some/path',
    302,
    ['X-Message' => 'Bye-bye']
);

```

## File Uploads

The `UploadedFile` class attempts to fix issues with how PHP structures the `$_FILES` global.

### Building an `UploadedFile`

The `UploadedFileFactory` is the most consistent way to build an `UploadedFile`, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\UploadedFileFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

$factory = new UploadedFileFactory();

/** @var StreamInterface */
$stream = ...;

/** @var UploadedFileInterface */
$file = $factory->createUploadedFile($stream);
$file = $factory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);

```

### Available Methods

The following methods are available:

#### `getStream()`

Gets a [`Psr\Http\Message\StreamInterface`](#streams) representing the file upload.

#### `moveTo($targetPath)`

Moves the file to the target path. Internally, this uses `move_uploaded_file()` or `rename()`, depending on whether it's called in a SAPI or non-SAPI environment.

#### `getSize()`

Gets the size of the file.

#### `getError()`

Gets any error codes associated to the file. This will return one of the [`UPLOAD_ERR_*` constants](http://php.net/manual/en/features.file-upload.errors.php).

#### `getClientFilename()`

Gets the filename sent by the client. The value of this should not be trusted, as it can easily be faked.

#### `getClientMediaType()`

Gets the media type sent by the client. The value of this should not be trusted, as it can easily be faked.

## Streams

Streams provide a standardized way of accessing streamable data, such as request/response bodies and file uploads. However, the might be useful in any other part of your code.

### Building a `Stream`

The `StreamFactory` is the most consistent way to build a `Stream`, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\StreamFactory;
use Psr\Http\Message\StreamInterface;

$factory = new StreamFactory();

/** @var StreamInterface */
$stream = $factory->createStream('string of data');
$stream = $factory->createStreamFromFile('/path/to/file', 'r');
$stream = $factory->createStreamFromResource($resource);

```

Alternatively, you can build a `Stream` manually:

```php
<?php

use Bitty\Http\Stream;

$stream = new Stream('string of data');
$stream = new Stream($resource);

```

### Available Methods

The following methods are available:

#### `close()`

Closes the stream and any underlying resources.

#### `detach()`

Separates the underlying resource from the stream and returns it.

#### `getSize()`

Get the size of the stream, if known.

#### `tell()`

Returns the current position of the file pointer.

#### `eof()`

Returns true if the stream is at the end of the stream.

#### `isSeekable()`

Returns whether or not the stream is seekable.

#### `seek($offset, $whence = SEEK_SET)`

Seek to a position in the stream. `$whence` should be one of PHP' [`SEEK_*` constants](http://www.php.net/manual/en/function.fseek.php).

#### `rewind()`

Seek to the beginning of the stream.

#### `isWritable()`

Returns whether or not the stream is writable.

#### `write($string)`

Write data to the stream.

#### `isReadable()`

Returns whether or not the stream is readable.

#### `read($length)`

Read data from the stream.

#### `getContents()`

Returns the remaining contents of the stream.

#### `getMetadata($key = null)`

Get stream metadata as an associative array or retrieve a specific key. The keys returned are identical to the keys returned from PHP's [`stream_get_meta_data()`](http://php.net/manual/en/function.stream-get-meta-data.php) function.

## URIs

The `Uri` class makes working with URI values easier, as you can easily get or set only certain parts of the URI.

### Building a `Uri`

The `UriFactory` is the most consistent way to build a `Uri`, regardless of the framework being used. All PSR-17 implementations share this method signature.

```php
<?php

use Bitty\Http\UriFactory;
use Psr\Http\Message\UriInterface;

$factory = new UriFactory();

/** @var UriInterface */
$uri = $factory->createUri('/some/path?foo=bar');
$uri = $factory->createUri('https://example.com/search?q=test');

```

Alternatively, you can build a `Uri` manually:

```php
<?php

use Bitty\Http\Uri;

$uri = new Uri('/some/path?foo=bar');
$uri = new Uri('https://example.com/search?q=test');

```

### Available Methods

The following methods are available:

#### `getScheme()`

Retrieve the scheme component of the URI.

#### `getAuthority()`

Retrieve the authority component of the URI. The authority syntax of the URI is `[user-info@]host[:port]`.

#### `getUserInfo()`

Retrieve the user information component of the URI. The syntax is `username[:password]`.

#### `getHost()`

Retrieve the host component of the URI.

#### `getPort()`

Retrieve the port component of the URI. If the port is a standard port (e.g., 80 for HTTP or 443 for HTTPS), this will return `null`.

#### `getPath()`

Retrieve the path component of the URI.

#### `getQuery()`

Retrieve the query string of the URI.

#### `getFragment()`

Retrieve the fragment component of the URI.

#### `withScheme($scheme)`

Returns a new instance with the specified scheme.

#### `withUserInfo($user, $password = null)`

Returns a new instance with the specified user information.

#### `withHost($host)`

Returns a new instance with the specified host.

#### `withPort($port)`

Returns a new instance with the specified port.

#### `withPath($path)`

Returns a new instance with the specified path.

#### `withQuery($query)`

Returns a new instance with the specified query.

#### `withFragment($fragment)`

Returns a new instance with the specified fragment.
