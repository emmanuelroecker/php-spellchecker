<?php
/**
 * Test GlSpellChecker
 *
 * PHP version 5.4
 *
 * @category  GLICER
 * @package   GlSpellChecker\Tests
 * @author    Emmanuel ROECKER
 * @author    Rym BOUCHAGOUR
 * @copyright 2015 GLICER
 * @license   MIT
 * @link      http://dev.glicer.com/
 *
 * Created : 04/05/15
 * File : GlSpellCheckerTest.php
 *
 */
namespace GlSpellChecker\Tests;

use GlSpellChecker\GlSpellChecker;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers        \GlSpellChecker\GlSpellChecker
 */
class GlSpellCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testCheck1()
    {
        $spellchecker = new GlSpellChecker("..\\LanguageTool-2.8\\", "fr", "fr_FR");

        $finder = new Finder();
        $files  = $finder->files()->in(__DIR__)->name("*.html");

        $results = $spellchecker->checkHtmlFiles(
                                $files,
                                    function (SplFileInfo $file, $nbrsentences) {
                                    },
                                    function ($sentence) {
                                    },
                                    function () {
                                    }
        );

        
    }
}