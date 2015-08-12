
`json_encode()`/`json_decode()` wrapper with clean semantics for PHP 5.3+.

```php
use PureJSON\JSON;

$json  = JSON::encode($value);
$value = JSON::decode($json);
```

`JSON::encode()` will only accept values which can be converted into their exact original by `JSON::decode()`, so that `JSON::decode(JSON::encode($x)) === $x`. The accept values are:
- `int`
- `string`
- `float` (but not `INF`, `-INF` or `NAN`)
- `bool`
- `null`
- `array` (whose contents are also valid)

`JSON::encode()`/`JSON::decode()` will assume PHP strings are UTF-8 by default. To encode/decode binary or ISO-8859-1 strings, use `JSON::encode(..., true)` and `JSON::decode(..., true)`.

To pretty print JSON, use `JSON::encode(..., ..., true)`.

`JSON::encode()`/`JSON::decode()` will check `json_last_error()` and throw a `PureJSON\JSONException` with an appropriate [code](http://php.net/manual/en/function.json-last-error.php) and [message](http://php.net/manual/en/function.json-last-error-msg.php).

