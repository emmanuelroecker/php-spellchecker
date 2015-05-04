<?php
/**
 * Spell check a sentence
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
 * File : GlSpellCheckerSentence.php
 *
 */

namespace GlSpellChecker;

/**
 * Class GlSpellCheckerError
 * @package GlSpellChecker
 */
class GlSpellCheckerSentence
{

    /**
     * @var string
     */
    private $text;

    /**
     * @var GlSpellCheckerError[] $errors
     */
    private $errors = [];

    /**
     * @param $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param GlSpellCheckerError $newerror
     */
    public function addError(GlSpellCheckerError $newerror)
    {
        $this->errors[] = $newerror;
    }

    /**
     * @return GlSpellCheckerError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return GlSpellCheckerError[]
     */
    public function mergeErrors() {
        $this->sortedErrorsByOffset();
        $errors = $this->errors;
        /**
         * @var GlSpellCheckerError[] $newerrors
         */
        $newerrors = [];
        foreach ($errors as $error) {

            $finded = false;
            foreach ($newerrors as $newerror) {
                $start = $newerror->getOffset();
                $end = $start + $newerror->getLength();
                if (($error->getOffset() >= $start) && ($error->getOffset() <= $end)) {
                    $newerror->merge($error);
                    $finded = true;
                }
            }
            if (!$finded) {
                $newerrors[] = clone $error;
            }
        }

        return $newerrors;
    }

    /**
     *
     */
    private function sortedErrorsByOffset() {
        usort(
            $this->errors,
            function (GlSpellCheckerError $a, GlSpellCheckerError $b) {
                if ($a->getOffset() == $b->getOffset()) {
                    return 0;
                }
                return ($a->getOffset() < $b->getOffset()) ? -1 : 1;
            }
        );
    }
} 