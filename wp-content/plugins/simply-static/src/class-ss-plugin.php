<?php

namespace Simply_Static;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class
 */
class Plugin {

	/**
	 * The slug of the plugin; used in actions, filters, i18n, table names, etc.
	 *
	 * @var string
	 */
	const SLUG = 'simply-static';

	/**
	 * Singleton instance.
	 *
	 * @var Simply_Static
	 */
	protected static $instance = null;

	/**
	 * An instance of the options structure containing all options for this plugin
	 *
	 * @var Simply_Static\Options
	 */
	protected $options = null;

	/**
	 * View object.
	 *
	 * @var \Simply_Static\View
	 */
	protected $view = null;

	/**
	 * Archive creation process
	 *
	 * @var \Simply_Static\Archive_Creation_Job
	 */
	protected $archive_creation_job = null;

	/**
	 * Current page name.
	 *
	 * @var string
	 */
	protected $current_page = '';

	/**
	 * @var null|\Simply_Static\Page_Handlers
	 */
	protected $page_handlers = null;

	/**
	 * @var null|\Simply_Static\Integrations
	 */
	protected $integrations = null;


	/**
	 * Return an instance of the Simply Static plugin
	 * @return Simply_Static
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->includes();

			// Run export via WP-Cron.
			add_action( 'simply_static_site_export_cron', array( self::$instance, 'run_static_export' ) );

			// Filters.
			add_filter( 'simplystatic.archive_creation_job.task_list', array(
				self::$instance,
				'filter_task_list'
			), 10, 2 );

			// Handle Basic Auth.
			add_filter( 'http_request_args', array( self::$instance, 'add_http_filters' ), 10, 2 );

			// Maybe clear local directory.
			add_action( 'ss_after_setup_task', array( self::$instance, 'maybe_clear_directory' ) );

			self::$instance->integrations = new Integrations();
			self::$instance->integrations->load();

			self::$instance->options              = Options::instance();
			self::$instance->view                 = new View();
			self::$instance->archive_creation_job = new Archive_Creation_Job();
			self::$instance->page_handlers        = new Page_Handlers();

			$page                         = isset( $_GET['page'] ) ? $_GET['page'] : '';
			self::$instance->current_page = $page;

			// Maybe run upgrade.
			Upgrade_Handler::run();

			// Multisite.
			if ( is_multisite() ) {
				Multisite::get_instance();
			}

			// Plugin compatibility.
			Plugin_Compatibility::get_instance();

			// Boot up admin.
			Admin_Settings::get_instance();
		}

		return self::$instance;
	}

	public function get_integration( $integration ) {
		$integrations = $this->integrations->get_integrations();
		if ( empty( $integrations[ $integration ] ) ) {
			return null;
		}

		$class = $integrations[ $integration ];

		return new $class();
	}

	/**
	 * Include required files
	 *
	 * @return void
	 */
	private function includes() {
		$path = plugin_dir_path( dirname( __FILE__ ) );
		require_once $path . 'src/class-ss-phpuri.php';
		require_once $path . 'src/class-ss-options.php';
		require_once $path . 'src/class-ss-view.php';
		require_once $path . 'src/class-ss-url-extractor.php';
		require_once $path . 'src/class-ss-url-fetcher.php';
		require_once $path . 'src/class-ss-archive-creation-job.php';
		require_once $path . 'src/tasks/traits/trait-can-transfer.php';
		require_once $path . 'src/tasks/class-ss-task.php';
		require_once $path . 'src/tasks/class-ss-setup-task.php';
		require_once $path . 'src/tasks/class-ss-fetch-urls-task.php';
		require_once $path . 'src/tasks/class-ss-transfer-files-locally-task.php';
		require_once $path . 'src/tasks/class-ss-simply-cdn-task.php';
		require_once $path . 'src/tasks/class-ss-create-zip-archive.php';
		require_once $path . 'src/tasks/class-ss-wrapup-task.php';
		require_once $path . 'src/tasks/class-ss-cancel-task.php';
		require_once $path . 'src/tasks/class-ss-generate-404-task.php';
		require_once $path . 'src/handlers/class-ss-page-handler.php';
		require_once $path . 'src/class-ss-query.php';
		require_once $path . 'src/models/class-ss-model.php';
		require_once $path . 'src/models/class-ss-page.php';
		require_once $path . 'src/class-ss-diagnostic.php';
		require_once $path . 'src/class-ss-sql-permissions.php';
		require_once $path . 'src/class-ss-upgrade-handler.php';
		require_once $path . 'src/class-ss-util.php';
		require_once $path . 'src/class-page-handlers.php';
		require_once $path . 'src/class-integrations.php';
		require_once $path . 'src/admin/inc/class-ss-admin-settings.php';
		require_once $path . 'src/admin/inc/class-ss-migrate-settings.php';
		require_once $path . 'src/class-ss-multisite.php';
		require_once $path . 'src/class-ss-plugin-compatibility.php';
	}

