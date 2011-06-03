<?php
/**
 * Copied from the PHPUnit documentation
 *
 * @package Attachments_test
 * @subpackage Attachments_utils
 */


/**
 * Class to iterate through a Comma-Separated-Value file
 *
 * @package Attachments_test
 * @subpackage Attachments_utils
 */
class CsvFileIterator implements Iterator {

	/** the filename */
    protected $filename;

	/** The file handle */
    protected $file;

	/** The key */
    protected $key = 0;

	/** The current line */
    protected $current;
 
	/**
	 * Constructor
	 *
	 * @param string $filename the name of the file to parse
	 */
    public function __construct($filename) {
		$this->filename = $filename;
        $this->file = fopen($filename, 'r');
    }
 
	/** Destructor */
    public function __destruct() {
        fclose($this->file);
    }
 
	/** Rewind to the beginning of the file */
    public function rewind() {
        rewind($this->file);
        $this->current = fgetcsv($this->file);
        $this->key = 0;
    }
 
	/** @return if the file is valid (not at the end) */
    public function valid() {
        return !feof($this->file);
    }

	/** @return the key */
    public function key() {
        return $this->key;
    }
 
	/** Get the current line of data */
    public function current() {
        return $this->current;
    }
 
	/** Read the next line of data */
    public function next() {
        $this->current = fgetcsv($this->file);
        $this->key++;
    }
}
