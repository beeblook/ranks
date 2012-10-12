<?php
/*
Plugin Name: Ranks
Plugin URI: http://
Description: Analytics・Facebook・Twitterからランキングを作ります。
Author: colorchips
Author URI: http://www.colorchips.co.jp/
Version: 1.0.0
*/

define('RANKS_VER', '1.0.0');
define('RANKS_DIR', dirname(__FILE__));

$ranks = new Ranks();

function is_ranks($key = null) {
	global $ranks;
	return $ranks->is_ranks();
}

function the_rank($format = '%d') {
	echo get_the_rank($format);
}

function get_the_rank($format = '%d') {
	global $ranks;
	return $ranks->get_the_rank($format);
}

class Ranks {

	public $query_var = 'ranks_key';
	public $menu_slug = 'ranks';
	public $template = 'ranks';

	public function __construct() {
		require_once RANKS_DIR . '/core/controller.php';
		require_once RANKS_DIR . '/core/view.php';
		add_action('init', array($this, 'init'));
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
	}

	/**
	 * Global
	 */

	public function init() {
		global $wp;
		$wp->add_query_var($this->query_var);
		add_action('parse_query', array($this, 'parse_query'));
		add_filter('template_include', array($this, 'template_include'));
		add_action('loop_start', array($this, 'loop_start'));
		add_action('loop_end', array($this, 'loop_end'));
		$this->schedule();
	}

	public function parse_query($wp_query) {
		if (!isset($wp_query->query_vars[$this->query_var])) return;

		$key = $wp_query->query_vars[$this->query_var];
		$patterns = get_option('ranks_patterns', array());
		if (!isset($patterns[$key])) return;

		$ranks_query = array(
			'post_type' => $patterns[$key]['post_type'],
			'meta_key' => $key,
			'orderby' => 'meta_value_num',
			'order' => 'desc',
			'posts_per_page' => $patterns[$key]['posts_per_page'],
		);

		$wp_query->query_vars = array_merge($wp_query->query_vars, $ranks_query);
		$wp_query->is_home = false;
		$wp_query->is_archive = true;
	}

	public function template_include($template) {
		global $wp;
		if (!isset($wp->query_vars[$this->query_var])) return $template;

		$key = $wp->query_vars[$this->query_var];
		$templates = array();

		$templates[] = "{$this->template}-{$key}.php";
		$templates[] = "{$this->template}.php";

		return get_query_template($this->template, $templates);
	}

	/**
	 * Admin
	 */

	public function admin_init() {
		wp_register_style('ranks-style', $this->src('css/ranks-admin.css'), array(), RANKS_VER, 'all');
		wp_register_script('ranks-script', $this->src('js/ranks-admin.js'), array('jquery'), RANKS_VER, false);
	}

	public function admin_menu() {
		$this->controller('setting');
	}

	public function controller($controller) {

		$classfile = RANKS_DIR . '/classes/controllers/' . $controller . '.php';
		if (!is_readable($classfile)) return false;
		require_once $classfile;

		$class_name = __CLASS__ . join('', array_map('ucfirst', explode('_', $controller))) . 'Controller';
		if (!class_exists($class_name) || !is_subclass_of($class_name, RanksController)) return false;

		$object = new $class_name($controller);
		$menu_slug = strtolower(__CLASS__) . '-' . $controller;
		$hook = add_options_page( $object->page_title, $object->menu_title, $object->capability, $menu_slug, array($object, '_view'));
		add_action("load-{$hook}", array($object, '_load'));
		add_action("admin_print_styles-{$hook}", array($object, 'styles'));
		add_action("admin_print_scripts-{$hook}", array($object, 'scripts'));
	}

	/**
	 * WordPress Loop
	 */

	public function is_ranks($key = null) {
		global $wp_query;
		if (!isset($wp_query->query_vars[$this->query_var])) return false;
		if (is_null($key)) return true;
		return $wp_query->query_vars[$this->query_var] == $key;
	}

