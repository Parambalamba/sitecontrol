<?php
/**
* Connect style
*/
function register_styles() {
    wp_enqueue_style( 'main', get_stylesheet_uri() );
    wp_enqueue_script( 'main', get_template_directory_uri() . '/main.js', array( 'jquery' ), false, false );
}
add_action( 'wp_enqueue_scripts', 'register_styles' );

add_action( 'wp_ajax_version_control', 'version_control' );
//add_action( 'wp_ajax_nopriv_version_control', 'version_control' );

/**
 * @return void
 * Запрос версии WordPress
 */
function version_control() {
    $url = $_POST['url'];
    $route = $_POST['route'];

    $app_pass = '';
    $app = '';
    if ( have_rows( 'site_info', 'managed_sites') ) {
        while ( have_rows( 'site_info', 'managed_sites') ) {
            the_row();
            if ( get_sub_field( 'site_domain' ) == $url ) {
                $app = get_sub_field( 'site_user' );
                $app_pass = get_sub_field( 'application_password' );
            }
        }
    }
    $to_url = 'https://' . $url . '/wp-json/softswiss/v1/' . $route;
        error_log("tourl = " . $to_url);
    $args = [
        'method' => 'GET',
        'headers'   => [
            'Authorization' => 'Basic ' . base64_encode( $app . ':' . $app_pass )
        ]
    ];
    $response = wp_remote_request( $to_url, $args );
    $body = wp_remote_retrieve_body( $response );
    if ( $body ) {
        echo json_encode( array(
            'foo' => $body
        ) );
    }
    die();
}

/**
 * @return void
 * Проверка срока окончания действия ssl сертификатов
 * для всех сайтов
 */
add_action('wp_ajax_check_ssl_expire', 'check_ssl_expire');
function check_ssl_expire() {
    $sites_info = array();
    error_log("in if have rows = " . get_field( 'site_info', 'managed_sites' ) );
    if ( have_rows( 'site_info', 'managed_sites' ) ) {
        error_log("in if have rows");
        while ( have_rows( 'site_info', 'managed_sites' ) ) {
            the_row();
            $sdomain = get_sub_field( 'site_domain' );
            $url = 'ssl://' . $sdomain . ':443';
            $context = stream_context_create(
                array(
                    'ssl' => array(
                        'capture_peer_cert' => true,
                        'verify_peer'       => false, // Т.к. промежуточный сертификат может отсутствовать,
                        'verify_peer_name'  => false  // отключение его проверки.
                    )
                )
            );
            $fp = stream_socket_client($url, $err_no, $err_str, 30, STREAM_CLIENT_CONNECT, $context);
            $cert = stream_context_get_params($fp);
            if (empty($err_no)) {
                $info = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
                $date = $info['validTo_time_t'];
                $now = new DateTime();
                if ( $date - $now->getTimestamp() < 6184000 ) {
                    $text = " У сайта " . $sdomain . " заканчивается срок действия SSL сертификата";
                    $to_url = 'https://api.telegram.org/bot5401558668:AAGwMxOz01cg3V5jM-FVXWWD0g4AQ-2adF4/sendMessage?chat_id=689907150&text='.$text;
                    $args = [
                        'method' => 'GET',
                    ];
                    $response = wp_remote_request( $to_url, $args );
                    $body = wp_remote_retrieve_body( $response );
                }
                update_sub_field( 'ssl_end_date', $date, 'managed_sites' );
                $sites_info += [$sdomain => date_create( '@' . $date )->format('c')];
            }
        }
    }
    echo json_encode( array(
        'result' => json_encode( $sites_info ),
    ) );
    die();
}

/**
 * Отправка сообщения админу через телеграм бот
 */
add_action( 'wp_ajax_send_info', 'send_info' );
function send_info() {
    $info = $_POST['info'];
    $to_url = 'https://api.telegram.org/bot5401558668:AAGwMxOz01cg3V5jM-FVXWWD0g4AQ-2adF4/sendMessage?chat_id=689907150&text='.$info;
    error_log("tourl = " . $to_url);
    $args = [
        'method' => 'GET',
    ];
    $response = wp_remote_request( $to_url, $args );
    $body = wp_remote_retrieve_body( $response );
    if ( $body ) {
        echo json_encode(array(
            'result' => 'succesful sending',
        ));
    }
    die();
}

/**
 * Добавляем страницу настроек для управляемых сайтов
 * Поля добавляются через ACF PRO
 */
if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title' 	=> 'Managed Sites',
        'menu_title'	=> 'Managed Sites',
        'menu_slug' 	=> 'theme-general-settings',
        'capability'	=> 'manage_options',
        'redirect'		=> false,
        'post_id' 		=> 'managed_sites'
    ));
}


?>