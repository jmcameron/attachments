<?php
class CsvFileIterator implements Iterator {
    protected $filename;
    protected $file;
    protected $key = 0;
    protected $current;
 
    public function __construct($filename) {
		$this->filename = $filename;
        $this->file = fopen($filename, 'r');
    }
 
    public function __destruct() {
        fclose($this->file);
    }
 
    public function rewind() {
        rewind($this->file);
        $this->current = fgetcsv($this->file);
        $this->key = 0;
    }
 
    public function valid() {
        return !feof($this->file);
    }
 
    public function key() {
        return $this->key;
    }
 
    public function current() {
        return $this->current;
    }
 
    public function next() {
        $this->current = fgetcsv($this->file);
        $this->key++;
    }
}
?>