	public function loop_start($wp_query) {
		if (!isset($wp_query->query_vars[$this->query_var])) return;
		add_filter('the_post', array($this, 'the_post'));
		$wp_query->ranks = array(
			'index' => 0,
			'rank' => 0,
			'prev' => null,
		);
	}

	public function loop_end($wp_query) {
		if (has_filter('the_post', array($this, 'the_post'))) {
			remove_filter('the_post', array($this, 'the_post'));
		}
	}

	public function the_post($post) {
		global $wp_query;
		if (!isset($wp_query->query_vars[$this->query_var])) return;
		$key = $wp_query->query_vars[$this->query_var];
		$score = get_post_meta(get_the_ID(), $key, true);
		$wp_query->ranks['index']++;
		if (is_null($wp_query->ranks['prev']) || $wp_query->ranks['prev'] != $score) {
			$wp_query->ranks['rank'] = $wp_query->ranks['index'];
			$wp_query->ranks['prev'] = $score;
		}
	}

	public function get_the_rank($format = '%d') {
		global $wp_query;
		return sprintf($format, $wp_query->ranks['rank']);
	}

	/**
	 * Schedule
	 */

	public function schedule() {
		$patterns = get_option('ranks_patterns', array());
		if (empty($patterns)) return;
		foreach (array_keys($patterns) as $key) {
			if (empty($patterns[$key]['schedule_event'])) continue;
			if (!wp_next_scheduled("ranks_schedule_{$key}")) {
				$His = sprintf(' %02s:00:00', $patterns[$key]['schedule_event']['hour']);
				switch ($patterns[$key]['schedule_event']['type']) {
					case 'daily':
						$next_schedule = strtotime('tomorrow' . $His);
						break;
					case 'weekly':
						$week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
						$next_schedule = strtotime($week[$patterns[$key]['schedule_event']['week']] . $His);
						break;
					case 'monthly':
						$next_schedule = strtotime(date('Y-m-',strtotime('this month')) . sprintf('%02s', $patterns[$key]['schedule_event']['day']) . $His);
						if ($next_schedule < time())
						$next_schedule = strtotime(date('Y-m-',strtotime('next month')) . sprintf('%02s', $patterns[$key]['schedule_event']['day']) . $His);
						break;
				}
				$patterns[$key]['next_schedule'] = $next_schedule;
				// var_dump($next_schedule, $patterns[$key]['next_schedule'], date_i18n('Y-m-d H:i:s', $patterns[$key]['next_schedule']), date('Y-m-d H:i:s', $patterns[$key]['next_schedule']));
				wp_schedule_single_event($patterns[$key]['next_schedule'], "ranks_schedule_{$key}", compact('key'));
				update_option('ranks_patterns', $patterns);
			}
			add_action("ranks_schedule_{$key}", array($this, 'schedule_event'));
		}
	}

	public function schedule_event($key) {
		ini_set('memory_limit', '256M');
		set_time_limit(-1);
		$patterns = get_option('ranks_patterns', array());
		$timestamp = current_time('timestamp');
		$this->account_count(null);
		$this->pattern_score($key);
		$processing_time = current_time('timestamp') - $timestamp;
		$method = __FUNCTION__;
		array_unshift($patterns[$key]['log'], compact('timestamp', 'processing_time', 'method'));
		update_option('ranks_patterns', $patterns);
	}

	/**
	 * Account Logic
	 */

	public function account_count($target_account = null) {

		$patterns = get_option('ranks_patterns', array());
		$post_type = array();
		foreach ($patterns as $pattern) {
			$post_type = array_merge($post_type, $pattern['post_type']);
		}
		$post_type = array_unique($post_type);

		$count_accounts = array();
		$accounts = get_option('ranks_accounts', array());
		foreach ($accounts as $account_slug => $account) {
			if (!$account['status'] || (!is_null($target_account) && $account_slug != $target_account)) continue;
			$meta_key = "ranks_{$account_slug}_count";
			delete_post_meta_by_key($meta_key);
			$count_accounts[$account_slug] = $meta_key;
		}

		query_posts(array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'post_status' => 'publish',
		));

