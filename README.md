# ptouch-print
PHP Library to print to Brother P-touch printers.

This was designed and tested with a PT-E550W, but it should also work with P750W and P710BT. 

I'm happy to add support for other devices if you raise an issue with a link to the raster documentation for the device (or a description of what changes are required).

## Usage

There is a complete usage demo in `demo.php` but briefly:

Add the library to your application:

```bash
composer require shanemcc/ptouch-print
```

Then:

```php
<?php
	require_once(__DIR__ . '/vendor/autoload.php');
	
	use ShaneMcC\PTouchPrint\RasterImage;
	use ShaneMcC\PTouchPrint\PrintJob;
	
	$someFile = __DIR__ . '/vendor/ptouch-print/images/demo-12mm.png';
	$image = RasterImage::fromFile($someFile);
	$job = new PrintJob(24);
	$job->startJob()->addImage($image)->endJob();
	
	$job->printToIP('192.168.0.100', 9100);
```

## Comments, Bugs, Feature Requests etc.

Bugs and Feature Requests should be raised on the [issue tracker on github](https://github.com/ShaneMcC/ptouch-print/issues). I'm happy to receive code pull requests via github.

Comments can be emailed to [shanemcc@gmail.com](shanemcc@gmail.com)
