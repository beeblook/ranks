<?php

require_once RANKS_DIR . '/libraries/gapi.class.php';

class RanksSettingController extends RanksController {

	public $page_title = 'Ranks 設定';
	public $menu_title = 'Ranks';
	public $capability = 'edit_posts';

	public $patterns = array();

	public $accounts = array(
		'analytics' => array(
			'label' => 'Analytics',
			'status' => false,
			'auth_token' => null,
			'profile_id' => null,
			'profile_name' => null,
			'term' => array('month'=>1),
			'start_date' => null,
			'end_date' => null,
		),
		'facebook' => array(
			'label' => 'Facebook',
			'status' => false,
		),
		'twitter' => array(
			'label' => 'Twitter',
			'status' => false,
		),
	);

	public $terms = array(
		'year' => '%s年間',
		'month' => '%sヶ月',
		'week' => '%s週間',
		'day' => '%s日間',
	);

	public function index() {

		$terms = $this->terms;
		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));
		$analytics_profile = get_option('ranks_analytics_profile_name', false);

		return compact('terms', 'patterns', 'accounts', 'analytics_mailaddress', 'analytics_profile');
	}

	public function target_new() {

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$terms = $this->terms;

		if (!empty($_POST)) {

			$key = $_POST['key'];

			if (isset($patterns[$key])) {

				$message = 2;

			} else {

				$patterns[$key] = array();
				$patterns[$key]['label'] = $_POST['label'];
				$patterns[$key]['post_type'] = $_POST['post_type'];
				$patterns[$key]['term'] = array($_POST['term']['unit']=>$_POST['term']['n']);
				$patterns[$key]['rates'] = array_map('floatval', $_POST['rates']);
				update_option('ranks_patterns', $patterns);
				$message = 1;

			}

			wp_redirect($this->url('target_edit', array('key' => $key, 'message' => $message)));
			exit;

		} else {

			switch ($_GET['message']) {
				case 1: $message = '<div class="ranks-message">設定完了しました。</div>'; break;
				case 1: $message = '<div class="ranks-error">そのキーはすでに設定されています。</div>'; break;
			}

			$pattern = array(
				'label' => '名称未設定',
				'post_type' => array('post'),
				'term' => array('month'=>1),
				'rates' => array_combine(array_keys($accounts), array_fill(0, count(array_keys($accounts)), 0)),
			);

		}

		return compact('message', 'accounts', 'key', 'terms', 'pattern');
	}

	public function target_edit() {

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$key = $_GET['key'];

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		$terms = $this->terms;

		if (!empty($_POST)) {

			if (isset($_POST['clear'])) {

				unset($patterns[$key]);
				update_option('ranks_patterns', $patterns);
				wp_redirect($this->url('index'));
				exit;

			} else {

				$patterns[$key]['label'] = $_POST['label'];
				$patterns[$key]['post_type'] = $_POST['post_type'];
				$patterns[$key]['term'] = array($_POST['term']['unit']=>$_POST['term']['n']);
				$patterns[$key]['rates'] = array_map('floatval', $_POST['rates']);
				update_option('ranks_patterns', $patterns);
				$message = 1;

			}

			wp_redirect($this->url(__FUNCTION__, array('message' => $message)));
			exit;

		} else {

			switch ($_GET['message']) {
				case 1: $message = '<div class="ranks-message">設定完了しました。</div>'; break;
			}

			$pattern = $patterns[$key];

		}

		return compact('message', 'accounts', 'key', 'terms', 'pattern');
	}

	public function target_preview() {

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$key = $_GET['key'];

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		$pattern = $patterns[$key];

		query_posts(array(
			'post_type' => $pattern['post_type'],
			'posts_per_page' => 500,
			'post_status' => 'publish',
			'meta_key' => $key,
			// 'meta_query' => array(
				// array(
					// 'key' => $key,
					// 'value' => '0',
					// 'compare' => '>',
				// ),
			// ),
			'orderby' => 'meta_value_num',
			'order' => 'desc',
		));

		return compact('accounts', 'key', 'pattern');
	}

	public function target_score(){

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$key = $_GET['key'];

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		delete_post_meta_by_key($key);

		query_posts(array(
			'post_type' => $patterns[$key]['post_type'],
			'posts_per_page' => -1,
			'post_status' => 'publish',
		));

		while(have_posts()){
			the_post();
			$score = array();
			foreach ($accounts as $account_slug => $account) {
				if (!$account['status']) continue;
				$score[] = $patterns[$key]['rates'][$account_slug] * (int) get_post_meta(get_the_ID(), "ranks_{$account_slug}_count", true);
			}
			update_post_meta(get_the_ID(), $key, array_sum($score));
		}

		wp_redirect($this->url('index'));
		exit;
	}

	public function account_analytics() {

		$terms = $this->terms;
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		if (!empty($_POST)) {

			if (isset($_POST['clear'])) {

				unset($accounts['analytics']);
				update_option('ranks_accounts', $accounts);
				$message = 9;

			} elseif (isset($_POST['profile_id'])) {

				$profile_id = $_POST['profile_id'];

				$ga = new gapi(null, null, $accounts['analytics']['auth_token']);
				$account_data = $ga->requestAccountData();
				foreach ($account_data as $account) {
					if ($account->getProfileId() == $profile_id) {
						$profile_name = $account->getProfileName();
						break;
					}
				}

				$accounts['analytics']['status'] = true;
				$accounts['analytics']['profile_id'] = $profile_id;
				$accounts['analytics']['profile_name'] = $profile_name;
				update_option('ranks_accounts', $accounts);
				$message = 3;

			} elseif (isset($_POST['mailaddress']) && isset($_POST['password'])) {

				try {
					$ga = new gapi($_POST['mailaddress'], $_POST['password']);
					$auth_token = $ga->getAuthToken();
					
					$accounts['analytics']['status'] = false;
					$accounts['analytics']['auth_token'] = $auth_token;
					update_option('ranks_accounts', $accounts);
					$message = 2;
				} catch (Exception $e) {
					$message = 1;
				}

			} else {

				$accounts['analytics']['status'] = isset($_POST['enable']) && $_POST['enable'];
				$accounts['analytics']['term'] = array($_POST['term']['unit']=>$_POST['term']['n']);
				update_option('ranks_accounts', $accounts);
				$message = 3;

			}

			wp_redirect($this->url(__FUNCTION__, array('message' => $message)));
			exit;

		} else {

			switch ($_GET['message']) {
				case 1: $message = '<div class="ranks-error">メールアドレスまたはパスワードが正しくありません。</div>'; break;
				case 2: $message = '<div class="ranks-message">本サイトのPVを取得できるプロファイルを選択して下さい。</div>'; break;
				case 3: $message = '<div class="ranks-message">設定完了しました。</div>'; break;
				case 9: $message = '<div class="ranks-message">設定を削除しました。</div>'; break;
			}

			if ($accounts['analytics']['auth_token'] && !isset($accounts['analytics']['profile_id'])) {
				$ga = new gapi(null, null, $accounts['analytics']['auth_token']);
				$account_data = $ga->requestAccountData();
			} else {
				$account_data = false;
			}

		}

		return compact('message', 'terms', 'accounts', 'account_data');
	}

	public function account_facebook() {

		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		if (!empty($_POST)) {

			$accounts['facebook']['status'] = isset($_POST['enable']) && $_POST['enable'];
			update_option('ranks_accounts', $accounts);
			$message = 1;

			wp_redirect($this->url(__FUNCTION__, array('message' => $message)));
			exit;

		} else {

			switch ($_GET['message']) {
				case 1: $message = '<div class="ranks-message">設定完了しました。</div>'; break;
			}

		}

		return compact('message', 'accounts');
	}

	public function account_twitter() {

		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		if (!empty($_POST)) {

			$accounts['twitter']['status'] = isset($_POST['enable']) && $_POST['enable'];
			update_option('ranks_accounts', $accounts);
			$message = 1;

			wp_redirect($this->url(__FUNCTION__, array('message' => $message)));
			exit;

		} else {

			switch ($_GET['message']) {
				case 1: $message = '<div class="ranks-message">設定完了しました。</div>'; break;
			}

		}

		return compact('message', 'accounts');
	}

	public function account_preview() {

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$account_slug = $_GET['account'];

		if (!isset($accounts[$account_slug])) {
			wp_redirect($this->url('index'));
			exit;
		}

		$post_type = array();
		foreach ($patterns as $pattern) {
			$post_type = array_merge($post_type, $pattern['post_type']);
		}
		$post_type = array_unique($post_type);

		query_posts(array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_key' => "ranks_{$account_slug}_count",
			'meta_query' => array(
				array(
					'key' => "ranks_{$account_slug}_count",
					'value' => '0',
					'compare' => '>',
				),
			),
			'orderby' => 'meta_value_num',
			'order' => 'desc',
		));

		return compact('accounts', 'account_slug');
	}

	public function account_count() {

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$account_slug = $_GET['account'];

		if (!isset($accounts[$account_slug])) {
			wp_redirect($this->url('index'));
			exit;
		}
		// $account = $accounts[$account_slug];
		
		$meta_key = "ranks_{$account_slug}_count";

		$post_type = array();
		foreach ($patterns as $pattern) {
			$post_type = array_merge($post_type, $pattern['post_type']);
		}
		$post_type = array_unique($post_type);

		ini_set('memory_limit', '256M');
		set_time_limit(-1);

		$per = 100;
		$p = isset($_GET['p'])?intval($_GET['p']):1;

		if ($p == 1) {
			delete_post_meta_by_key($meta_key);
		}

		query_posts(array(
			'post_type' => $post_type,
			'posts_per_page' => $per,
			'offset' => $per * ( $p - 1 ),
			'post_status' => 'publish',
		));

		while(have_posts()){
			the_post();
			$count = $this->get_account_count($account_slug);
			update_post_meta(get_the_ID(), $meta_key, $count);
		}

		global $wp_query;
		if($wp_query->post_count == $per) {
			wp_redirect($this->url(__FUNCTION__, array('account' => $account_slug, 'p' => $p+1)));
		} else {
			wp_redirect($this->url('index'));
		}
		exit;

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

}
