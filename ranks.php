<?php
/*
Plugin Name: Ranks
Plugin URI: http://
Description: Analytics・Facebook・Twitterからランキングを作ります。
Author: colorchips
Author URI: http://www.colorchips.co.jp/
Version: 0.1.1
*/

define('RANKS_VER', '0.1.1');
define('RANKS_DIR', dirname(__FILE__));

$ranks = new Ranks();

function is_ranks($key = null) {
	global $ranks;
	return $ranks->is_ranks($key);
}

function get_ranks($key = null) {
	global $ranks;
	return $ranks->get_ranks_pattern($key);
}
function get_ranks_patterns($key = null) {
	global $ranks;
	return $ranks->get_ranks_patterns($key);
}

function get_ranks_label($key = null) {
	global $ranks;
	return $ranks->get_ranks_label($key);
}
function the_ranks_label($key = null) {
	echo get_ranks_label($key);
}

function get_ranks_update($format = null, $key = null) {
	global $ranks;
	return $ranks->get_ranks_update($format, $key);
}
function the_ranks_update($format = null, $key = null) {
	echo get_ranks_update($format, $key);
}

function get_ranks_link($key = null) {
	global $ranks;
	return $ranks->get_ranks_link($key);
}
function the_ranks_link($key = null) {
	echo get_ranks_link($key);
}

