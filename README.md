PPF
===

PHP Project Framework.

Quick start for PPF
===================

PPDF is based on a certain file structure. An example situation for a PPF project could be:

*File structure:*

```
/PPF/PPF.php
/modules/module1/
/modules/module2/init.php
/modules/module3/
/application/init.php
/index.php
```

*/index.php:*

```php
<?php
require_once(__DIR__ . DIRECTORY_SEPERATOR . 'PPF' . DIRECTORY_SEPERATOR . 'PPF.php');

PPF::init([
	// Autoloader
	'enableAutoloader' => true, // Enables the PPF autoloader
	'namespaces' => true, // Support namespaces for the autoloader, when enabled the class \Foo\Bar will be searched in the file Foo/Bar.php. When disabled the class Foo_Bar will be searched in Foo/Bar.php.

	// Modules, order is important.
	'modules' => [[
		'application',
		'modules/module1',
		'modules/module2',
		'modules/module3',
	]],
]);
```

This file is the bootstrapper of the application. It enables the PPF autoloader and registers some module/application folders. This example will actually run/include the following files:

* /index.php
  The bootstrapper of the application.
* /PPF/PPF.php
  The PPF code.
* /modules/module2/init.php
  This file could do some specific action for fully load this module.
* /application/init.php
  The actual application. This file could trigger a MVC routing system.

Use PPF's file system
=====================

## array|null|string findPath(string $file [ , string $directory = null [ , $extension = null [ , $all = false ]]] )

Find the absolute path for a given file (or directory). This function will search in all registered modules.

*Arguments:*

* string $file
  The file or directory you want to find the path for.
* string $directory
  A directory to start looking in. Can be null or an empty string to disable.
* string $extension
  An extension for the file you are looking for. Can be null or empty to disable.
* bool $all
  When true this function will return all found paths in an array. When false only the first will be returned, or null when not found.

*Return:*

This function returns an array of paths when $all is true, a string when $all is false and a path is found or null when no file is found.

*Examples:*

```php
<?php
/* Search the view 'layout' in all modules. This will try the following paths for existence:
 *  - application/views/layout.php
 *  - modules/module1/views/layout.php
 *  - modules/module2/views/layout.php
 *  - modules/module3/views/layout.php
 */
$path = PPF::findPath('layout', 'views', 'php'); 

/* Search the css file 'base' in all modules. This will try the following paths for existence:
 *  - application/css/base.css
 *  - modules/module1/css/base.css
 *  - modules/module2/css/base.css
 *  - modules/module3/css/base.css
 */
$path = PPF::findPath('base', 'css', 'css'); 
```
