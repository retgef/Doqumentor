<?php
namespace Doqumentor;
/**
	This file is part of Doqumentor.

    Doqumentor is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Doqumentor is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Doqumentor.  If not, see <http://www.gnu.org/licenses/>.
*/
class Parser {
	
	private $string;
	private $shortDesc;
	private $longDesc;
	private $params;
	
	private function parseLines($lines) {
		foreach($lines as $line) {
			$parsedLine = $this->parseLine($line);
			
			if($parsedLine === false && empty($this->shortDesc)) {
				$this->shortDesc = implode(PHP_EOL, $desc);
				$desc = array();
			} elseif($parsedLine !== false) {
				$desc[] = $parsedLine;
			}
		}
		$this->longDesc = implode(PHP_EOL, $desc);
	}
	
	private function parseLine($line) {
		
		//Trim the whitespace from the line
		$line = trim($line);
		
		if(empty($line)) return false; //Empty line
		
		if(strpos($line, '@') === 0) {
			$param = substr($line, 1, strpos($line, ' ') - 1);
			$value = substr($line, strlen($param) + 2);
			if($this->setParam($param, $value)) return false;
		}
		
		return $line;
	}
	
	private function setupParams($type = "") {
		$params = array(
			"access"	=>	'',
			"author"	=>	'',
			"copyright"	=>	'',
			"deprecated"=>	'',
			"example"	=>	'',
			"ignore"	=>	'',
			"internal"	=>	'',
			"link"		=>	'',
			"see"		=>	'',
			"since"		=>	'',
			"tutorial"	=>	'',
			"version"	=>	''
		);
		
		$this->params = $params;
	}
	
	private function setParam($param, $value) {
		if(!array_key_exists($param, $this->params)) return false;
		
		if(empty($this->params[$param])) {
			$this->params[$param] = $value;
		} else {
			$arr = array($this->params[$param], $value);
			$this->params[$param] = $arr;
		}
		return true;
	}
	
	public function __construct($string) {
		$this->string = $string;
		$this->setupParams();
	}
	
	public function parse() {
		//Get the comment
		if(preg_match('#^/\*\*(.*)\*/#s', $this->string, $comment) === false)
			die("Error");
			
		$comment = trim($comment[1]);
		
		//Get all the lines and strip the * from the first character
		if(preg_match_all('#^\*(.*)#m', $comment, $lines) === false)
			die('Error');
		
		$this->parseLines($lines[1]);
	}
	
	public function getShortDesc() {
		return $this->shortDesc;
	}
	
	public function getDesc() {
		return $this->longDesc;
	}
	
	public function getParams() {
		return $this->params;
	}
}
/*
$a = new Parser($string);
$a->parse();
echo "Short: " . $a->getShortDesc() . PHP_EOL;
echo "Long: " . $a->getDesc() . PHP_EOL;
print_r($a->getParams()); */
?>