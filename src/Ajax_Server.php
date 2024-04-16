<?php //phpcs:disable WordPress.Security.NonceVerification.Missing, SlevomatCodingStandard.Operators.SpreadOperatorSpacing
/**
 * Ajax_Server class file.
 *
 * @package eXtended WordPress
 */

namespace XWP;

use Oblak\WP\Traits\Singleton;
use XWP\Contracts\Hook\Accessible_Hook_Methods;
use XWP\Hook\Decorators\Action;
use XWP\Hook\Decorators\Filter;
use XWP\Hook\Invoker;

/**
 * Ajax server.
 */
class Ajax_Server {
    use Singleton;
    use Accessible_Hook_Methods;

    /**
     * The base for the ajax requests.
     *
     * @var string
     */
    protected readonly string $base;

    /**
     * Class constructor.
     */
    protected function __construct() {
        $this->base = \get_option( 'xwp_ajax_slug', 'wp-ajax' );
        Invoker::instance()
            ->load_handler( $this )
            ->register_handler( HTTP\Request_Dispatcher::class );
    }

    /**
     * Add the admin settings for the module.
     */
    #[Action( tag:'admin_init', context: Action::CTX_ADMIN )]
    protected function add_admin_settings() {
        \add_settings_field( 'xwp_ajax_slug', 'XWP AJAX base', $this->field( ... ), 'permalink', 'optional' );
    }

    /**
     * Display the ajax slug field.
     */
    public function field() {
        ?>
        <input
            name="xwp_ajax_slug"
            type="text"
            class="regular-text code"
            value="<?php echo \esc_attr( $this->base ); ?>"
            placeholder="wp-ajax"
        >
        <?php
    }

    /**
     * Save the admin settings for the module.
     */
    #[Action( tag: 'init', context: Action::CTX_ADMIN )]
    protected function save_admin_settings() {
        if ( ! isset( $_POST['permalink_structure'], $_POST['xwp_ajax_slug'] ) || ! \is_admin() ) {
            return;
        }

        //phpcs:ignore Universal.Operators
        $ajax_base = \sanitize_text_field( \wp_unslash( $_POST['xwp_ajax_slug'] ) ) ?: 'wp-ajax';

        \update_option( 'xwp_ajax_slug', $ajax_base );
    }

    /**
     * Add needed query vars.
     *
     * @param  array $vars The existing query vars.
     * @return array
     */
    #[Filter( tag: 'query_vars' )]
    protected function add_query_vars( array $vars ): array {
        return \array_merge( $vars, array( 'xwp_ajax' ) );
    }

    /**
     * Add rewrites for the module.
     *
     * @param  array $rules The existing rewrite rules.
     * @return array        The modified rewrite rules.
     */
    #[Filter( tag: 'rewrite_rules_array', priority: 120 )]
    protected function add_rewrites( array $rules ) {
        $addon_rules = array(
            $this->base . '/([^/]+)/([^/]+)/?(.*)?' => 'index.php?xwp_ajax=1',
            $this->base . '/([^/]+)/v([0-9]+)/([^/]+)/?(.*)?' => 'index.php?xwp_ajax=1',

        );

        return \array_merge( $addon_rules, $rules );
    }

    /**
     * Add the ajax vars to the head.
     */
    #[Action( tag: 'wp_head', priority: PHP_INT_MAX, context: Action::CTX_FRONTEND )]
    #[Action( tag: 'admin_head', priority: PHP_INT_MAX, context: Action::CTX_ADMIN )]
    protected function add_ajax_vars() {
        $add_vars = \apply_filters( 'xwp_ajax_vars', true );
        if ( ! \has_filter( 'xwp_ajax_controllers' ) || ! $add_vars ) {
            return;
        }

        $html = require_once __DIR__ . '/Utils/xwp-js-template.php';

        \printf(
            \str_replace( array( "\n", "\t", '  ' ), '', $html ), //phpcs:ignore WordPress.Security
            \esc_url( \xwp_ajax_url() ),
            \esc_url( \admin_url( 'admin-ajax.php' ) ),
        );
    }
}
