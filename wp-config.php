define( 'WP_DEBUG', true );

if ( WP_DEBUG ) {
    @error_reporting( E_ALL );
    @ini_set( 'log_errors', true );
    @ini_set( 'log_errors_max_len', '0' );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    define( 'CONCATENATE_SCRIPTS', false );
    define( 'SAVEQUERIES', true );
}