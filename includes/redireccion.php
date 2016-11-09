<?php
/*
 *
 * Funciones de redireccion luego de hacer login
 *
 */

    function registro(){

        $redirect = apply_filters( 'redirect', get_permalink( 4 ), 4 );
        echo $redirect;
        wp_redirect( $redirect );
        exit();
    }

    function checkEmail(){
        $email = $_SESSION['opauth']['auth']['info']['email'];
        $userdata= get_user_by( 'email', $email );
        if ( $userdata ){
            //echo "That E-mail is registered to user number " . $userdata->ID;
            loginAs($userdata->ID);
            wp_redirect(get_permalink( get_page_by_path( 'user-dashboard' )));
            exit();
        }
        else{
            //echo "That E-mail doesn't belong to any registered users on this site";
            /*$uid = createUser();
            
            if ($uid) {
               loginAs($uid);
               redirectionToPage("Perfil");
            }
            else
                redirectionToPage("login");*/
            wp_redirect(get_permalink( get_page_by_path( 'register' )));
            exit();
        }
    }

    function loginAs($uid)
    {
        $user = wp_set_current_user($uid);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);
    }

    function redirectionToPage($page_name){
        if($page_name == "login"){
            wp_redirect(wp_login_url());
        }
        elseif($page_name == "register"){
            wp_redirect( wp_registration_url());
        }
        else{
            global $wpdb;

            $id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$page_name."'");
            $redirect = apply_filters( '_redirect', get_permalink( $id ), $id );
            wp_redirect( $redirect );
        }
        exit();
    }

   /* function isInitialized()
    {
        global $wpdb;

        $table = $wpdb->prefix.'usuarios';
        $query = $wpdb->prepare("SHOW TABLES LIKE %s;", $table);

        return ($wpdb->get_var($query) === null? false : true);
    }*/

    function createTables(){
        global $wpdb;

        $table = $wpdb->prefix.'usuarios';
        $query = "CREATE TABLE IF NOT EXISTS $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                id_user bigint(20) NOT NULL,
                nombre varchar(45) NOT NULL,
                apellido varchar(45) NOT NULL,
                telefono varchar(45) NOT NULL,
                sexo varchar(10) NOT NULL,
                fecha_nacimiento date NULL,
                doc_identificacion varchar(128) NOT NULL,
                provider varchar(128) NOT NULL,
                remote_id varchar(128) NOT NULL,
                PRIMARY KEY (`id`)
            );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($query);
    }

    function createUser()
    {
        global $wpdb;

        if (!get_option('users_can_register'))
        {
            return new WP_Error(1, __('User registration is disabled', 'opauth'));
        }

        $table = $wpdb->prefix.'usuarios';

        $explode_email = explode("@",$_SESSION['opauth']['auth']['info']['email']);
        $prefix = sanitize_user($explode_email[0]);
        $suffix = '';
        $username = '';

        do
        {
            $username = $prefix . $suffix++;
        } while (username_exists($username));
                
        $user = array();
        $user['user_login'] = $username;
        $user['display_name'] = $_SESSION['opauth']['auth']['info']['name'];
        $user['user_email'] = $_SESSION['opauth']['auth']['info']['email'];
                
        // Garbage
        $user['user_pass'] = generateRandomSalt(12, 16);
                
        $uid = wp_insert_user($user);

        if (is_wp_error($uid))
        {
            return $uid;
        }

                // Just as in themes/kallyas/functions.php:3481
                // TODO update for Winecountry
                //wp_new_user_notification( $uid, $user['user_pass'], 'Facebook');

        $wpdb->replace($table,
                array(
                    'id_user' => $uid,
                    'nombre' => $_SESSION['opauth']['auth']['info']['first_name'],
                    'apellido' => $_SESSION['opauth']['auth']['info']['last_name'],
                    'sexo' => $_SESSION['opauth']['auth']['raw']['gender'],
                    'provider' => $_SESSION['opauth']['auth']['provider'],
                    'remote_id' => $_SESSION['opauth']['auth']['uid']
                )
        );
                
                $thumbnail_url = empty($_SESSION['opauth']['auth']['info']['image']) ? '' : $_SESSION['opauth']['auth']['info']['image'];
                update_user_meta($uid, 'thumbnail_url', $thumbnail_url);

        return $uid;
    }

    function generateRandomSalt($min, $max)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz';
        $alphabet .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= '0123456789';
        $length = mt_rand($min, $max);
        $salt = '';

        while ($length--) {
            $salt .= $alphabet[mt_rand(0, strlen($alphabet) - 1)];
        }

        return $salt;
    }

