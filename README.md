# PushXML
PHP Lib to parse XML in a very simple way

Highly inspired by the original code of the genious master of any code you can find on the internet [@nlehuen](https://github.com/nlehuen)

This XML parser uses the standard XML SAX parser (which in turn uses expat) to provide a SimpleXML-like data model in a streaming fashion. This way, you get the benefit of an in-memory model without having to load a whole document in memory.

To parse a document, you provide a path expression and a callback function. The path expression tells the parser when to use the callback as soon as a tree branch matching the path expression has been parsed. See the example use in the source code.

## Usage
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



Composer hse-monitor (front)

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../SBLib2/DataSource/"
        },
        {
            "type": "path",
            "url": "../RolLib/4.0/"
        },
        {
            "type": "composer",
            "url": "http://composer.gisi-interactive.net/"
        }
    ],
    "require": {
        "php": ">=5.6",
        "sblib/sblib": "0.1.x-dev",
        "sblib2/core": "v0.0.1",
        "sblib2/datasource": "@dev",
        "sharedmemory/sharedmemory": "0.1.0",
        "rol/rollib4": "@dev",
        "smarty/smarty": "v3.1.27",
        "clue/graph": "v0.9.0",
        "graphp/graphviz": "v0.2.1",
        "graphp/algorithms": "v0.8.1"
    },
    "autoload": {
        "classmap": [
            "private/",
            "crontab/"
        ],
        "exclude-from-classmap": ["vendor/sblib/sblib/Form/ZendX/Validate/", "vendor/sblib/sblib/Autoloader/file_auto_load.php", "vendor/sblib/sblib/vendor/HTMLPurifier/"]
    },
    "config": {
        "local": {
            "": "/mnt/bastet/ppetit/public_html/RolLib/4.0",
            "<vendor>": "/mnt/bastet/ppetit/public_html/hse-monitor/vendor/rol/rollib4"
        }
    }
}
```

RolLib
```json
{
    "name": "rol/rollib4",
    "repositories": [
        {
            "type": "path",
            "url": "../../SBLib2/DataSource/"
        },
        {
            "type": "composer",
            "url": "http://composer.gisi-interactive.net/"
        }
    ],
    "require": {
        "ext-pdo_mysql": "*",
        "sblib/sblib": "0.1.x-dev",
        "sblib2/core": "v0.0.1",
        "sblib2/datasource": "@dev",
        "pimple/pimple": "~3.0",
        "monolog/monolog": "~1.17",
        "phpoffice/phpexcel": "1.8.0"
    },
    "require-dev": {
        "clue/graph": "v0.9.0",
        "graphp/graphviz": "v0.2.1",
        "graphp/algorithms": "v0.8.1"
    },
    "autoload": {
        "classmap": [
            "mediatheque/",
            "ROL/TemplateAbstract.php",
            "ROL/Form/ZendX/Validate/"
        ],
        "psr-4": {
            "Acc\\": "Acc/",
            "Admin\\": "Admin/",
            "AE\\": "AE/",
            "AR\\": "AR/",
            "Archive\\": "Archive/",
            "ATEX\\": "ATEX/",
            "Client\\": "Client/",
            "Com\\": "Com/",
            "Conf\\": "Conf/",
            "FC\\": "FC/",
            "Forum\\": "Forum/",
            "Inc\\": "Inc/",
            "PA\\": "PA/",
            "PC\\": "PC/",
            "PM\\": "PM/",
            "Reg\\": "Reg/",
            "Report\\": "Report/",
            "ROL\\": "ROL/",
            "Test\\": "Test/",
            "Urg\\": "Urg/",
            "Vig\\": "Vig/",
            "Waste\\": "Waste/"
        }
    },
    "extra": {
        "branch-alias": {
            "dev-develop": "0.1.x-dev"
        }
    }
}
```