	/**
	 * Old method to include admin menu.
	 *
	 * @return void
	 */
	public function add_plugin_admin_menu() {
		// Deprecated, only for upgrade support.
	}

	/**
	 * Handle static export.
	 *
	 * @param int $blog_id given blog id.
	 *
	 * @return void
	 */
	public function run_static_export( $blog_id = 0 ) {
		if ( ! $blog_id ) {
			$blog_id = get_current_blog_id();
		}
		do_action( 'ss_before_static_export', $blog_id );

		$this->archive_creation_job->start( $blog_id );

		// Exit if Basic Auth but no credentials were provided.
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			$options         = get_option( 'simply-static' );
			$basic_auth_user = $options['http_basic_auth_username'];
			$basic_auth_pass = $options['http_basic_auth_password'];

			if ( empty( $basic_auth_user ) && empty( $basic_auth_pass ) ) {
				// Cancel export.
				$message = __( 'Missing Basic Auth credentials - you need to configure the Basic Auth credentials in Simply Static -> Settings -> Misc -> Basic Auth to continue the export.', 'simply-static' );
				$this->archive_creation_job->cancel();
				$this->archive_creation_job->save_status_message( $message, 'error' );

				// Reset logs.
				$options['archive_name']       = null;
				$options['archive_start_time'] = null;
				$options['archive_end_time']   = null;

				update_option( 'simply-static', $options );
			}
		}
	}

	/**
	 * Handle cancel archive job.
	 *
	 * @return void
	 */
	public function cancel_static_export() {
		$this->archive_creation_job->cancel();
	}

	/**
	 * Get activity log data.
	 *
	 * @param int $blog_id given blog id.
	 *
	 * @return mixed
	 */
	public function get_activity_log( $blog_id = 0 ) {
		$blog_id = $blog_id ?: get_current_blog_id();

		do_action( 'ss_before_render_activity_log', $blog_id );

		$log = $this->options->get( 'archive_status_messages' );

		do_action( 'ss_after_render_activity_log', $blog_id );

		return $log;
	}

	/**
	 * Get export log data.
	 *
	 * @param int $per_page given per page.
	 * @param int $current_page given current page.
	 * @param int $blog_id given blog id.
	 *
	 * @return array
	 */
	public function get_export_log( $per_page, $current_page = 1, $blog_id = 0 ) {

		$blog_id = $blog_id ?: get_current_blog_id();

		do_action( 'ss_before_render_export_log', $blog_id );

		$offset = ( intval( $current_page ) - 1 ) * intval( $per_page );

		$static_pages = apply_filters(
			'ss_total_pages_log',
			Page::query()
			    ->limit( $per_page )
			    ->offset( $offset )
			    ->order( 'http_status_code DESC' )
			    ->find()
		);

		$http_status_codes  = Page::get_http_status_codes_summary();
		$total_static_pages = array_sum( array_values( $http_status_codes ) );
		$total_pages        = ceil( $total_static_pages / $per_page );

		do_action( 'ss_after_render_export_log', $blog_id );

		$static_pages_formatted = [];

		foreach ( $static_pages as $static_page ) {
			$msg                = '';
			$parent_static_page = $static_page->parent_static_page();
			if ( $parent_static_page ) {
				$display_url = Util::get_path_from_local_url( $parent_static_page->url );
				$msg         .= "<a href='" . $parent_static_page->url . "'>" . sprintf( __( 'Found on %s', 'simply-static' ), $display_url ) . "</a>";
			}
			if ( $msg !== '' && $static_page->status_message ) {
				$msg .= '; ';
			}
			$msg .= $static_page->status_message;

			$information = [
				'id'          => $static_page->id,
				'url'         => $static_page->url,
				'processable' => in_array( $static_page->http_status_code, Page::$processable_status_codes ),
				'code'        => $static_page->http_status_code,
				'notes'       => $msg,
				'error'       => $static_page->error_message,
			];

			$static_pages_formatted[] = $information;
		}

		return [
			'static_pages'       => $static_pages_formatted,
			'total_static_pages' => $total_static_pages,
			'total_pages'        => $total_pages,
			'status_codes'       => $http_status_codes,
		];
	}

	/**
	 * Starts the archive creation job.
	 *
	 * @return Archive_Creation_Job|null
	 */
	public function get_archive_creation_job() {
		return $this->archive_creation_job;
	}

	/**
	 * Set HTTP Basic Auth for wp-background-processing
	 *
	 * @param array $parsed_args given args.
	 * @param string $url given URL.
	 *
	 * @return array
	 */
	public function add_http_filters( $parsed_args, $url ) {
		// Check for Basic Auth credentials.
		if ( strpos( $url, get_bloginfo( 'url' ) ) !== false ) {
			$digest = self::$instance->options->get( 'http_basic_auth_digest' );

			if ( $digest ) {
				$parsed_args['headers']['Authorization'] = 'Basic ' . $digest;
			}
		}

		// Check for Freemius.
		if ( false === strpos( $url, '://api.freemius.com' ) ) {
			return $parsed_args;
		}

		if ( empty( $parsed_args['headers'] ) ) {
			return $parsed_args;
		}

		foreach ( $parsed_args['headers'] as $key => $value ) {
			if ( 'Authorization' === $key ) {
				$parsed_args['headers']['Authorization2'] = $value;
			} else if ( 'Authorization2' === $key ) {
				$parsed_args['headers']['Authorization'] = $value;
				unset( $parsed_args['headers'][ $key ] );
			}
		}

		return $parsed_args;
	}

	/**
	 * Return the task list for the Archive Creation Job to process
	 *
	 * @param array $task_list The list of tasks to process.
	 * @param string $delivery_method The method of delivering static files.
	 *
	 * @return array The list of tasks to process.
	 */
	public function filter_task_list( $task_list, $delivery_method ): array {
		array_push( $task_list, 'setup', 'fetch_urls' );

		$generate_404 = $this->options->get( 'generate_404' );

		// Add 404 task
		if ( $generate_404 ) {
			$task_list[] = 'generate_404';
		}

		if ( 'zip' === $delivery_method ) {
			$task_list[] = 'create_zip_archive';
		} elseif ( 'local' === $delivery_method ) {
			$task_list[] = 'transfer_files_locally';
		} elseif ( 'simply-cdn' === $delivery_method ) {
			$task_list[] = 'simply_cdn';
		}
		$task_list[] = 'wrapup';

		return $task_list;
	}


	/**
	 * Maybe clear local directory before export.
	 *
	 * @return void
	 */
	public function maybe_clear_directory() {
		// Check the export type.
		$use_single            = get_option( 'simply-static-use-single' );
		$use_build             = get_option( 'simply-static-use-build' );
		$clear_local_directory = apply_filters( 'ss_clear_local_directory', empty( $use_build ) && empty( $use_single ) && $this->options->get( 'clear_directory_before_export' ) && 'local' === $this->options->get( 'delivery_method' ) );

		// Clear out the local directory before copying files.
		if ( $clear_local_directory ) {
			$local_dir = apply_filters( 'ss_local_dir', $this->options->get( 'local_dir' ) );

			// Make sure the directory exists and is not empty.
			$iterator = new \FilesystemIterator( $local_dir );

			if ( is_dir( $local_dir ) && $iterator->valid() ) {
				Transfer_Files_Locally_Task::delete_local_directory_static_files( $local_dir, $this->options );
			}
		}
	}
}
