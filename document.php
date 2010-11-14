<?php 
namespace Doqumentor;

/**
* Doqument class
* 
* Singleton pattern
*/
class Doqument {
	
	/**
	* Store for instance of Doqument class
	*/
	private static $instance;
	
	/**
	* Stores
	*/
	private $functions 	= array();
	private $classes 	= array();
	private $constants 	= array();
	
	/**
	* Settings
	*/
	private $showall	= false;
	private $jquery		= false;
	
	/**
	* Private methods
	*/
	
	private function __construct($showall = false) {
		//Settings
		$this->showall		= $showall;
		
		//Get lists
		$functions	= get_defined_functions();
		$classes	= get_declared_classes();
		$constants	= get_defined_constants(true);
		
		$this->functions = $this->parseFunctions($functions['user']);
		if($showall) {
			$this->functions = array_merge($this->functions, $this->parseFunctions($functions['internal']));
		}
		
		if(!$showall) {
			foreach($this->parseClasses($classes) as $class) {
				if($class->isUserDefined()) $this->classes[] = $class;
			}
		} else {
			$this->classes = $this->parseClasses($classes);
		}
		if($showall) {
			foreach($constants as $constants) 
				$this->constants = array_merge($this->constants, $constants);
		} else {
			$this->constants = $constants['user'];
		}
		
		asort($this->constants);
		usort($this->functions, array($this, 'sort'));
		usort($this->classes, array($this, 'sort'));
	}
	
	/**
	* Protected methods
	*/
	
	protected function parseFunctions($functions) {
		$functionList = array();
		foreach($functions as $func) {
			$functionList[] = new \ReflectionFunction($func);
		}
		return $functionList;
	}
	
	protected function parseClasses($classes) {
		$classList = array();
		foreach($classes as $class) {
			$classList[] = new \ReflectionClass($class);
		}
		return $classList;
	}
	
	protected function sort($item1, $item2) {
		return strcmp($item1->getShortName(), $item2->getShortName());
	}
	
	protected function formatParameters($params) {
		$args = array();
		foreach($params as $param) {
			$arg = '';
			if($param->isPassedByReference()) {
				$arg .= '&';
			}
			if($param->isOptional()) {
				$arg .= '[' . $param->getname();
				if($param->isDefaultValueAvailable()) {
					$arg .= ' = ';
					if(empty($param->getDefaultValue())) $arg .= '""';
					else $arg .= $param->getDefaultValue();
				}
				$arg .= ']';
			} else {
				$arg .= $param->getName();
			}
			$args[] = $arg;
		}
		return implode(', ', $args);
	}
	
	protected function formatItem($item, $type = 'unknown') {	
		$html  = '';
		
		$html .= "<div class=\"$type\" title=\"" . strtolower($item->getShortName()) . "\">" . PHP_EOL;
		$html .= "<h2>$type " . $item->getShortName();
		
		if(is_a($item, 'ReflectionFunction'))
			$html .= "(" . $this->formatParameters($item->getParameters()) . ")";
			
		$html .= "</h2>" . PHP_EOL;
		if($comment = $item->getDocComment()) {
			$html .= "<p class=\"comment\">Comment:</p>";
			$html .= "<pre class=\"comment\">" . $comment . "</pre>";
		}
		$filename = $item->getFileName();
		if(!empty($filename)) {
			$html .= "<p class=\"info\"><span class=\"filename\">" . $filename. ": </span><span class=\"lines\">Lines " . $item->getStartLine() . " - " . $item->getEndLine() . "</span></p>" . PHP_EOL;
		}
		$html .= "</div>" . PHP_EOL;
		
		return $html;
	}
	
	protected function jquery() {
		return "<script> " . file_get_contents('jquery.js') . "</script>";
	}
	
	/**
	* Public methods
	*/
	
	public static function init($showall = false) {
		if(!isset(self::$instance))
			self::$instance = new Doqument($showall);
		
		return self::$instance;
	}
	
	public function jquerify() {
		$this->jquery = true;
		return $this;
	}
	
	/**
	* Display methods
	*/
	
	public function displayFunctions() {
		$html  = '';
		foreach($this->functions as $func) {
			$html .= $this->formatItem($func, 'function');
		}
		return $html;
	}
	
	public function displayClasses() {
		$html = '';
		foreach($this->classes as $class) {
			$html .= "<div class=\"classWrapper\">" . $this->formatItem($class, 'class');
			$methods = $class->getMethods();
			usort($methods, array($this, 'sort'));
			$html .= "<div class=\"methods\">" . PHP_EOL;
			foreach($methods as $method) {
				$html .= $this->formatItem($method, 'method');
			}
			$html .= "</div></div>" . PHP_EOL;
		}
		return $html;
	}
	
	public function displayConstants() {
		$html = '';
		if(!is_array($this->constants)) return false;
		foreach($this->constants as $const => $val) {
			$html .= "<div class=\"constant\" title=\"" . strtolower($const) . "\">" . PHP_EOL;
			$html .= "<h2>const " . $const . " = " . $val . "</h2>" . PHP_EOL;
			$html .= "</div>" . PHP_EOL;
		}
		return $html;
	}
		
	public function get() {
		$html  = '<div id="doqument">';
		if($this->jquery) {
			$html .= 'Search: <input type="text" class="search" onkeyup="search(this.value);">';
		}
		$html .= $this->displayFunctions();
		$html .= $this->displayClasses();
		if($consts = $this->displayConstants()) $html .= $consts;
		
		$html .= "</div>";
		return $html;
	}
	
	public function display() {
		echo $this->get();
		if($this->jquery) {
			echo "<div style=\"position: absolute; bottom: 10px; right: 10px\"><a href=\"#\" onclick=\"$('#doqument').dialog('open'); return false;\">[Doqument]</a></div>";
			echo $this->jquery();
		}
	}
}
?>