<?php

/**
 * Copied from the PHPUnit documentation
 *
 * @package Attachments
 * @subpackage Tests
 */


namespace Tests\Utils;

/**
 * Class to iterate through a Comma-Separated-Value file
 *
 * @package Attachments
 * @subpackage Tests
 */
class CsvFileIterator implements \Iterator
{
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
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->file = fopen($filename, 'r');
    }

    /** Destructor */
    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }

    /** Rewind to the beginning of the file */
    public function rewind(): void
    {
        if ($this->file) {
            rewind($this->file);
            $this->current = fgetcsv($this->file, 0, ',', '"', '\\');
            $this->key = 0;
        }
    }

    /** @return if the file is valid (not at the end) */
    public function valid(): bool
    {
        return $this->file && !feof($this->file);
    }

    /** @return the key */
    public function key(): mixed
    {
        return $this->key;
    }

    /** Get the current line of data */
    public function current(): mixed
    {
        return $this->current;
    }

    /** Read the next line of data */
    public function next(): void
    {
        if ($this->file) {
            $this->current = fgetcsv($this->file, 0, ',', '"', '\\');
            $this->key++;
        }
    }
}
