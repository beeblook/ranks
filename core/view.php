<?php

class RanksView {

	public $controller;
	public $attr = array();

	public function __construct($controller) {
		$this->controller = $controller;
	}

	public function html() {
		$view_file = RANKS_DIR . '/classes/views/' . $this->controller->controller . '/' . $this->controller->action . '.php';
		if (!file_exists($view_file)) return false;
		$title = $this->controller->page_title;
		extract($this->controller->attr);
		ob_start();
		include $view_file;
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function src($path) {
		global $ranks;
		return $ranks->src($path);
	}

	public function url($action, $params = array()) {
		return $this->controller->url($action, $params);
	}

	public function __get($name) {
		return $this->controller->$name;
	}

}