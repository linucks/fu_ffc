<?php
/**
 * farmurban functions
*/
$FF_STAFF_PAGES = array( '/the-staff-room/', '/zoom-room/', '/team-leader-instructions/', '/resources/', '/teacher-crib-sheets/' );
$FF_STAFF_MENUS = array( 'The Staff Room', 'Teacher\'s Forum' );

/* For error logging */
if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

/* General Utility Functions */
function page_in_array($page_array){
    //$path = parse_url(wp_get_referer(), PHP_URL_PATH);
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return in_array( $path, $page_array );
}

function page_only_for_teachers() {
    global $FF_STAFF_PAGES;
    return ( ! user_is_teacher() && page_in_array( $FF_STAFF_PAGES ) );
}

function user_is_teacher( $user_id = null ) {
    if ($user_id === null) {
        $user_id = bp_loggedin_user_id();
    }
    $group_id = groups_get_id( 'teachers' );
    return groups_is_user_member( $user_id, $group_id );
}

function is_base_url($url) {
  $url_rtrim = rtrim($url, "/");
  return ( $url_rtrim === home_url() || $url_rtrim === "" );
}

/* Theme */
function my_theme_enqueue_styles() {
    $parent_style = 'spacious_style';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'farmurban_spacious_style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_style( 'google-fonts-dosis', 'https://fonts.googleapis.com/css?family=Dosis' );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/* Redirect specific users to set pages when they log on if requried */
function fu_login_redirect($redirect_to, $redirect_url_specified, $user) {
    if ( ! is_wp_error( $user ) ) {
      $redirect_to = calculate_user_redirect($redirect_to, $user);
    }
    return $redirect_to;
}
add_filter('login_redirect','fu_login_redirect', 10, 3);

function calculate_user_redirect($redirect_to, $user) {
   /* Calculcate the redirect based on the user and requested page */
    if ( ! in_array('administrator',  $user->roles) ) {
        if ( user_is_teacher($user->ID) &&  is_base_url($redirect_to) ) {
            /* redirect to staff room if just logging in and not requesting a particular page */
            $redirect_to = home_url( '/the-staff-room/' );
        }
    }
    return $redirect_to;
}

/* Ensure user is logged on */
function my_page_template_redirect()
{
    if ( ! is_user_logged_in() || page_only_for_teachers() )
    {
        $redirect_to = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        wp_safe_redirect( wp_login_url( $redirect_to ), 302);
        exit();
    }
}
add_action( 'template_redirect', 'my_page_template_redirect' );

/* Style the Login Page */
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo home_url( '/wp-content/uploads/2018/08/FarmUrbanLogoNew100x106.png' ); ?>);
		height:106px;
		width:100px;
		background-size: 100px 106px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
        #wp-submit {
            color:  #F4E8DB;
            background-color: #0D96A5;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
    return 'Farm Urban';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );

function fu_login_message( $message ) {
    if ( empty($message) ){
        return "<p>The Future Food Challenge is our exciting schools programme, putting young people's science and business skills to the test!</p><br/><p>To find out more <a href=\"mailto:info@farmurban.co.uk\">contact</a> the Farm Urban team.</p>";
    } else {
        return $message;
    }
}
add_filter( 'login_message', 'fu_login_message' );

/* shortcodes for adding dynamically generated urls */
function get_bp_profile() {
    return "<a href=\"" . bp_loggedin_user_domain() . "profile\">profile</a>";
}
add_shortcode( 'bp_profile', 'get_bp_profile' );

function get_bp_messages() {
    return "<a href=" . bp_loggedin_user_domain() . bp_get_messages_slug() . ">messages</a>";
}
add_shortcode( 'bp_messages', 'get_bp_messages' );

function get_bp_compose() {
    return "<a href=\"" . bp_loggedin_user_domain() . bp_get_messages_slug() . "/compose\">compose</a>";
}
add_shortcode( 'bp_compose', 'get_bp_compose' );

function filter_nav_menu_items($menu){
    // https://wordpress.stackexchange.com/questions/233667/how-to-hide-an-item-from-a-menu-to-logged-out-users-without-a-plugin
    global $FF_STAFF_MENUS;
    if ( in_array( $menu->title, $FF_STAFF_MENUS ) && ! user_is_teacher() ) {
        $menu->_invalid = True;
    }
    return $menu; //return the filtered object
}
add_filter( 'wp_setup_nav_menu_item', 'filter_nav_menu_items', 1 );