		while (have_posts()) {
			the_post();
			foreach ($count_accounts as $account_slug => $meta_key) {
				update_post_meta(get_the_ID(), $meta_key, $this->get_account_count($account_slug));
			}
		}

		return true;
	}

	public function get_account_count($account_slug, $post_id=null) {
		switch ($account_slug) {
			case 'analytics':
				return $this->get_analytics_pageview($post_id);
			case 'facebook':
				return $this->get_facebook_like($post_id);
			case 'twitter':
				return $this->get_twitter_tweet($post_id);
			default:
				return 0;
		}
	}

	public function get_analytics_pageview($post_id=null) {
		static $report;

		if(is_null($report)){
			$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));
			if (!isset($accounts['analytics']['auth_token'])) return 0;
			$ga = new gapi(null, null, $accounts['analytics']['auth_token']);
			$unit = array_shift(array_keys($accounts['analytics']['term']));
			$n = $accounts['analytics']['term'][$unit];
			$start_date = date('Y-m-d', strtotime("$n $unit ago"));
			$end_date = date('Y-m-d');
			$start_index = 1;
			$max_resluts = 1000;
			$ga->requestReportData($accounts['analytics']['profile_id'], 'pagePath', 'pageviews', '-pageviews', null, $start_date, $end_date, $start_index, $max_resluts);
			foreach($ga->getResults() as $result) {
				$report[(string) $result] = $result->getPageviews();
			}
		}

		$url = parse_url(get_permalink($post_id));
		$pagepath = urldecode($url['path']);
		return isset($report[$pagepath]) ? (int) $report[$pagepath] : 0;
	}

	public function get_facebook_like($post_id=null) {
		$result = json_decode(file_get_contents('https://graph.facebook.com/'.get_permalink($post_id)));
		return (int) $result->shares;
	}

	public function get_twitter_tweet($post_id=null) {
		$result = json_decode(file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.get_permalink($post_id)));
		return (int) $result->count;
	}

	/**
	 * Pattern Logic
	 */

	public function pattern_score($key){

		$patterns = get_option('ranks_patterns', array());
		$accounts = get_option('ranks_accounts', array());

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		delete_post_meta_by_key($key);

		add_filter('posts_where', array($this, 'pattern_score_where'), 10, 2);

		$unit = array_shift(array_keys($patterns[$key]['term']));
		$n = $patterns[$key]['term'][$unit];
		query_posts(array(
			'post_type' => $patterns[$key]['post_type'],
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_ago' => date_i18n('Y-m-d 00:00:00', strtotime("$n $unit ago")),
		));

		$ranking = array();

		while(have_posts()){
			the_post();
			$score = array();
			foreach ($accounts as $account_slug => $account) {
				if (!$account['status']) continue;
				$score[] = intval($patterns[$key]['rates'][$account_slug] * (int) get_post_meta(get_the_ID(), "ranks_{$account_slug}_count", true));
			}
			$total_score = array_sum($score);
			update_post_meta(get_the_ID(), $key, $total_score);
			$ranking[] = array('ID' => get_the_ID(), 'score' => $total_score);
		}

		$sort = array();
		foreach ($ranking as $data) {
			$sort[] = $data['score'];
		}

		array_multisort($sort, SORT_DESC, $ranking);

		return $ranking;
	}

	public function pattern_score_where($where, $wp_query) {
		global $wpdb;
		if (!isset($wp_query->query_vars['post_ago'])) return $where;
		$where.= $wpdb->prepare(" AND {$wpdb->posts}.post_date >= %s", $wp_query->query_vars['post_ago']);
		return $where;
	}

	/**
	 * Helper
	 */

	public function src($path) {
		return plugins_url($path, __FILE__);
	}

	public function url($controller, $action = 'index') {
		$url = admin_url('options-general.php');
		$url = add_query_arg('page', strtolower(__CLASS__) . '-' . $controller, $url);
		if ($action != 'index') $url = add_query_arg('action', $action);
		return $url;
	}

}
