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

class Ranks {

	public $menu_slug = 'ranks';

	public $post_types = array('post','information','enquete','blog','news','lab');

	public function __construct() {
		require_once RANKS_DIR . '/core/controller.php';
		require_once RANKS_DIR . '/core/view.php';
		add_action('admin_init', array($this, 'admin_init'));
		add_action('admin_menu', array($this, 'admin_menu'));
	}

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


	public function src($path) {
		return plugins_url($path, __FILE__);
	}

	public function url($controller, $action = 'index') {
		$url = admin_url('options-general.php');
		$url = add_query_arg('page', strtolower(__CLASS__) . '-' . $controller, $url);
		if ($action != 'index') $url = add_query_arg('action', $action);
		return $url;
	}











	function old_main() {
		//add_action('init', array($this, 'init'));
		//add_action('parse_request', array($this, 'parse_request'));
		
		add_action('admin_init', array($this, 'admin_init'));
		add_action('widgets_init', array($this, 'widgets_init'));
		add_rewrite_rule('ranking/', 'index.php?social_ranking=analytics');
	}

	public function _init() {
		global $wp;
		$wp->add_query_var('social_ranking');
	}

	public function _parse_request($wp) {
		if(!isset($wp->query_vars['social_ranking'])) return;
		switch($wp->query_vars['social_ranking']){
			default:
				$social_ranking = $wp->query_vars['social_ranking'];
				break;
		}
		exit;
	}

	public function _admin_init() {
		if(!isset($_GET['social_ranking'])) return;
		ini_set('memory_limit', '256M');
		set_time_limit(-1);
		switch($_GET['social_ranking']){
			case 'count':
				$this->count();
				break;
			case 'score':
				$this->score();
				header('Location: /wp-admin/options-general.php?page=social_ranking');
				// header('Location: /wp-admin/?social_ranking=preview');
				break;
			case 'export':
				$this->export();
				break;
			case 'preview':
				$this->preview();
				break;
			default:
				break;
		}
		exit;
	}

	public function widgets_init() {
		$class = __CLASS__.'_Widget';
		if(class_exists($class)) register_widget($class);
	}

	public function query_posts($query=array()) {
		$query = wp_parse_args($query, array(
			'post_type' => $this->post_types,
			'posts_per_page' => -1,
			'post_status' => 'publish',
		));
		return query_posts($query);
	}

	public function set_time_limit($per_time = 1) {
		global $wp_query;
		set_time_limit($wp_query->post_count * $per_time);
	}


	#------------------------------------------------
	# ソーシャルカウントの取得
	#------------------------------------------------

	public function count() {
		//$this->clear_count();
		//$this->query_posts('posts_per_page=300');
		$per = 100;
		$p = isset($_GET['p'])?intval($_GET['p']):1;
		if($p==1) $this->clear_count();
		$this->query_posts(array(
			'posts_per_page' => $per,
			'offset' => $per * ( $p - 1 ),
		));
		$this->set_time_limit();
		while(have_posts()){
			the_post();
			$analytics = $this->get_analytics_report();
			$facebook = $this->get_facebook_count();
			$twitter = $this->get_twitter_count();
			$hatena = $this->get_hatena_count();
			$gplus = $this->get_gplus_count();
			update_post_meta(get_the_ID(), 'social_ranking_analytics', $analytics);
			update_post_meta(get_the_ID(), 'social_ranking_facebook', $facebook);
			update_post_meta(get_the_ID(), 'social_ranking_twitter', $twitter);
			update_post_meta(get_the_ID(), 'social_ranking_hatena', $hatena);
			update_post_meta(get_the_ID(), 'social_ranking_gplus', $gplus);
		}
		global $wp_query;
		if($wp_query->post_count == $per) {
			// echo '<a href="/wp-admin/?social_ranking=count&p='.($p+1).'">次の'.$per.'件</a>';
			header('Location: /wp-admin/?social_ranking=count&p='.($p+1).'');
		} else {
			// echo '<a href="/wp-admin/?social_ranking=score">スコア計算</a>';
			header('Location: /wp-admin/?social_ranking=score');
		}
	}

	public function clear_count() {
		delete_post_meta_by_key('social_ranking_facebook');
		delete_post_meta_by_key('social_ranking_twitter');
		delete_post_meta_by_key('social_ranking_hatena');
		delete_post_meta_by_key('social_ranking_gplus');
		delete_post_meta_by_key('social_ranking_analytics');
	}

	public function get_facebook_count($post_id=null) {
		$result = json_decode(file_get_contents('https://graph.facebook.com/'.get_permalink($post_id)));
		return (int) $result->shares;
	}

