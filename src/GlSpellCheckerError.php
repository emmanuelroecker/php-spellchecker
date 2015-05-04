<?php
/**
 * Main Class
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
 * File : GlSpellCheckerError.php
 *
 */


namespace GlSpellChecker;

/**
 * Class GlSpellCheckerError
 * @package GlSpellChecker
 */
class GlSpellCheckerError
{
    /**
     * @var array $suggs
     */
    private $suggs;

    /**
     * @var string
     */
    private $msg;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $offset;

    /**
     * @param string $msg
     * @param int    $offset
     * @param int    $length
     * @param string $word
     * @param array  $suggs
     */
    public function __construct($msg = '', $offset = null, $length = null, $word = '', $suggs = [])
    {
        $this->msg    = $msg;
        $this->offset = $offset;
        $this->length = $length;
        $this->word   = $word;
        $this->suggs  = $suggs;
    }

    /**
     * @param GlSpellCheckerError $mergeerror
     */
    public function merge(GlSpellCheckerError $mergeerror) {
        $this->msg .= ' ' . $mergeerror->msg;
        $this->word .= ' ' . $mergeerror->word;
        if ($mergeerror->length > $this->length) {
            $this->length = $mergeerror->length;
        }

        if (isset($mergeerror->suggs)) {
            if (isset($this->suggs)) {
                $this->suggs = array_merge($this->suggs,$mergeerror->suggs);
            } else {
                $this->suggs = $mergeerror->suggs;
            }
        }
    }

    /**
     * @return array
     */
    public function getSuggestions()
    {
        return $this->suggs;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->msg;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }
} 