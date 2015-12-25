<?php
/**
 * Spell check html files
 *
 * PHP version 5.4
 *
 * @category  GLICER
 * @package   GlSpellChecker
 * @author    Emmanuel ROECKER
 * @author    Rym BOUCHAGOUR
 * @copyright 2015 GLICER
 * @license   MIT
 * @link      http://dev.glicer.com/
 *
 * Created : 04/05/15
 * File : GlSpellChecker.php
 *
 */

namespace GlSpellChecker;

use GlHtml\GlHtml;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;


/**
 * Class GlSpellChecker
 * @package GlSpellChecker
 */
class GlSpellChecker
{
    /**
     * @var int
     */
    private $languageToolServerPort = 8081;

    /**
     * @var string
     */
    private $languageToolLanguage = 'fr';

    /**
     * @var Process $languagetoolServer ;
     */
    private $languagetoolServer = null;

    /**
     * @var Client
     */
    private $languagetoolClientHttp;


    /**
     * @var string
     */
    private $enchantLanguage = "fr_FR";
    private $enchantDictionnary;
    private $enchantBroker;

    /**
     * @param string $languageToolDirectory
     * @param string $languageToolLanguage
     * @param string $enchantLanguage
     * @param int    $languageToolServerPort
     *
     * @throws \Exception
     */
    public function __construct(
        $languageToolDirectory,
        $languageToolLanguage,
        $enchantLanguage,
        $languageToolServerPort = 8081
    ) {
        $this->languageToolLanguage   = $languageToolLanguage;
        $this->enchantLanguage        = $enchantLanguage;
        $this->languageToolServerPort = $languageToolServerPort;
        $this->startLanguageToolServer($languageToolDirectory);

        $this->languagetoolClientHttp = new Client();

        $this->enchantBroker = enchant_broker_init();

        if (!enchant_broker_dict_exists($this->enchantBroker, $this->enchantLanguage)) {
            throw new \Exception("Cannot find dictionnaries for enchant");
        } else {
            $this->enchantDictionnary = enchant_broker_request_dict($this->enchantBroker, $this->enchantLanguage);
        }
    }

    public function __destruct()
    {
        $this->stopLanguageToolServer();
        enchant_broker_free_dict($this->enchantDictionnary);
        enchant_broker_free($this->enchantBroker);
    }

    /**
     * @param string                   $title
     * @param GlSpellCheckerSentence[] $sentences
     *
     * @return string
     */
    public function convertToHtml($title, $sentences)
    {
        $html = '<!DOCTYPE HTML>';
        $html .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<title>' . $title . '</title>';
        $html .= '<style>';
        $html .= '.error {  color: red  }';

        $html .= '.tooltip
                    {
                        display: inline;
                        position: relative;
                        text-decoration: none;
                        top: 0px;
                        left: 0px;
                    }';

        $html .= '.tooltip:hover:after
                    {
                        background: #333;
                        background: rgba(0,0,0,.8);
                        border-radius: 5px;
                        top: -5px;
                        color: #fff;
                        content: attr(data-tooltip);
                        left: 160px;
                        padding: 5px 15px;
                        position: absolute;
                        z-index: 98;
                        width: 150px;
                    }';
        $html .= '</style>';
        $html .= '</head><body>';

        foreach ($sentences as $sentence) {
            $html .= '<div class="sentence">';
            $text   = $sentence->getText();
            $errors = $sentence->mergeErrors();

            if (count($errors) <= 0) {
                $html .= $text;
                $html .= '</div>';
                continue;
            }

            $cons  = "";
            $start = 0;
            foreach ($errors as $error) {
                $offset = $error->getOffset();
                $length = $error->getLength();
                $cons .= mb_substr($text, $start, $offset - $start, 'UTF-8');

                $tooltip = $error->getMessage();
                $suggs   = $error->getSuggestions();
                if (count($suggs) > 0) {
                    $tooltip .= " : " . $suggs[0];
                }
                $zone = mb_substr($text, $offset, $length, 'UTF-8');
                $cons .= '<span class="error tooltip" data-tooltip="' . $tooltip . '">' . $zone . '</span>';

                $start = $offset + $length;
            }
            $cons .= mb_substr($text, $start, mb_strlen($text) - $start, 'UTF-8');

            $html .= $cons;
            $html .= '</div>';
        }
        $html .= '<br><br><br></body></html>';

        return $html;
    }

