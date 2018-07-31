# XmlHelper
Works two ways:
- convert an array to XML
- convert an XML to an array

This package is based on [array-to-xml](https://github.com/spatie/array-to-xml) from spatie

## array to xml

```php
use Funkeye\XmlHelper\XmlHelper;
...
$array = [
    'Good guy' => [
        'name' => 'Luke Skywalker',
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => 'Sauron',
        'weapon' => 'Evil Eye'
    ]
];

$result = XmlHelper::arrayToXml($array);
```
After running this piece of code `$result` will contain:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy>
        <name>Luke Skywalker</name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>Sauron</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>
```

Optionally you can set the name of the rootElement by passing it as the second argument. If you don't specify
this argument (or set it to an empty string) "root" will be used.
```
$result = XmlHelper::arrayToXml($array, 'customrootname');
```

By default all spaces in the key names of your array will be converted to underscores. If you want to opt out of
this behaviour you can set the third argument to false. We'll leave all keynames alone.
```
$result = XmlHelper::arrayToXml($array, 'customrootname', false);
```

You can use a key named `_attributes` to add attributes to a node.

```php
$array = [
    'Good guy' => [
        '_attributes' => ['attr1' => 'value'],
        'name' => 'Luke Skywalker',
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => 'Sauron',
        'weapon' => 'Evil Eye'
    ]
];

$result = XmlHelper::arrayToXml($array);
```

This code will result in:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy attr1="value">
        <name>Luke Skywalker</name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>Sauron</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>
```

It is also possible to wrap the value of a node into a CDATA section. This allows you to use reserved characters.

```php
$array = [
    'Good guy' => [
        'name' => [
            '_cdata' => '<h1>Luke Skywalker</h1>'
        ],
        'weapon' => 'Lightsaber'
    ],
    'Bad guy' => [
        'name' => '<h1>Sauron</h1>',
        'weapon' => 'Evil Eye'
    ]
];

$result = XmlHelper::arrayToXml($array);
```

This code will result in:

```xml
<?xml version="1.0"?>
<root>
    <Good_guy>
        <name><![CDATA[<h1>Luke Skywalker</h1>]]></name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>&lt;h1&gt;Sauron&lt;/h1&gt;</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>
```

If your input contains something that cannot be parsed a `DOMException` will be thrown.

To add attributes to the root element provide an array with an `_attributes` key as the second argument. 
The root element name can then be set using the `rootElementName` key.

```php
$result = XmlHelper::arrayToXml($array, [
    'rootElementName' => 'helloyouluckypeople',
    '_attributes' => [
        'xmlns' => 'https://github.com/spatie/array-to-xml',
    ],
]);
```

## xml to array
```php
$xml = '<?xml version="1.0"?>
<root>
    <Good_guy>
        <name><![CDATA[<h1>Luke Skywalker</h1>]]></name>
        <weapon>Lightsaber</weapon>
    </Good_guy>
    <Bad_guy>
        <name>&lt;h1&gt;Sauron&lt;/h1&gt;</name>
        <weapon>Evil Eye</weapon>
    </Bad_guy>
</root>';
$result = XmlHelper::arrayToXml($xml);
```

This code will result in:

```php
array (size=2)
  'Good_guy' => 
    array (size=2)
      'name' => string '<h1>Luke Skywalker</h1>' (length=23)
      'weapon' => string 'Lightsaber' (length=10)
  'Bad_guy' => 
    array (size=2)
      'name' => string '<h1>Sauron</h1>' (length=15)
      'weapon' => string 'Evil Eye' (length=8)
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