function get_the_rank($format = '%d') {
	global $ranks;
	return $ranks->get_the_rank($format);
}
function the_rank($format = '%d') {
	echo get_the_rank($format);
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
		$this->rewrite_rule();
		$this->schedule();
	}

	public function parse_query($wp_query) {
		if (!isset($wp_query->query_vars[$this->query_var])) return;

		$key = $wp_query->query_vars[$this->query_var];
		$patterns = get_option('ranks_patterns', array());
		if (!isset($patterns[$key])) return;

		$wp_query->query_vars['post_type'] = $patterns[$key]['post_type'];
		$wp_query->query_vars['meta_key'] = $key;
		$wp_query->query_vars['orderby'] = 'meta_value_num';
		$wp_query->query_vars['order'] = 'desc';

		if (!isset($wp_query->query_vars['posts_per_page'])) {
			$wp_query->query_vars['posts_per_page'] = $patterns[$key]['posts_per_page'];
		}

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
		if (!class_exists($class_name) || !is_subclass_of($class_name, 'RanksController')) return false;

		$object = new $class_name($controller);
		$menu_slug = strtolower(__CLASS__) . '-' . $controller;
		$hook = add_options_page( $object->page_title, $object->menu_title, $object->capability, $menu_slug, array($object, '_view'));
		add_action("load-{$hook}", array($object, '_load'));
		add_action("admin_print_styles-{$hook}", array($object, 'styles'));
		add_action("admin_print_scripts-{$hook}", array($object, 'scripts'));
	}

	/**
	 * Misc
	 */

	public function get_use_post_types() {
		$patterns = get_option('ranks_patterns', array());
		$post_type = array();
		foreach ($patterns as $pattern) {
			$post_type = array_merge($post_type, $pattern['post_type']);
		}
		return array_unique($post_type);
	}

	public function get_ranks_patterns() {
		$patterns = get_option('ranks_patterns', array());
		return $patterns;
	}

	public function get_ranks_pattern($key = null) {
		if (is_null($key)) $key = get_query_var($this->query_var);
		if (!$key) return false;
		$patterns = get_option('ranks_patterns', array());
		return isset($patterns[$key]) ? $patterns[$key] : null;
	}

	public function get_ranks_label($key = null) {
		$pattern = $this->get_ranks_pattern($key);
		return $pattern['label'];
	}

	public function get_ranks_update($format = null, $key = null) {
		if (is_null($format)) $format = get_option('date_format');
		$pattern = $this->get_ranks_pattern();
		return date_i18n($format, $pattern['log'][0]['timestamp']);
	}

	public function get_ranks_link($key = null) {
		if (is_null($format)) $format = get_option('date_format');
		$pattern = $this->get_ranks_pattern();
		return home_url($pattern['rewrite_rule']);
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
		$score = get_post_meta($post->ID, $key, true);
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
	 * Rewrite Rule
	 */
	public function rewrite_rule() {
		$patterns = get_option('ranks_patterns', array());
		if (empty($patterns)) return;
		foreach (array_keys($patterns) as $key) {
			if (!$patterns[$key]['rewrite_rule']) continue;
			$regex = preg_replace('/\/$/', '/?', $patterns[$key]['rewrite_rule']);
			add_rewrite_rule($regex, 'index.php?'.$this->query_var.'='.$key, 'top');
		}
	}

	/**
	 * Schedule
	 */

	public function schedule() {
		$patterns = get_option('ranks_patterns', array());
		if (empty($patterns)) return;
		foreach (array_keys($patterns) as $key) {
			if (empty($patterns[$key]['schedule_event'])) continue;
			$schedule_hook = "ranks_schedule_{$key}";

			// スケジュールが未設定の場合、次のスケジュールを設定する
			if (!wp_next_scheduled($schedule_hook, compact('key'))) {
				// 次回実行時間を計算する
				$next_schedule = null;
				$gmt_offset = get_option('gmt_offset') * 3600;
				$hour = sprintf(' %02s:00:00', $patterns[$key]['schedule_event']['hour']);
				$now = time();
				switch ($patterns[$key]['schedule_event']['type']) {
					// 日次
					case 'daily':
						$next_schedule = strtotime('today' . $hour) - $gmt_offset;
						if ($next_schedule < $now)
						$next_schedule = strtotime('tomorrow' . $hour) - $gmt_offset;
						break;
					// 週次
					case 'weekly':
						$week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
						$next_schedule = strtotime('this ' . $week[$patterns[$key]['schedule_event']['week']] . $hour) - $gmt_offset;
						if ($next_schedule < $now)
						$next_schedule = strtotime('next ' . $week[$patterns[$key]['schedule_event']['week']] . $hour) - $gmt_offset;
						break;
					// 月次
					case 'monthly':
						$next_schedule = strtotime(date('Y-m-',strtotime('this month')) . sprintf('%02s', $patterns[$key]['schedule_event']['day']) . $hour) - $gmt_offset;
						if ($next_schedule < $now)
						$next_schedule = strtotime(date('Y-m-',strtotime('next month')) . sprintf('%02s', $patterns[$key]['schedule_event']['day']) . $hour) - $gmt_offset;
						break;
				};
				if ($next_schedule) {
					wp_schedule_single_event($next_schedule, $schedule_hook, compact('key'));
					$patterns[$key]['next_schedule'] = $next_schedule;
					update_option('ranks_patterns', $patterns);
				}
			}

			// スケジュールイベントのフック
			add_action($schedule_hook, array($this, 'schedule_event'));
		}
	}

	public function schedule_event($key) {
		if (!$key) return;
		ini_set('memory_limit', '256M');
		set_time_limit(-1);
		$pattern = $this->get_ranks_pattern($key);
		$target_account = array_keys(array_filter($pattern['rates']));
		$this->account_count($target_account, 'schedule');
		$this->pattern_score($key, 'schedule');
		$this->schedule();
	}

	/**
	 * Account Logic
	 */

	public function account_count($target_account = array(), $method = 'manual') {

		if (!is_array($target_account)) $target_account = array($target_account);

		$accounts = get_option('ranks_accounts', array());

		$count_accounts = array();
		foreach ($accounts as $account_slug => $account) {

			// 無効は除外
			if (!$account['status']) continue;

			// ターゲット指定の場合、指定以外は除外
			if ((!empty($target_account) && !in_array($account_slug, $target_account))) continue;

			$count_accounts[$account_slug] = "ranks_{$account_slug}_count";

		}

		if (!empty($count_accounts)) {

			$posts = get_posts(array(
				'post_type' => $this->get_use_post_types(),
				'posts_per_page' => -1,
				'post_status' => 'publish',
			));

			foreach ($count_accounts as $account_slug => $meta_key) {

				$start_microtime = microtime(true);

				$timestamp = current_time('timestamp');

				if ($meta_key) {

					// 既存データを破棄
					delete_post_meta_by_key($meta_key);

					foreach ($posts as $post) {
						$meta_value = $this->get_account_count($account_slug, $post->ID);
						update_post_meta($post->ID, $meta_key, $meta_value);
					}

				}

				$processing_time = microtime(true) - $start_microtime;

				// ログ初期化
				if (!isset($accounts[$account_slug]['log'])) $accounts[$account_slug]['log'] = array();

				// ログ挿入
				$lastlog = compact('timestamp', 'processing_time', 'method');
				array_unshift($accounts[$account_slug]['log'], $lastlog);

				// ログは10世代まで
				if (count($accounts[$account_slug]['log']) > 10) {
					$accounts[$account_slug]['log'] = array_slice($accounts[$account_slug]['log'], 0, 10);
				}

				// ログファイル
				if (is_writable(RANKS_DIR.'/schedule.log')) {
					$log = date_i18n('[Y-m-d H:i:s T]') . ' ' . $account_slug . ' (' . $processing_time . ' sec)';
					file_put_contents(RANKS_DIR.'/schedule.log', $log.PHP_EOL, FILE_APPEND | LOCK_EX);
				}
			}

		}

		return update_option('ranks_accounts', $accounts);
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

		require_once RANKS_DIR . '/libraries/gapi.class.php';

		if(is_null($report)){
			$accounts = get_option('ranks_accounts', array());
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
		return isset($result->shares) ? (int) $result->shares : 0;
	}

	public function get_twitter_tweet($post_id=null) {
		$result = json_decode(file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.get_permalink($post_id)));
		return isset($result->count) ? (int) $result->count : 0;
	}

	/**
	 * Pattern Logic
	 */

	public function pattern_score($key, $method = 'manual'){

		$patterns = get_option('ranks_patterns', array());
		$accounts = get_option('ranks_accounts', array());

		// 指定のパターンがなければ失敗
		if (!isset($patterns[$key])) return false;

		// 無効は除外
		if (!$patterns[$key]['status']) return false;

		// 対象記事の取得
		add_filter('posts_where', array($this, 'pattern_score_where'), 10, 2);
		$post_ago = sprintf("%s %s ago", array_shift(array_keys($patterns[$key]['term'])), $patterns[$key]['term'][$unit]);
		$posts = get_posts(array(
			'post_type' => $patterns[$key]['post_type'],
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post_ago' => $post_ago,
		));

		// 既存データを破棄
		delete_post_meta_by_key($key);

		$start_microtime = microtime(true);

		$timestamp = current_time('timestamp');

		foreach ($posts as $post) {

			$score = array();

			foreach ($accounts as $account_slug => $account) {
				if (!$account['status']) continue;
				$score[] = intval($patterns[$key]['rates'][$account_slug] * (int) get_post_meta($post->ID, "ranks_{$account_slug}_count", true));
			}

			$total_score = array_sum($score);

			update_post_meta($post->ID, $key, $total_score);
		}

		$processing_time = microtime(true) - $start_microtime;

		// ログ初期化
		if (!isset($patterns[$key]['log'])) $patterns[$key]['log'] = array();

		// ログ挿入
		$lastlog = compact('timestamp', 'processing_time', 'method');
		array_unshift($patterns[$key]['log'], $lastlog);

		// ログは10世代まで
		if (count($patterns[$key]['log']) > 10) {
			$patterns[$key]['log'] = array_slice($patterns[$key]['log'], 0, 10);
		}

		// ログファイル
		if (is_writable(RANKS_DIR.'/schedule.log')) {
			$log = date_i18n('[Y-m-d H:i:s T]') . ' ' . $key . ' (' . $processing_time . ' sec)';
			file_put_contents(RANKS_DIR.'/schedule.log', $log.PHP_EOL, FILE_APPEND | LOCK_EX);
		}

		update_option('ranks_patterns', $patterns);
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
