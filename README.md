# php-spellchecker

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/emmanuelroecker/php-spellchecker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/emmanuelroecker/php-spellchecker/?branch=master)
[![Build Status](https://travis-ci.org/emmanuelroecker/php-spellchecker.svg?branch=master)](https://travis-ci.org/emmanuelroecker/php-spellchecker)
[![Coverage Status](https://coveralls.io/repos/emmanuelroecker/php-spellchecker/badge.svg?branch=master&service=github)](https://coveralls.io/github/emmanuelroecker/php-spellchecker?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f2022454-9dc8-424d-8b19-0764dcc5b7d1/mini.png)](https://insight.sensiolabs.com/projects/f2022454-9dc8-424d-8b19-0764dcc5b7d1)

Spell check html files

It's working with :

*   [LanguageTool](https://www.languagetool.org/)
*   [Php Enchant](http://php.net/manual/en/book.enchant.php)
*   [Guzzle](http://docs.guzzlephp.org)
*   [Symfony Finder Component](http://symfony.com/doc/2.3/components/finder.html)
*   [Glicer Simply-html Component](https://github.com/emmanuelroecker/php-simply-html)

## Install LanguageTool Server

### Use Docker

[Docker](http://www.docker.com/) must be installed

```console
docker pull silviof/docker-languagetool
docker run -d -p 8010:8081 silviof/docker-languagetool
```

### Or use stand-alone 

Java must be installed

Download and install [LanguageTool stand-alone for desktop](https://www.languagetool.org/) in a directory.

## Enchant

[PECL Enchant](http://pecl.php.net/package/enchant) can be used

## Install php-spellchecker

This library can be found on [Packagist](https://packagist.org/packages/glicer/spell-checker).

The recommended way to install is through [composer](http://getcomposer.org).

Edit your `composer.json` and add:

```json
{
    "require": {
       "glicer/spell-checker": "dev-master"
    }
}
```

And install dependencies:

```bash
php composer.phar install
```

## How to spell check html files ?

```php
<?php
    require 'vendor/autoload.php';

    use GlSpellChecker\GlSpellChecker;
    use Symfony\Component\Finder\Finder;

    //language to check, define languagetool directory, and languagetool port used
    $spellchecker  = new GlSpellChecker("fr", "fr_FR","C:\\Glicer\\LanguageTool\\",'localhost', 8081);
    // or with docker $spellchecker = new GlSpellChecker("fr","fr_FR",null,'localhost',8010);
    
    //construct list of local html files to check spell
    $finder = new Finder();
    $files  = $finder->files()->in('./public')->name("*.html");

    //launch html checking
    $filereport = $spellchecker->checkHtmlFiles(
                                        $files,
                                            function (SplFileInfo $file, $nbrsentences) {
                                                // called at beginning - $nbr sentences to check
                                            },
                                            function ($sentence) {
                                                // called each sentence to check
                                            },
                                            function () {
                                                // called at the end
                                            }
                );


    //$filereport contain fullpath to html file report
    print_r($filereport);
```

you can view $filereport with your browser

## How to spell check yaml files ?

```php
<?php
    require 'vendor/autoload.php';

    use GlSpellChecker\GlSpellChecker;
    use Symfony\Component\Finder\Finder;

    //define languagetool directory, language to check and languagetool port used
    $spellchecker  = new GlSpellChecker("fr", "fr_FR","C:\\Glicer\\LanguageTool\\",'localhost',8081);
    // or with docker $spellchecker = new GlSpellChecker("fr","fr_FR",null,'localhost',8010);
    
    //construct list of local html files to check spell
    $finder = new Finder();
    $files  = $finder->files()->in('./public')->name("*.yml");

    //launch html checking
    $filereport = $spellchecker->checkYamlFiles(
                                        $files,
                                        ['test'], //list of fields to check
                                            function (SplFileInfo $file, $nbrsentences) {
                                                // called at beginning - $nbr sentences to check
                                            },
                                            function ($sentence) {
                                                // called each sentence to check
                                            },
                                            function () {
                                                // called at the end
                                            }
                );


    //$filereport contain fullpath to html file report
    print_r($filereport);
```

## Running Tests

Change LanguageTool in phpunit.xml.dist : 
*   ip/port if you use docker server
*   directory if you use local server

Launch from command line :

```console
vendor\bin\phpunit
```
## License MIT

## Contact

Authors : Emmanuel ROECKER & Rym BOUCHAGOUR

[Web Development Blog - http://dev.glicer.com](http://dev.glicer.com)
