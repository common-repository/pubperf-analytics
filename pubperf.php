<?php
/*
Plugin Name: Pubperf Analytics
Plugin URI:  https://www.pubperf.com
Description: Pubperf Analytics plugin provides detailed performance statistics insight about your website speed, prebid analytics, advertising analytics and more.
Version:     2.0.2
Author:      Pubperf
Author URI: https://www.pubperf.com
Requires at least: 4.0
Tested up to: 6.3.4
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Pubperf' ) ) :

class WP_Pubperf {

	protected static $_instance = null;
	protected $plugin_name;
	protected $version;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		$this->plugin_name = 'wp-pubperf';
		$this->version = '2.0.0';

		add_action( 'init' , array( $this, 'init' ) );
	}

	public function init() {

		add_action( 'admin_menu', array( $this, 'pubperf_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'pubperf_admin_menu_init' ) );
		add_action( 'wp_head',  array( $this, 'pubperf_head_scripts' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'pubperf_settings_link' ) );
		add_action( 'admin_notices', array( $this, 'pubperf_admin_notice_license' ) );
	}

	/**
	 * Adds links to settings page
	 *
	 * @since  1.0.1
	 *
	 * @param  array $links Original links
	 * @return array $links Updated links
	 */
	public function pubperf_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=wp-pubperf' ),  __( 'Settings', 'wp-pubperf' ) );

		return $links;
	}

	public function pubperf_admin_menu_init() {
		register_setting( 'wp-pubperf', 'wp-pubperf_settings' );

		add_settings_section(
			'pubperf_section',
			'',
			array( $this, 'pubperf_settings_section_callback' ),
			'wp-pubperf'
		);

		add_settings_field(
			'pubperf-license',
			__( 'Your Website Key (License)', 'wp-pubperf' ),
			array( $this, 'pubperf_code_callback' ),
			'wp-pubperf',
			'pubperf_section'
		);

	}

	public function pubperf_admin_notice_license() {

		$setting = get_option( 'wp-pubperf_settings' );

		if( ! $setting['pubperf-license'] ) {
			$error = sprintf( '%s<a href="%s">%s</a>', __( 'Pubperf Analytics requires Your Website ID (License) to get started. ', 'wp-pubperf' ) , admin_url( 'admin.php?page=pubperf-options' ),  __( 'Settings', 'wp-pubperf' ) );
		?>
			<div class="error notice-error">
				<p><?php echo $error; ?></p>
			</div>
		<?php
		}
	}

	public function pubperf_settings_section_callback() {
		?>
		<div class="pubperf-plugin-logo" >
			<a href="https://www.pubperf.com/?from=wp">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/logo-black.png'; ?>" width="120" />
			</a>
		</div>
		<?php
	}

	public function pubperf_code_callback() {

		$setting = get_option( 'wp-pubperf_settings' );
		?>
		<input type='text' name='wp-pubperf_settings[pubperf-license]' value='<?php echo esc_attr($setting['pubperf-license']); ?>'><br>
		<small>
			<div class="desc">
				Get your license key at: <a target="_blank" href="https://www.pubperf.com/?utm_source=wp_license">https://www.pubperf.com/</a><br>
				Please find the license key (license-xxxxxx) at the install section.<br>
				Pubperf dashboard will begin showing data once sufficient data has been collected.
			</div>
		</small>
		<?php
	}

	public function pubperf_add_admin_menu() {


		$pubperf_admin_page = add_menu_page(
			'Pubperf Analytics',
			'Pubperf Analytics',
			'manage_options',
			'pubperf-options',
			array( $this, 'pubperf_admin_page' )
		);
	}

	public function pubperf_admin_page() {
		$setting = get_option( 'wp-pubperf_settings' );
    ?>

		<style>
		    #pubperf-left {
		    	width:670px;
		    	float: left;
		    }
		    #pubperf-right {
		    	position: absolute;
			    top: 60px;
			    right: 60px;
			    width: 292px;
			    text-align: right;
		    }
		    #pubperf-right .top {
		    	background: white;
		    	padding: 10px;
		    	text-align: center;
		    }
		    #pubperf-right .logo {
			    background: #555f80;
			    padding: 20px 10px;
			    text-align: center;
			    color: white;
		    }

		    #pubperf-right h3 {
		    	color: white;
		    }
		    #pubperf-right .button-primary {
		    	font-size: 20px;
		    }
		</style>

		<div class="wrap" id="pubperf-left">
			<form action="options.php" method="POST">
				<?php settings_fields( 'wp-pubperf' ); ?>
				<?php do_settings_sections( 'wp-pubperf' ); ?>
				<?php
				// Display account link
				if( $setting['pubperf-license'] ) {
					?>
					<p><strong><a href="https://app.pubperf.com/?from=wp" target="_blank"><?php esc_html_e( 'Go to your Pubperf dashboard', 'wp-pubperf' ); ?></a></strong></p>
					<?php
				}
				?>
				<?php submit_button(); ?>
			</form>
		</div>

		    <div id="pubperf-right">
            	<div class="top">
                    <a target="_blank" href="<?php _e( 'https://www.pubperf.com', 'pubperf' ); ?>" target="_blank">
                        <img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/logo-black.png'; ?>" width="120" />
                    </a>
                </div>
                    
                <div class='logo'>
                	<div>
                        <h3>Increase Revenue by Gaining Insight</h3>
                        <ul>
                        	<li><strong>Realtime Prebid.js and Header bidding Analytics</strong>
                        	<li><strong>Webpage Speed Analytics</strong>
                        	<li><strong>Ad Revenue Analytics</strong>
                        	<li><strong>Content Performance Analytics</strong>
                        	<li><strong>Enhanced Lighthouse Report Snapshots</strong>
                            <li><strong>24/7 Technical support</strong>
                            <li><strong>Support: support@transfon.com</strong>
                        </ul>
                        <a class="button button-primary" href="https://app.pubperf.com/app/register?from=wp" target="_blank">Free Trial</a>
                        
                    </div>
                </div>
            </div>

    <?php
	}

	public function pubperf_head_scripts() {
		$setting = get_option( 'wp-pubperf_settings' );
		if( ! $setting['pubperf-license'] || strpos($setting['pubperf-license'], 'license-') < 0) {
			return;
		}
		$setting['pubperf-license'] = str_replace('license-', '', $setting['pubperf-license']);
		?>
<!-- Pubperf Tag -->
<script async src="https://t.pubperf.com/t/<?php echo esc_attr($setting['pubperf-license']); ?>.js?from=wp"></script>
<!-- End Pubperf tag -->
		<?php
	}
}

endif;

function Pubperf() {
	return WP_Pubperf::instance();
}
Pubperf();
?>
