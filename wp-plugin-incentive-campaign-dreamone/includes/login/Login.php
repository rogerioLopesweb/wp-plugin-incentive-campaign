<?php
    class Login 
    {
        public static function init(){
            add_shortcode( 'form_login_custom', 'Login::formLogin' );
            add_shortcode( 'force_redirect_login', 'Login::forceRedirectLogin' );
			add_shortcode( 'force_redirect_dashboard', 'Login::forceRedirectDashboard' );
			add_shortcode( 'force_redirect_logoult', 'Login::forceRedirectLogoult' );
        }
        public static function formLogin(){
            Login::formTemplate();
            Login::auth();
        }
        public static function formTemplate() {
            if(is_user_logged_in() && current_user_can('administrator')) { 
              return "[form_login_custom]";
           }
            if ( is_user_logged_in() ) {
                echo 'Você está logado, redirecionando...';
                $page_slug = get_post_field( 'post_name', $post_id );
                if($page_slug == 'login'){
                  wp_redirect( get_site_url(). "/dashboard");
                  exit;
                }
            } else {
                // 'Vc não esta locado';
                echo '<form id="login-form" action="'.get_site_url(). "/login" .'" class="form-login" method="POST">
	            <h3>Entre com sua conta</h3>
	            <div>
	              <label for="login-form-username">Usuario:</label>
	              <input type="text" name="login-form-username" id="login-form-username" 
	                                        class="form-control" />
	            </div>
	            <div>
	              <label for="login-form-password">Senha:</label>
	              <input type="password" name="login-form-password" id="login-form-password"
	                                        class="form-control" />
	            </div>
	            <div>
	              <button type="submit" class="button button-3d"
	                                        id="login-form-submit">Entrar</button>
	            </div>
	          </form>';
            }
        }
        public static function auth(){
            if(isset($_POST['login-form-username'])){
                $login = sanitize_user($_POST['login-form-username']);
                $password = esc_attr($_POST['login-form-password']);
                if($login != "" && $password != ""){
                    $dataUserExternal =  Login::loginExternal($login, $password);
                    if($dataUserExternal["status"] == "S"){
                        Login::loginWP($login, $password, $dataUserExternal);
                    }else{
                        echo $dataUserExternal["msg"];
                    }
                }
            }
        }
        public static function loginExternal($login, $password){
            if($login == "teste" && $password == "123456"){
                return array("status"=>"S", "msg" =>"Login externo efetuado com sucesso!",  "name"=>"Roger", "email"=>"roger@teste.com.br");
            }else{
                return array("status"=>"F", "msg" =>"Login incorreto!");
            }
        }
        public static function loginWP($login, $password, $dataUserExternal){
            $credentials = array();
            $credentials['user_login'] = $login;
            $credentials['user_password'] = $password;
            $user = wp_signon($credentials, "");

            if ( is_wp_error($user) ) {
                Login::registerUserWP($login, $password, $dataUserExternal);
            } else {    
                wp_clear_auth_cookie();
                do_action('wp_login', $user->ID);
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true);
                $redirect_to = $_SERVER['REQUEST_URI'];
                wp_safe_redirect($redirect_to);
                exit;
            }
        }
        public static function registerUserWP($login, $password, $dataUserExternal){
            $WP_array = array (
                'user_login'    =>  $login,
                'user_email'    =>  $dataUserExternal['email'],
                'user_pass'     =>  $password,
                'user_url'      =>  '',
                'first_name'    =>  $dataUserExternal['name'],
                'last_name'     =>  '',
                'nickname'      =>  $dataUserExternal['name'],
                'description'   =>  '',
            ) ;
            $id = wp_insert_user( $WP_array ) ;
            Login::loginWP($login, $password, $dataUserExternal);
        }
        public  static function loginExternalByToken($token){
            
        }
        public  static function forceRedirectLogin(){
        	if(!is_user_logged_in()) { 
                echo "Você já esta logado, redirecionando...";
            	wp_redirect( get_site_url(). "/login");
            	exit();
           }
        	
        }
        public  static function forceRedirectDashboard(){
        	if( is_user_logged_in() ) { 
                echo "Você já esta logado, redirecionando...";
                wp_redirect( get_site_url(). "/dashboard");
                exit();
           }
        }
        public  static function forceRedirectLogoult(){
        	if( is_user_logged_in() && !current_user_can('administrator') ) { 
                echo "Você já esta logado, redirecionando...";
                wp_logout();
                wp_redirect( get_site_url() );
                exit();
           }
        }
    }
?>