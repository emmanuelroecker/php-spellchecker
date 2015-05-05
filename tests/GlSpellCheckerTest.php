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

        $spellchecker = new GlSpellChecker(LANGUAGETOOL_DIR, "fr", "fr_FR", LANGUAGETOOL_PORT);

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
        $this->assertEquals(
             $sentences[1]->mergeErrors()[0]->getMessage(),
                 "Cette phrase ne commence pas par une majuscule Faute de frappe possible trouvée"
        );
    }

    public function testCheck2()
    {
        $spellchecker = new GlSpellChecker(LANGUAGETOOL_DIR, "fr", "fr_FR", LANGUAGETOOL_PORT);

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

        $html      = new GlHtml(file_get_contents($results[0]));
        $sentences = $html->getSentences();

        $this->assertEquals($sentences[0], "Est-ce que le code pousse sur les arbres ? - Blog de développement web");
    }
}