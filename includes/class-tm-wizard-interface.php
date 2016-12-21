<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TM_Wizard_Interface' ) ) {

	/**
	 * Define TM_Wizard_Interface class
	 */
	class TM_Wizard_Interface {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for skins list.
		 *
		 * @var array
		 */
		private $skins = null;

		/**
		 * Holder for current skin data.
		 *
		 * @var array
		 */
		private $skin = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'admin_footer', array( $this, 'item_template' ) );
		}

		/**
		 * Register wizard page
		 *
		 * @return void
		 */
		public function menu_page() {

			add_menu_page(
				esc_html__( 'TemplateMonster Installation Wizard', 'tm-wizard' ),
				esc_html__( 'TM Wizard', 'tm-wizard' ),
				'manage_options',
				tm_wizard()->slug(),
				array( $this, 'render_plugin_page' ),
				'dashicons-flag',
				75
			);

		}

		/**
		 * Render plugin page
		 *
		 * @return void
		 */
		public function render_plugin_page() {

			tm_wizard()->get_template( 'page-header.php' );
			$this->dispatch();
			tm_wizard()->get_template( 'page-footer.php' );
		}

		/**
		 * Print JS item template
		 *
		 * @return void
		 */
		public function item_template() {

			if ( empty( $_GET['page'] ) || tm_wizard()->slug() !== $_GET['page'] ) {
				return;
			}

			printf(
				'<script type="text/html" id="tmpl-wizard-item">%1$s</script>',
				$this->get_item( '{{{data.slug}}}', '{{{data.name}}}' )
			);

		}

		/**
		 * Get plugin installation notice
		 *
		 * @param  string $slug Plugin slug.
		 * @param  string $name Plugin name.
		 * @return string
		 */
		public function get_item( $slug, $name ) {

			ob_start();
			$wizard_item = tm_wizard()->get_template( 'plugin-item.php' );
			$item = ob_get_clean();

			return sprintf( $item, $slug, $name, $this->get_loader() );

		}

		/**
		 * Get loader HTML
		 *
		 * @return string
		 */
		public function get_loader() {
			ob_start();
			tm_wizard()->get_template( 'loader.php' );
			return ob_get_clean();
		}

		/**
		 * Process wizard steps
		 *
		 * @return void
		 */
		public function dispatch() {

			$step = ! empty( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;

			switch ( $step ) {

				case 1:
					tm_wizard()->get_template( 'before-install.php' );
					break;

				case 2:
					tm_wizard()->get_template( 'install.php' );
					break;

				case 3:
					tm_wizard()->get_template( 'after-install.php' );
					break;
			}

		}

		/**
		 * Show before import page title
		 *
		 * @return void
		 */
		public function before_import_title() {

			$skins = $this->get_skins();

			if ( empty( $skins ) ) {
				esc_html_e( 'No data found for installation', 'tm-wizard' );
			} elseif ( 1 === count( $skins ) ) {
				esc_html_e( 'Start install', 'tm-wizard' );
			} else {
				esc_html_e( 'Select skin and start install', 'tm-wizard' );
			}

		}

		/**
		 * Return available skins list
		 *
		 * @return array
		 */
		public function get_skins() {

			if ( ! empty( $this->skins ) ) {
				return $this->skins;
			}

			$this->skins = tm_wizard_settings()->get( array( 'skins', 'advanced' ) );

			return $this->skins;
		}

		/**
		 * Setup processed skin data
		 *
		 * @param  string $slug Skin slug.
		 * @param  array  $data Skin data.
		 * @return void
		 */
		public function the_skin( $slug = null, $data = array() ) {
			$data['slug'] = $slug;
			$this->skin = $data;
		}

		/**
		 * Retrun processed skin data
		 *
		 * @return array
		 */
		public function get_skin() {
			return $this->skin;
		}

		/**
		 * Get info by current screen.
		 *
		 * @param  string $key Key name.
		 * @return mixed
		 */
		public function get_skin_data( $key = null ) {

			if ( empty( $this->skin[ $key ] ) ) {
				return false;
			}

			return $this->skin[ $key ];
		}

		/**
		 * Returns skin plugins list
		 *
		 * @param  string $slug Skin name.
		 * @return string
		 */
		public function get_skin_plugins( $slug = null ) {

			$skins = $this->get_skins();
			$skin  = isset( $skins[ $slug ] ) ? $skins[ $slug ] : false;

			if ( ! $skin ) {
				return '';
			}

			$plugins = $skin[ 'full' ];

			if ( empty( $plugins ) ) {
				return '';
			}

			$registered  = tm_wizard_settings()->get( array( 'plugins' ) );
			$plugins_str = '';
			$format      = '<div class="tm-wizard-skin-plugins__item">%s</div>';

			foreach ( $plugins as $plugin ) {

				$plugin_data = isset( $registered[ $plugin ] ) ? $registered[ $plugin ] : false;

				if ( ! $plugin_data ) {
					continue;
				}

				$plugins_str .= sprintf( $format, $plugin_data['name'] );
			}

			return $plugins_str;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of TM_Wizard_Interface
 *
 * @return object
 */
function tm_wizard_interface() {
	return TM_Wizard_Interface::get_instance();
}

tm_wizard_interface();
