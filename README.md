# PureJSON

`json_encode()`/`json_decode()` wrapper for PHP 5.3+ with one-to-one mapping between JSON and PHP values.

```php
use PureJSON\JSON;

$json  = JSON::encode($value);
$value = JSON::decode($json);
```

- `JSON::encode()` will only accept values which can be converted into their exact original by `JSON::decode()`, so that `JSON::decode(JSON::encode($x)) === $x`. The accepted values are:
  - `int`
  - `string`
  - `float` (but not `INF`, `-INF` or `NAN`)
  - `bool`
  - `null`
  - `array` (whose contents are also valid)

- `JSON::encode()`/`JSON::decode()` will assume PHP strings are UTF-8 by default. To encode/decode binary or ISO-8859-1 strings, use `JSON::encode(..., true)` and `JSON::decode(..., true)`.

- To pretty print JSON, use `JSON::encode(..., ..., true)`.

- `JSON::encode()`/`JSON::decode()` will check `json_last_error()` for you and throw a `PureJSON\JSONException` with an appropriate [code](http://php.net/manual/en/function.json-last-error.php) and [message](http://php.net/manual/en/function.json-last-error-msg.php).

## Serialization

PureJSON provides a completely optional feature to assist in serializing PHP objects to and from JSON.

The methods `JSON::serialize()` and `JSON::deserialize()` are alternatives to `JSON::encode()` and `JSON::decode()` which map the JSON `{...}` syntax to and from PHP objects instead of to and from PHP associative arrays. Whereas `JSON::encode()` rejects objects and accepts associative arrays, `JSON::serialize()` rejects associative arrays and accepts objects.

Objects passed to `JSON::serialize()` must:
1. implement the `PureJSON\Serializable` interface
2. have a public constructor without parameters (or with only optional parameters)
3. have only public properties (any protected or private properties are ignored)

In order for `JSON::deserialize()` to reproduce an instance of the original class, the special property `@type` is filled by `JSON::serialize()` with the result of the `jsonType()` method of `PureJSON\Serializable`, and `JSON::deserialize()` accepts an explicit list of classes implementing `PureJSON\Serializable` to instantiate.

(By storing a type tag instead of the PHP class name in JSON, the PHP class can be renamed while maintaining compatibility with existing serialized data, and if the JSON given to `JSON::deserialize()` is produced by an attacker, they cannot instantiate classes outside of the explicit list.)

### Exmaple

With `JSON::encode()`/`JSON::decode()`:

```php
use PureJson\JSON;

$company = array(
	'name' => 'Good Company',
    'employees' => array(
    	array(
        	'name' => 'Jesse',
            'role' => 'sales',
        ),
        array(
        	'name' => 'Ben',
            'role' => 'development',
        ),
    ),
);

// encode/decode will reproduce the original array
$json    = JSON::encode($company);
$company = JSON::decode($json);
```

With `JSON::serialize()`/`JSON::deserialize()`:

```php
use PureJson\JSON;
use PureJson\Serializable;

class Company implements Serializable {
	public static function jsonType() {
    	return 'company';
    }

	// Properties must be public
	public $name;
    public $employees;

	// Constructor must not have required parameters
    public function __construct($name = null, $employees = array()) {
    	$this->name = $name;
        $this->employees = $employees;
    }
}

class Employee implements Serializable {
	public static function jsonType() {
    	return 'employee';
    }

	public $name;
    public $role;

	public function __construct($name = null, $role = null) {
    	$this->name = $name;
        $this->role = $role;
    }
}

$company = new Company(
	'Good Company',
    array(
    	new Employee('Jesse', 'sales'),
        new Employee('Ben', 'development'),
    )
);

// serialize/deserialize will produce the original object graph
$json    = JSON::serialize($company);
$company = JSON::deserialize($json, array(
	Company::class,
    Employee::class,
));
```