	public function get_twitter_count($post_id=null) {
		$result = json_decode(file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.get_permalink($post_id)));
		return (int) $result->count;
	}

	public function get_hatena_count($post_id=null) {
		$result = file_get_contents('http://api.b.st-hatena.com/entry.count?url='.get_permalink($post_id));
		return (int) $result;
	}

	public function get_gplus_count($post_id=null) {
		$result = file_get_contents('https://plusone.google.com/u/0/_/+1/fastbutton?annotation=inline&url='.get_permalink($post_id));
		return (int) preg_match("/window\.__SSR = {c: ([0-9.]+) ,a:'inline'/", $result, $matches) ? $matches[1] : 0;
	}

	public function get_analytics_report($post_id=null) {
		static $report;
		if(is_null($report)){
			$auth_token = get_option('social_ranking-gapi_auth_token', false);
			if (!$auth_token) return 0;
			$ga = new gapi(null, null, $auth_token);
			$ga->requestReportData('41710859', 'pagePath', 'pageviews', '-pageviews', null, date('Y-m-d',strtotime('3 month ago')), date('Y-m-d'), 1, 1000);
			foreach($ga->getResults() as $result) {
				$report[(string) $result] = $result->getPageviews();
			}
		}
		$url = parse_url(get_permalink($post_id));
		$pagepath = urldecode($url['path']);
		if(isset($report[$pagepath])){
			return (int) $report[$pagepath];
		}
		return 0;
	}


	#------------------------------------------------
	# ソーシャルカウントのスコア計算
	#------------------------------------------------

	public function score($points){
		$this->clear_score();
		$this->query_posts();
		while(have_posts()){
			the_post();
			$analytics = (int) get_post_meta(get_the_ID(), 'social_ranking_analytics', true);
			$facebook = (int) get_post_meta(get_the_ID(), 'social_ranking_facebook', true);
			$twitter = (int) get_post_meta(get_the_ID(), 'social_ranking_twitter', true);
			$hatena = (int) get_post_meta(get_the_ID(), 'social_ranking_hatena', true);
			$gplus = (int) get_post_meta(get_the_ID(), 'social_ranking_gplus', true);
			$score = $this->get_score(compact('facebook', 'twitter', 'analytics'));
			update_post_meta(get_the_ID(), 'social_ranking_score', $score);
		}
	}

	public function clear_score() {
		delete_post_meta_by_key('social_ranking_score');
	}

	public function get_score($points){
		$score = 0;
		foreach($points as $name => $point){
			$rate = $this->get_point_rate($name);
			$score += $point * $rate;
		}
		return $score;
	}

	public function get_point_rate($name=null){
		$rate = array(
			'facebook' 		=> 5,
			'twitter' 		=> 2,
			'hatena' 		=> 1,
			'gplus' 		=> 1,
			'analytics' 	=> 0.5,
		);
		if(is_null($name)) return $rate;
		return isset($rate[$name]) ? $rate[$name] : 1;
	}

}

class Ranks_Widget extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_social_ranking', 'description' => 'ソーシャルランキングを表示' );
		$this->WP_Widget( 'social_ranking', 'ソーシャルランキング', $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $social_ranking;
		extract( $args );
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );

		$query = array(
			'posts_per_page' => (int) $instance[ 'limit' ],
			'meta_key' => 'social_ranking_score',
			'meta_query' => array(
				array(
					'key' => 'social_ranking_score',
					'value' => '0',
					'compare' => '>',
				),
			),
			'orderby' => 'meta_value_num',
			'order' => 'desc',
		);
		$social_ranking->query_posts($query);

		if ( !have_posts() ) return;

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
?>
<section class="access-ranking">
	<h2><img src="<?php src( '/images/side/ttl-ranking.gif' ); ?>" alt="アクセスランキング" width="298" height="40"></h2>
	<table>
		<tbody>
<?php $rank = 1; while (have_posts()): the_post(); ?>
			<tr class="<?php echo 'rank_' . $rank; ?>">
				<th><?php esc_html_e( $rank ); ?></th>
				<td class="thumb"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'post-thumbnail-small' ); ?></a></td>
				<td class="summary"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
			</tr>
<?php $rank++; endwhile; ?>
		</tbody>
	</table>
	<div class="more"><a href="<?php echo home_url('ranking/'); ?>">アクセスランキングをもっと見る</a></div>
</section>
<?php
		echo $after_widget;
		wp_reset_query();
		return;
	}
	
	public function form( $instance ) {
		global $wp_post_types;
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'limit' => 5 ) );
		$title = strip_tags( $instance[ 'title' ] );
		$limit = intval( $instance[ 'limit' ] );
?>
<div id="cc-ranking-widget">
	<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
	<p><label for="<?php echo $this->get_field_id( 'limit' ); ?>">件数:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" /></p>
</div>
<?php
	}
}