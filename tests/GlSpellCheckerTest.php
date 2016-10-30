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

use GlHtml\GlHtml;
use GlSpellChecker\GlSpellChecker;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers        \GlSpellChecker\GlSpellChecker
 * @covers        \GlSpellChecker\GlSpellCheckerError
 * @covers        \GlSpellChecker\GlSpellCheckerSentence
 * @backupGlobals disabled
 */
class GlSpellCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testCheck1()
    {
        $html = <<<EOD
<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <div><p>je fais un test</p><p>bonjur les ami</p></div>
</body>
</html>
EOD;

        $spellchecker = new GlSpellChecker("fr", "fr_FR", LANGUAGETOOL_DIR, LANGUAGETOOL_IP, LANGUAGETOOL_PORT);

        $html      = new GlHtml($html);
        $sentences = $html->getSentences();
        $sentences = $spellchecker->checkSentences(
                                  $sentences,
                                      function ($sentence) {
                                      }
        );

        $this->assertEquals($sentences[0]->getText(), "je fais un test");
        $this->assertEquals(
             $sentences[0]->mergeErrors()[0]->getMessage(),
                 "Cette phrase ne commence pas par une majuscule"
        );
        $this->assertEquals($sentences[1]->getText(), "bonjur les ami");
        $this->assertContains("Cette phrase ne commence pas par une majuscule",$sentences[1]->mergeErrors()[0]->getMessage());
        $this->assertContains("Faute de frappe possible trouvée",$sentences[1]->mergeErrors()[0]->getMessage());

    }

    public function testCheck2()
    {
        $spellchecker = new GlSpellChecker("fr", "fr_FR", LANGUAGETOOL_DIR, LANGUAGETOOL_IP, LANGUAGETOOL_PORT);

        $finder = new Finder();
        $files  = $finder->files()->in(__DIR__)->name("le-code-pour-les-nouilles.html");

        $results = $spellchecker->checkHtmlFiles(
                                $files,
                                    function (SplFileInfo $file, $nbrsentences) {
                                    },
                                    function ($sentence) {
                                    },
                                    function () {
                                    }
        );

        $html      = new GlHtml(file_get_contents($results[0]));
        $sentences = $html->getSentences();

        $this->assertEquals($sentences[0], "Est-ce que le code pousse sur les arbres ? - Blog de développement web");
    }

    public function testCheck3()
    {
        $spellchecker = new GlSpellChecker("fr", "fr_FR", LANGUAGETOOL_DIR, LANGUAGETOOL_IP, LANGUAGETOOL_PORT);

        $finder = new Finder();
        $files  = $finder->files()->in(__DIR__)->name("*.yml");

        $results = $spellchecker->checkYamlFiles(
                                $files,
                                    ['text'],
                                    function (SplFileInfo $file, $nbrsentences) {
                                    },
                                    function ($sentence) {
                                    },
                                    function () {
                                    }
        );

        $html      = new GlHtml(file_get_contents($results[0]));
        $sentences = $html->getSentences();

        $this->assertStringStartsWith("Markdown est un langage de balisage léger.", $sentences[1]);
    }

    public function testCheck4()
    {
        $spellchecker = new GlSpellChecker("fr", "fr_FR", LANGUAGETOOL_DIR, LANGUAGETOOL_IP, LANGUAGETOOL_PORT);

        $finder = new Finder();
        $files  = $finder->files()->in(__DIR__)->name("test.html");

        $results = $spellchecker->checkHtmlFiles(
                                $files,
                                    function (SplFileInfo $file, $nbrsentences) {
                                    },
                                    function ($sentence) {
                                    },
                                    function () {
                                    }
        );

        $html      = new GlHtml(file_get_contents($results[0]));
        $sentences = $html->getSentences();

        $this->assertEquals($sentences[0], "test.html");
        $this->assertEquals($sentences[1], "il n'y a pas de titre");
    }
    
    public function testConvertToHtml()
    {
        $spellchecker = new GlSpellChecker("fr", "fr_FR", LANGUAGETOOL_DIR, LANGUAGETOOL_IP, LANGUAGETOOL_PORT);

        $finder = new Finder();
        $files  = $finder->files()->in(__DIR__)->name("test2.html");

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