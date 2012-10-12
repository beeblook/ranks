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
				$patterns[$key]['posts_per_page'] = intval($_POST['posts_per_page']);
				$patterns[$key]['term'] = array($_POST['term']['unit']=>$_POST['term']['n']);
				$patterns[$key]['rates'] = array_map('floatval', $_POST['rates']);
				$patterns[$key]['rewrite_rule'] = isset($_POST['create_rewrite_rule']) && $_POST['create_rewrite_rule'] == 'create' ? $_POST['rewrite_rule'] : null;
				$patterns[$key]['schedule_event'] = isset($_POST['enable_schedule_event']) && $_POST['enable_schedule_event'] == 'enable' ? $_POST['schedule_event'] : array();
				$patterns[$key]['next_schedule'] = null;

				wp_clear_scheduled_hook("ranks_schedule_{$key}", compact('key'));

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
		global $ranks;

		$patterns = array_merge($this->patterns, get_option('ranks_patterns', array()));
		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$key = $_GET['key'];

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		query_posts(array(
			$ranks->query_var => $key,
		));


		$pattern = $patterns[$key];

		return compact('accounts', 'key', 'pattern');
	}

	public function target_score(){
		global $ranks;

		$patterns = get_option('ranks_patterns', array());

		$key = $_GET['key'];

		if (!isset($patterns[$key])) {
			wp_redirect($this->url('index'));
			exit;
		}

		$ranks->pattern_score($key);

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
		global $ranks;

		$accounts = array_merge($this->accounts, get_option('ranks_accounts', array()));

		$account_slug = $_GET['account'];
		if (!isset($accounts[$account_slug])) {
			wp_redirect($this->url('index'));
			exit;
		}

		$timestamp = current_time('timestamp');
		$ranks->account_count($account_slug);
		$processing_time = current_time('timestamp') - $timestamp;
		$method = 'manual';
		array_unshift($accounts[$account_slug]['log'], compact('timestamp', 'processing_time', 'method'));

		wp_redirect($this->url('index'));
		exit;
	}

}
