# PushXML
PHP Lib to parse XML in a very simple way

Highly inspired by the original code of the genious master of any code you can find on the internet [@nlehuen](https://github.com/nlehuen)

This XML parser uses the standard XML SAX parser (which in turn uses expat) to provide a SimpleXML-like data model in a streaming fashion. This way, you get the benefit of an in-memory model without having to load a whole document in memory.

To parse a document, you provide a path expression and a callback function. The path expression tells the parser when to use the callback as soon as a tree branch matching the path expression has been parsed. See the example use in the source code.

 Usage example
```php
$xml = <<<XMLEND
<?xml version="1.0" encoding="US-ASCII"?>
<foo id="bar">
  <section id="1">
    <title>This not a section</title>
  </section>
  <section id="1">
    <title>This is the first section</title>
    <item id="1">
      <hello>world</hello>
      <container>
        <this>is</this>
        <a>test</a>
      </container>
    </item>
    <item id="2" name="foobar again">
      <goodbye>cruel world</goodbye>
    </item>
  </section>
  <section id="2">
    <title>This is the second section</title>
    <item id="3">
      <a>b</a>
      <a>c</a>
    </item>
    <item id="4">
      <c>d</c>
    </item>
  </section>
  <test>
    <item id="5">
      <title>This item is not in a section</title>
    </item>
  </test>
</foo>
XMLEND;

function callback($root,$node) {
  print_r($node);
}

$parser = new PushXML('/foo/section/item','callback');
$parser->parse($xml,TRUE);
```