    public function checkYamlFiles(
        Finder $files,
        array    $fields,
        callable $checkfilestart,
        callable $checksentence,
        callable $checkfileend
    ) {
        $results = [];
        /**
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            try {
                $data = Yaml::parse(
                            file_get_contents(
                                $file->getRealPath()
                            )
                );
            } catch (ParseException $e) {
                throw new \Exception("Unable to parse YAML string: {$e->getMessage()}");
            }
            $sentences = [];
            foreach ($data as $item) {
                foreach ($item as $key => $valueitem) {
                    foreach ($fields as $field) {
                        if ($key == $field) {
                            $sentences[] = $valueitem;
                        }
                    }
                }
            }
            $checkfilestart($file, count($sentences));
            $sentences = $this->checkSentences(
                              $sentences,
                                  $checksentence
            );
            $htmlcode  = $this->convertToHtml($file->getFilename(), $sentences);

            $checkerfile = sys_get_temp_dir() . "/" . uniqid("spellcheck") . ".html";
            file_put_contents($checkerfile, $htmlcode);
            $results[] = $checkerfile;

            $checkfileend();
        }

        return $results;
    }

    /**
     * @param Finder   $files
     * @param callable $checkfilestart
     * @param callable $checksentence
     * @param callable $checkfileend
     *
     * @return array
     */
    public function checkHtmlFiles(
        Finder $files,
        callable $checkfilestart,
        callable $checksentence,
        callable $checkfileend
    ) {
        $results = [];
        /**
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            $html = file_get_contents($file->getRealPath());
            $html = new GlHtml($html);

            $title = $html->get("head title");

            if ($title && sizeof($title) > 0) {
                $title = $title[0]->getText();
            } else {
                $title = $file->getFilename();
            }

            $sentences = $html->getSentences();
            $checkfilestart($file, count($sentences));
            $sentences = $this->checkSentences(
                              $sentences,
                                  $checksentence
            );
            $htmlcode  = $this->convertToHtml($title, $sentences);

            $checkerfile = sys_get_temp_dir() . "/" . uniqid("spellcheck") . ".html";
            file_put_contents($checkerfile, $htmlcode);
            $results[] = $checkerfile;

            $checkfileend();
        }

        return $results;
    }

    /**
     * @param array    $sentences
     *
     * @param callable $closure
     *
     * @return GlSpellCheckerSentence[]
     */
    public
    function checkSentences(
        array $sentences,
        callable $closure
    ) {
        $url              = "http://localhost:{$this->languageToolServerPort}";
        $sentencesChecked = [];
        foreach ($sentences as $sentence) {
            $response        = $this->languagetoolClientHttp->get(
                                                            $url,
                                                                [
                                                                    'query' => [
                                                                        'language' => $this->languageToolLanguage,
                                                                        'text'     => $sentence
                                                                    ]
                                                                ]
            );
            $xml             = $response->getBody()->getContents();
            $glxml           = new GlHtml($xml);
            $errors          = $glxml->get('error');
            $sentenceChecked = new GlSpellCheckerSentence($sentence);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $msg    = $error->getAttribute('msg');
                    $offset = (int)$error->getAttribute('offset');
                    $length = (int)$error->getAttribute('errorlength');
                    $suggs  = [];
                    $word   = null;
                    if ($error->getAttribute('locqualityissuetype') == 'misspelling') {
                        $word        = mb_substr($sentence, $offset, $length, 'UTF-8');
                        $wordcorrect = enchant_dict_check($this->enchantDictionnary, $word);
                        if (!$wordcorrect) {
                            $suggs = enchant_dict_suggest($this->enchantDictionnary, $word);
                        }
                    }
                    $glerror = new GlSpellCheckerError($msg, $offset, $length, $word, $suggs);
                    $sentenceChecked->addError($glerror);
                }
            }
            $sentencesChecked[] = $sentenceChecked;
            $closure($sentence);
        }

        return $sentencesChecked;
    }

    /**
     * @param string $directory
     */
    private function startLanguageToolServer($directory)
    {
        $jar                      = $directory . "languagetool-server.jar";
        $command                  = "java -cp $jar org.languagetool.server.HTTPServer --port {$this->languageToolServerPort}";
        $this->languagetoolServer = new Process($command);
        $this->languagetoolServer->start();
        sleep(3);
    }

    private function stopLanguageToolServer()
    {
        $this->languagetoolServer->stop();
        $this->languagetoolServer = null;
    }
} 