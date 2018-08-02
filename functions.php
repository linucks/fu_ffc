<?php
/**
 * farmurban functions
*/

$FF_STAFF_PAGES = array( '/the-staff-room/', '/zoom-room/', '/team-leader-instructions/', '/resources/', '/teacher-crib-sheets/' );
$FF_STUDENT_PAGES = array( '/forums/forum/future-food-challenge-2018/', '/shop/', '/12-week-content/', '/future-food-challenge-2018/', '/how-to-use-this-website/' );
$FF_PAGES = array_merge($FF_STAFF_PAGES, $FF_STUDENT_PAGES);
$FF_STAFF_MENUS = array( 'The Staff Room', 'Teacher\'s Forum' );
$FF_STUDENT_MENUS = array( 'Forum', 'Future Food Challenge 2018' );
$FF_MENUS = array_merge($FF_STAFF_MENUS, $FF_STUDENT_MENUS);

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

function fu_login_redirect($redirect_to, $redirect_url_specified, $user) {
    if ( ! is_wp_error( $user ) ) {
      $redirect_to = calculate_user_redirect($redirect_to, $user);
    }
    return $redirect_to;
}
add_filter('login_redirect','fu_login_redirect', 10, 3);

function calculate_user_redirect($redirect_to, $user) {
   /* Calculcate the redirect based on the user and requested page */
    global $FF_PAGES;
    global $FF_STUDENT_PAGES;
    if ( ! in_array('administrator',  $user->roles) ) {
        if ( user_is_teacher($user->ID) ) {
            if ( ! in_array( $redirect_to, $FF_PAGES ) ) {
              $redirect_to = home_url( '/the-staff-room/' );
            }
        } else {
          if ( ! in_array( $redirect_to, $FF_STUDENT_PAGES ) ) {
            $redirect_to = home_url( '/future-food-challenge-2018/' );
          }
        }
    }
    return $redirect_to;
}

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

/* Buddypress stuff below */
function page_in_array($page_array){
    //$path = parse_url(wp_get_referer(), PHP_URL_PATH);
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return in_array( $path, $page_array );
}

function is_protected_page() {
    global $FF_PAGES;
    return ( ! is_user_logged_in() && ( bp_requires_login() || page_in_array( $FF_PAGES ) ) );
}

function bp_requires_login() {
    return ( ( ! bp_is_blog_page() && ! bp_is_activation_page() && ! bp_is_register_page() ) || is_bbpress() );
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

function my_page_template_redirect()
{
    if ( page_only_for_teachers() || is_protected_page() )
    {
        wp_redirect( home_url( '/wp-login.php?redirect_to=' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ) );
        exit();
    }
}

add_action( 'template_redirect', 'my_page_template_redirect' );

function filter_nav_menu_items($menu){
    // https://wordpress.stackexchange.com/questions/233667/how-to-hide-an-item-from-a-menu-to-logged-out-users-without-a-plugin
    global $FF_MENUS;
    global $FF_STAFF_MENUS;
    if ( in_array( $menu->title, $FF_STAFF_MENUS ) && ! user_is_teacher() ) {
        $menu->_invalid = True;
    } elseif ( in_array( $menu->title, $FF_MENUS ) && ! is_user_logged_in() ) {
        $menu->_invalid = True;
    }
    return $menu; //return the filtered object
}
add_filter( 'wp_setup_nav_menu_item', 'filter_nav_menu_items', 1 );
