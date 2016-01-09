# php-spellchecker

Spell check html files

It's working with :

*   [LanguageTool](https://www.languagetool.org/)
*   [Php Enchant](http://php.net/manual/en/book.enchant.php)
*   [Guzzle](http://docs.guzzlephp.org)
*   [Symfony Finder Component](http://symfony.com/doc/2.3/components/finder.html)
*   [Glicer Simply-html Component](https://github.com/emmanuelroecker/php-simply-html)

## Install LanguageTool Server

Download and install [LanguageTool 2.8 stand-alone for desktop](https://www.languagetool.org/) in a directory.

Java must be installed

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

    //define languagetool directory, language to check and languagetool port used
    $spellchecker  = new GlSpellChecker("C:\\Glicer\\LanguageTool-2.8\\", "fr", "fr_FR",8081);

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
    $spellchecker  = new GlSpellChecker("C:\\Glicer\\LanguageTool-2.8\\", "fr", "fr_FR",8081);

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

Docker must be installed :

```console
docker pull silviof/docker-languagetool
docker run --rm -p 8010:8010 silviof/docker-languagetool
```

Change LanguageTool ip/port in phpunit.xml.dist

Launch from command line :

```console
vendor\bin\phpunit
```
## License MIT

## Contact

Authors : Emmanuel ROECKER & Rym BOUCHAGOUR

[Web Development Blog - http://dev.glicer.com](http://dev.glicer.com)