<?php
/**
 * @package Akismet
 */
class Ranks_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'ranks_widget',
			__( 'Ranks Widget' ),
			array( 'description' => __( 'Display The Your Registerd Rank ' ) )
		);

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_head', array( $this, 'css' ) );
		}
	}

	function css() {
?>
<style type="text/css">

</style>

<?php
	}

	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
		}
		else {
			$title = __( 'Ranking' , 'Ranks' );
		}

    global $ranks;
    $patterns = $ranks->get_patterns();

    $my_pattern = ( isset( $instance['my_pattern'] ) ) ? esc_attr( $instance['my_pattern'] ) : "";
    $my_pattern_id = $this->get_field_id( 'my_pattern' );
		$my_pattern_name = $this->get_field_name( 'my_pattern' );
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

    <p>
      <label for="<?php echo $my_pattern_id ?>"><?php _e( 'Pattern' , 'Ranks' ); ?></label>
        <select name="<?php echo $my_pattern_name; ?>" id="<?php echo $my_pattern_id ?>">
        <?php foreach ($patterns as $pattern_key => $pattern) : ?>
          <option value="<?php echo $pattern_key; ?>" <?php selected($pattern_key, $my_pattern); ?>><?php echo $pattern['label']; ?></option>
        <?php endforeach; ?>
        </select>
      </label>
    </p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['my_pattern'] = strip_tags( $new_instance['my_pattern'] );

		return $instance;
	}

	function widget( $args, $instance ) {

    global $ranks;
		$patterns = $ranks->get_patterns();
		$accounts = $ranks->get_accounts();

		$title = $instance['title'];
		$key = $instance['my_pattern'];

		if ( is_null($key) ) {
			return;
		}

		query_posts(array(
			$ranks->query_var => $key,
		));
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
?>
    <ul>
<?php
    while(have_posts()){
      the_post();
      $counts = array();
      foreach ($accounts as $account_slug => $account) {
        if ($account['status'] && $pattern['rates'][$account_slug] > 0) {
          $counts[$account_slug] = (int) get_post_meta(get_the_ID(), "ranks_{$account_slug}_count", true);
        }
      }
?>
      <li>
        <span class="rank"><?php the_rank(); ?></span>
        <span class="post-title">
          <a class="row-title" href="<?php the_permalink(); ?>" target="_blank"><?php the_title() ?></a>
        </span>
			</td>
      </li>
<?php
    }

    wp_reset_query();
?>
    </ul>
<?php
    echo $args['after_widget'];
  }
}

function ranks_register_widgets() {
	register_widget( 'Ranks_Widget' );
}

add_action( 'widgets_init', 'ranks_register_widgets' );
