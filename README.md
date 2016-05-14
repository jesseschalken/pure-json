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

The methods `JSON::serialize()` and `JSON::deserialize()` differ from `JSON::encode()` and `JSON::decode()` by mapping the JSON `{...}` syntax to and from PHP objects instead of to and from PHP associative arrays. Whereas `JSON::encode()` rejects objects and accepts associative arrays, `JSON::serialize()` rejects associative arrays and accepts objects.

In order for `JSON::deserialize()` to reproduce an instance of the original class given to `JSON::serialize()`:

1. Objects provided to `JSON::serialize()` must implement the `PureJSON\Serializable` interface.
2. The method `jsonProps()` is used for properties and `jsonType()` is used to fill a special `@type` property to identify the type of the object.
3. `JSON::deserialize()` requires an explicit list of classes implementing `PureJSON\Serializable` to possibly instantiate using the method `jsonCreate($props)`.

(By storing a type tag instead of the PHP class name in JSON, the PHP class can be renamed while maintaining compatibility with existing serialized data, and if the JSON given to `JSON::deserialize()` is produced by an attacker, they cannot instantiate classes outside of the explicit list.)

### Exmaple

With `JSON::encode()`/`JSON::decode()`:

```php
use PureJson\JSON;

$company = array(
    'name'      => 'Good Company',
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
```json
{
    "name": "Good Company",
    "employees": [
        {
            "name": "Jesse",
            "role": "sales"
        },
        {
            "name": "Ben",
            "role": "Development"
        }
    ]
}
```
With `JSON::serialize()`/`JSON::deserialize()`:

```php
use PureJson\JSON;
use PureJson\Serializable;

class Company implements Serializable {
    public static function jsonCreate(array $props) {
        return new self($props['name'], $props['employees']);
    }

    public static function jsonType() {
    	return 'company';
    }

    private $name;
    private $employees;

    public function __construct($name, $employees) {
    	$this->name      = $name;
        $this->employees = $employees;
    }

    public function jsonProps() {
        return array(
            'name'      => $this->name,
            'employees' => $this->employees,
        );
    }
}

class Employee implements Serializable {
    public static function jsonCreate(array $props) {
        return new self($props['name'], $props['role']);
    }

    public static function jsonType() {
        return 'employee';
    }

    private $name;
    private $role;

    public function __construct($name, $role) {
        $this->name = $name;
        $this->role = $role;
    }

    public function jsonProps() {
        return array(
            'name' => $this->name,
            'role' => $this->role,
        );
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
```json
{
    "@type": "company",
    "name": "Good Company",
    "employees": [
        {
            "@type": "employee",
            "name": "Jesse",
            "role": "sales"
        },
        {
            "@type": "employee",
            "name": "Ben",
            "role": "Development"
        }
    ]
}
```
