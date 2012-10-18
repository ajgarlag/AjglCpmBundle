AjglCpmBundle
==============

The AjglCpmBundle allows you to install the CommonJS Package Manager
(CPM) and to manage javascript dependencies with it

## Prerequisites

This version of the bundle has been tested with Symfony 2.1. It requires
a JVM installed and the java command available in PATH


## Installation

Installation is a quick process:

1. Download AjglCpmBundle using composer
2. Enable the Bundle
3. Configure the bundle

### Step 1: Download AjglCpmBundle using composer

Add AjglCpmBundle in your composer.json:

```js
{
    "require": {
        "ajgl/cpm-bundle": "*"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update ajgl/cpm-bundle
```

Composer will install the bundle to your project's `vendor/ajgl` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Ajgl\Bundle\CpmBundle\AjglCpmBundle(),
    );
}
```

### Step 3: Configure the bundle

You could configure the CPM package dependencies for your app:

``` yaml
ajgl_cpm:
    // ...
    packages:
        dojo:
            version: 1.7.2
        dgrid: ~
            ....
```

### Next Steps

For the first time, you should call `php app/console cpm:cpm:install`
to download and install the CPM.

After that, you could install your javascript dependencies calling
`php app/console cpm:install`