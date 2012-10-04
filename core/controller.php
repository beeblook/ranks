<?php

class RanksController {

	public $page_title;
	public $menu_title;
	public $capability;

	public $controller;
	public $action;
	public $attr = array();

	public function __construct($controller) {
		$this->controller = $controller;
	}

	public function styles() {
		wp_enqueue_style('ranks-style');
	}

	public function scripts() {
		wp_enqueue_script('ranks-script');
	}

	public function url($action, $params = array()) {
		global $ranks;
		$url = $ranks->url($this->controller, $action);
		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$url = add_query_arg($key, $value, $url);
			}
		}
		return $url;
	}

	public function _load() {
		$this->action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
		if (method_exists($this, $this->action)) {
			$return = $this->{$this->action}();
		}
		if (is_array($return)) {
			$this->attr = array_merge($this->attr, $return);
		}
	}

	public function _view() {
		if (!method_exists($this, $this->action)) {
			echo '<div class="wrap"><h2>ERROR</h2><p>'.$this->action.' action is not found.</p></div>';
			return;
		}
		$view = new RanksView($this);
		print $view->html();
	}

}