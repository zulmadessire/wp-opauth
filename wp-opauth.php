<?php
/*
* Plugin Name: WP OPauth
* Description: Futuro plugin wp_opauth para inicio de sesion
* Author: Zulma Hernandez
* Version: 0.0.1.1
* License: GPLv2 
*/

 /**
 * Define paths
 */
define('CONF_FILE', dirname(__FILE__).'/'.'opauth.conf.php');
define('OPAUTH_LIB_DIR', dirname(__FILE__).'/lib/Opauth/');
require_once(dirname(__FILE__). "/includes/redireccion.php");

/**
* Load config
*/
if (!file_exists(CONF_FILE)){
    trigger_error('Config file missing at '.CONF_FILE, E_USER_ERROR);
    exit();
}
require CONF_FILE;

/**
 * Instantiate Opauth with the loaded config
 */
require OPAUTH_LIB_DIR.'Opauth.php';
$Opauth = new Opauth( $config );

/**
* 
*/
class Wpop
{
    
    function __construct( )
    {
        /*register_activation_hook(__FILE__, array($this, 'wpoa_activate'));
        register_deactivation_hook(__FILE__, array($this, 'wpoa_deactivate'));
        // hook load event to handle any plugin updates:
        add_action('plugins_loaded', array($this, 'wpoa_update'));*/
        // hook init event to handle plugin initialization:
        add_action('init', array($this, 'init'));
    }

    function init ()
    {
        // load scripts and css for login page
        add_action( 'login_enqueue_scripts', array( $this, 'wpop_login_scripts_styles' ) );
        // hook buttons login
        add_action( 'login_form', array( $this, 'wpop_buttons_login' ) );
        // admin menu settings
        add_action('admin_menu', array($this, 'wpop_settings_page'));
    }

    // load scripts and css for login page
    function wpop_login_scripts_styles()
    {
        wp_enqueue_style( 'wpop-opauth-css', plugins_url( 'css/opauth.css', __FILE__ ), array() );
        wp_enqueue_script( 'wpop-opauth-js', plugins_url( 'js/oauth.js', __FILE__ ), array( 'jquery' ) );
    }

    function wpop_buttons_login()
    {
        $site_url = parse_url( get_bloginfo( 'url' ) );
        $blog_url = rtrim(site_url(), "/") . "/";
        echo "blog el url: ".$blog_url;
        echo "</br>site el url: ".$site_url['path'];
        echo "</br>Url: ".site_url();
        ?>
            <p class="galogin">
                <a href="<?php echo $blog_url . "wp-login/facebook"; ?>" class="Button btn btn-primary redes"><em><span><i class="fa fa-facebook fa-fw fa-lg" aria-hidden="true"></i></span></em> Sign in with Facebook</a>
                <a href="<?php echo $blog_url . "wp-login/google"; ?>" class="Button btn btn-danger redes"><em><span><i class="fa fa-google-plus  fa-fw fa-lg"></i></span></em> Sign in with Google+</a>
            </p>
        <?php
    }

    // add the main settings page
    function wpop_settings_page() {
        add_options_page( 'WP-OPauth Options', 'WP-OPauth', 'manage_options', 'WP-OPauth', array( $this, 'wpop_settings_page_content' ) );
    }

    // add the content settings page
    function wpop_settings_page_content() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        //$blog_url = rtrim(site_url(), "/") . "/";
        //include 'wp-oauth-settings.php';
    }


}





/*function verificar_sesion(){
  add_action('init', 'checkEmail');
}*/



/*
function email_login_authenticate( $user, $username, $password ) {
    if ( is_a( $user, 'WP_User' ) )
        return $user;

    if ( !empty( $username ) ) {
        $username = str_replace( '&', '&amp;', stripslashes( $username ) );
        $user = get_user_by( 'email', $username );
        if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
            $username = $user->user_login;
    }

    return wp_authenticate_username_password( null, $username, $password );
}
remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'email_login_authenticate', 20, 3 );

function username_or_email_login() {
    if ( 'wp-login.php' != basename( $_SERVER['SCRIPT_NAME'] ) )
        return;

    ?><script type="text/javascript">
    // Form Label
    if ( document.getElementById('loginform') )
        document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'opauth' ) ); ?>';

    // Error Messages
    if ( document.getElementById('login_error') )
        document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php echo esc_js( __( 'username' ) ); ?>', '<?php echo esc_js( __( 'Username or Email' , 'opauth' ) ); ?>' );
    </script><?php
}
add_action( 'login_form', 'username_or_email_login' );*/

$wpop = new Wpop();
 ?>