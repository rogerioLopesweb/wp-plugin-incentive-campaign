<?php
    class Login
    {
        public static function init(){
            add_shortcode( 'form_login_custom', 'Login::formLogin' );
            add_shortcode( 'force_redirect_login', 'Login::forceRedirectLogin' );
			add_shortcode( 'force_redirect_dashboard', 'Login::forceRedirectDashboard' );
			add_shortcode( 'force_redirect_logoult', 'Login::forceRedirectLogoult' );
            add_shortcode( 'login_external_token', 'Login::loginExternalByToken' );
            add_shortcode( 'login_redireciona_locomotiva', 'Login::UrlLocomotiva' );
            add_action("wp", "Login::forceRedirectAcceptedCampaignTerms");
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
                echo 'VOCÊ ESTÁ LOGADO, REDIRECIONANDO...';
                if(get_the_ID() != false){
                    $post_id = get_the_ID();
                    $page_slug = get_post_field( 'post_name', $post_id );
                    if($page_slug == 'login'){
                        Login::forceRedirectDashboard();
                        exit;
                    }
                }
            } else {
                // 'Vc não esta locado';
                echo '<form id="login-form" action="'.get_site_url(). "/login" .'" class="form-login" method="POST">
	            <div>
	              <label for="login-form-username">CPF:</label>
	              <input type="text" name="login-form-username" id="login-form-username" class="form-btn-username" />
	            </div>
	            <div>
	              <label for="login-form-password">SENHA:</label>
					<div class="password-container">
					  <input type="password" name="login-form-password" id="login-form-password" class="form-btn-password">
					  <span class="password-toggle-icon" onclick="togglePasswordVisibility()"><i class="fa fa-eye"></i></span>
					</div>
				</div>
	            <div style="display:flex; align-items:center; justify-content:space-between;">
					<a href="https://spc.funifier.com/#!/esqueceu" target="_blank" class="form-btn-lost-password" role="button">ESQUECI MINHA SENHA</a>
					<button type="submit" class="form-btn-submit" id="login-form-submit">ENTRAR</button>
	            </div>
	          </form>';
            }
        }
        public static function auth(){
            if(isset($_POST['login-form-username'])){
                $login = sanitize_user($_POST['login-form-username']);
                $password = esc_attr($_POST['login-form-password']);
                if($login != "" && $password != ""){
                    $login =  str_replace(array('-', '.','/'), '', $login);
                    $dataUserExternal =  Login::loginExternal($login, $password);
                    if($dataUserExternal["status"] == "S"){
                        Login::loginWP($login, $password, $dataUserExternal);
                    }else{
                        echo $dataUserExternal["msg"];
                    }
                }
            }
        }
        public static function loginExternalGetToken($login, $password){
             //homologacao spc_vendas_dev producao = spc_vendas
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "http://service2.funifier.com/v3/auth/token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "apiKey=spc_vendas&grant_type=password&username=".$login."&password=".$password,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded"
                ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "F";
            } else {
                $data = json_decode($response,true);
                if (isset($data) && array_key_exists("statusCode", $data)) {
                    if($data["statusCode"] == 500){
                        return "F";
                    }
                }
                if (isset($data) && array_key_exists("access_token", $data)) {
                    return $data["access_token"];
                }else{
                    return $data["access_token"];
                }
               
            }
        }
        public static function loginExternalGetDataUserByToken($token){
            $curl = curl_init();
            curl_setopt_array($curl, [
              CURLOPT_URL => "https://service2.funifier.com/v3/player/me",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_POSTFIELDS => "",
              CURLOPT_HTTPHEADER => [
                "Authorization: Bearer Bearer " . $token,
                "Bearer: " . $token
              ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
              return "F";
            } else {
              return $response;
            }
        }
        public static function loginExternalGetDataUserFullByTokenID($token, $idUser){
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://service2.funifier.com/v3/database/player/aggregate",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => '[{"$match":{ "_id":"'.$idUser.'" }}, {"$project":{ "_id":1, "image":"$image.original.url", "name":1, "email":1, "extra":1 }}, {"$lookup": {"from":"spc_entity__c", "localField":"extra.entity", "foreignField":"_id", "as":"entity" }}, {"$unwind" : "$entity" }]',
                CURLOPT_HTTPHEADER => [
                "Authorization: Bearer Bearer " . $token,
                "Bearer: " . $token,
                "Content-Type: application/json"
              ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
              return "F";
            } else {
              return $response;
            }
        }
        public static function loginExternal($login, $password){
            if($login == "teste" && $password == "123456"){
                return array("status"=>"S", "msg" =>"<div class='sucesso-login'>LOGIN EXTERNO EFETUADO COM SUCESSO</div>",  "name"=>"Roger", "email"=>"roger@teste.com.br");
            }
            $token = Login::loginExternalGetToken($login, $password);
            if($token == "F"){
                return array("status"=>"F", "msg" =>"<div class='erro-login'>CPF E/OU SENHA INCORRETO(S)</div>");
            }
            $dataUserStr = Login::loginExternalGetDataUserByToken($token);
            if($dataUserStr == "F"){
                return array("status"=>"F", "msg" =>"<div class='erro-login'>FALHA AO CONSULTAR OS DADOS DE LOGIN</div>");
            }
            $dataUser = json_decode($dataUserStr,true);
            return array("status"=>"S",
                "msg" =>"<div class='sucesso-login'>LOGIN EXTERNO EFETUADO COM SUCESSO</div>",
                "idUser"=>$dataUser["_id"],
                "name"=>$dataUser["name"],
                "email"=>$dataUser["email"],
                "login" => $login,
                "password" => $password,
                "telefone" => $dataUser["extra"]["telefone"],
                "codigo_entidade" => $dataUser["extra"]["entity"],
                "operador" =>$dataUser["extra"]["operador"],
                "token" => $token
            );
        }
        public static function loginWP($login, $password, $dataUserExternal){
            $credentials = array();
            $credentials['user_login'] = $login;
            $credentials['user_password'] = $password;
            $user = wp_signon($credentials, "");
            if ( is_wp_error($user) ) {
                $credentials['user_password'] = $login ."@Tmp";
                $user = wp_signon($credentials, "");
            }
            if ( is_wp_error($user) ) {
                Login::registerUserWP($login, $password, $dataUserExternal);
            } else {
                wp_clear_auth_cookie();
                do_action('wp_login', $user->ID);
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID, true);
                $redirect_to = $_SERVER['REQUEST_URI'];
                $idUser = $user->ID;
                Login::saveDataUser($idUser,  $dataUserExternal);
                wp_safe_redirect($redirect_to);
                exit;
            }
        }
        public static function registerUserWP($login, $password, $dataUserExternal){
           $arrayName =  Login::split_name($dataUserExternal['name']);
            $WP_array = array (
                'user_login'    =>  $login,
                'user_email'    =>  $dataUserExternal['email'],
                'user_pass'     =>  $password,
                'user_url'      =>  '',
                'display_name'  =>  $arrayName['first_name'],
                'first_name'    =>  $arrayName['first_name'],
                'last_name'     =>  $arrayName['last_name'] ,
                'nickname'      =>  $dataUserExternal['first_name'],
                'description'   =>  '',
            ) ;
            $idUser = wp_insert_user( $WP_array ) ;
            Login::saveDataUser($idUser,  $dataUserExternal);
            Login::loginWP($login, $password, $dataUserExternal);
        }
        public  static function loginExternalByToken(){
            if(is_user_logged_in() && current_user_can('administrator')) {
                return "[login_external_token]";
             }
            Login::forceRedirectDashboard();
            if(!empty($_GET['authtoken'])){
                $authtoken =  trim($_GET['authtoken']);
                $authtoken = base64_decode($authtoken);
                $token =  trim(str_replace(array('Bearer', 'bearer',' '), '', $authtoken));
              //  echo "<h3>Token decodificado</h3>";
               // echo  $token;
                $dataUserExternal = json_decode(Login::loginExternalGetDataUserByToken($token),true);
                $dataUserExternal["token"] = $token; 
                $idUser =  $dataUserExternal["_id"];
                //$dataUser = Login::loginExternalGetDataUserFullByTokenID($token, $idUser);
                if($dataUser == "F") {
                    echo "<div class='erro-login'>OCORREU UM ERRO, FAVOR TENTAR NOVAMENTE</div>";
                    exit;
                }
                //var_dump($dataUser);
                $user = get_user_by( 'login', $idUser );
                if ( ! empty( $user ) ) {
                    $password = $user->user_pass;
                    Login::loginWP($idUser, $password , $dataUserExternal);
                }else{
                    $password = $idUser."@Tmp";
                    Login::loginWP($idUser, $password , $dataUserExternal);
                }
                /* if(array_key_exists('name', $dataUser)){
                    echo "<br>". $dataUser["name"];
                }
                if(array_key_exists('email', $dataUser)){
                    echo "<br>". $dataUser["email"];
                }
                if(array_key_exists('cpf', $dataUser)){
                    echo "<br>". $dataUser["cpf"];
                }
                if(array_key_exists('entity', $dataUser)){
                    echo "<br>". $dataUser["entity"];
                }
                if(array_key_exists('seller', $dataUser)){
                    echo "<br>". $dataUser["seller"];
                }
                if(array_key_exists('region', $dataUser)){
                    echo "<br>". $dataUser["region"];
                }
                if(array_key_exists('state', $dataUser)){
                    echo "<br>". $dataUser["state"];
                }*/
            }
        }
        public  static function forceRedirectLogin(){

            if(is_user_logged_in() && current_user_can('administrator')) {
                return "[form_login_custom]";
             }
        	if(!is_user_logged_in()) {
                echo "<div class='sucesso-login'>VOCÊ JÁ ESTÁ LOGADO, REDIRECIONANDO...</div>";
            	wp_redirect( get_site_url(). "/login");
            	exit();
           }
        }
        public  static function forceRedirectDashboard(){
        	if( is_user_logged_in() ) {
                echo "<div class='sucesso-login'>VOCÊ JÁ ESTÁ LOGADO, REDIRECIONANDO...</div>";
                wp_redirect( get_site_url(). "/indicadores");
                exit();
           }
        }
        public  static function forceRedirectLogoult(){
        	if( is_user_logged_in()) {
                echo "<div class='sucesso-login'>VOCÊ JÁ ESTÁ LOGADO, REDIRECIONANDO...</div>";
                wp_logout();
                wp_redirect( get_site_url() );
                exit();
           }else{
                wp_redirect( get_site_url() );
                exit();
           }
        }
        public  static function forceRedirectAcceptedCampaignTerms(){
        	if( is_user_logged_in() && !current_user_can('administrator') ) {
                if(get_the_ID() != false){
                    $post_id = get_the_ID();
                    $page_slug = get_post_field( 'post_name', $post_id );
                    if($page_slug != 'termos-da-campanha' && $page_slug != 'logout'){
                        $campaign_terms = get_user_meta( get_current_user_id() , "user-accepted-of-campaign-terms", true );
                        if($campaign_terms != "S"){
                            echo "<div class='sucesso-login'>VOCÊ JÁ ESTÁ LOGADO, REDIRECIONANDO...</div>";
                            wp_redirect( get_site_url(). "/termos-da-campanha");
                            exit();
                        }
                    }
                }
           }
        }
        public static function saveDataUser($idUser,  $dataUserExternal){
            /*var_dump($dataUserExternal);
            exit;*/
          /*if(is_array($dataUserExternal)){
              //$dataUserExternal["extra","entity"];
           // Verifica se o array e a coluna existem antes de obter o valor
           /*if (isset($dataUserExternal) && array_key_exists("_id", $dataUserExternal["name"])) {
                $nome = $dataUserExternal["name"];
            } else {
                $nome = '';
            }
            if (isset($dataUserExternal) && array_key_exists("_id", $dataUserExternal["_id"])) {
                $cpf = $dataUserExternal["_id"];
            } else {
                $cpf = '';
            }
            if (isset($dataUserExternal["extra"]) && array_key_exists("telefone", $dataUserExternal["extra"])) {
                $telefone = $dataUserExternal["extra"]["telefone"];
            } else {
                $telefone = '';
            }
            if (isset($dataUserExternal["extra"]) && array_key_exists("operador", $dataUserExternal["extra"])) {
                $operador = $dataUserExternal["extra"]["operador"];
            } else {
                $operador = '';
            }

            if (isset($dataUserExternal["entity"]) && array_key_exists("_id", $dataUserExternal["entity"])) {
                $codigo_entidade = $dataUserExternal["entity"]["_id"];
            } else {
                $codigo_entidade = '';
            }

            if (isset($dataUserExternal["entity"]) && array_key_exists("name", $dataUserExternal["entity"])) {
                $entidade = $dataUserExternal["entity"]["name"];
            } else {
                $entidade = '';
            }

            if (isset($dataUserExternal["entity"]) && array_key_exists("uf", $dataUserExternal["entity"])) {
                $uf_entidade = $dataUserExternal["entity"]["uf"];
            } else {
                $uf_entidade = '';
            }

            if (isset($dataUserExternal["entity"]) && array_key_exists("region", $dataUserExternal["entity"])) {
                $regiao_entidade = $dataUserExternal["entity"]["region"];
            } else {
                $regiao_entidade = '';
            }

            $curva_entidade = "";

            

            // Salva os valores como meta do usuário
            update_user_meta( $idUser, 'user-cpf', $cpf );
            update_user_meta( $idUser, 'user-seller', $nome );
            update_user_meta( $idUser, 'user-telephone', $telefone );
            update_user_meta( $idUser, 'user-code-operating', $operador );
            update_user_meta( $idUser, 'user-code-entity', $codigo_entidade );
            /*update_user_meta( $idUser, 'entidade', $entidade );
            update_user_meta( $idUser, 'uf_entidade', $uf_entidade );
            update_user_meta( $idUser, 'regiao_entidade', $regiao_entidade );*/
            /*

            $args = array(
                'post_type' => 'entidades',  
                'posts_per_page' => 1,
                'meta_query' => array(
                'relation' => 'AND',
                    [
                        'meta_key' => 'entity-code',
                        'meta_value' => $codigo_entidade,
                    ]
                ),
            );
            $entidade_query = new WP_Query($args);
            // Se o $codigo_entidade existir, atualize-o, caso contrário, insira um novo
            if ($entidade_query->have_posts()) {
            /*  // Atualizar o post existente
                $entidade_post = $entidade_query->posts[0];
                $entidade_post_id = $entidade_post->ID;
                // Atualizar o título do post
                $entidade_post->post_title = $entidade;
                wp_update_post($entidade_post);
                update_post_meta($entidade_post_id, 'entity-code', $codigo_entidade);
                update_post_meta($entidade_post_id, 'entity-curve', $curva_entidade);
                update_post_meta($entidade_post_id, 'entity-uf', $uf_entidade);
                update_post_meta($entidade_post_id, 'entity-region', $regiao_entidade);*/
                /*
            } else {
                // Inserir no post_type 'entidades'
                $entidade_post = array(
                    'post_title'    => $entidade,
                    'post_type'     => 'entidades',
                    'post_status'   => 'publish',
                );
                // Insere o post e retorna o ID
                $entidade_post_id = wp_insert_post($entidade_post);
                // Adiciona os metadados ao post_type 'entidades'
                update_post_meta($entidade_post_id, 'entity-code', $codigo_entidade);
                update_post_meta($entidade_post_id, 'entity-curve', $curva_entidade);
                update_post_meta($entidade_post_id, 'entity-uf', $uf_entidade);
                update_post_meta($entidade_post_id, 'entity-region', $regiao_entidade);
            }
          }*/

           if (isset($dataUserExternal) && array_key_exists("name", $dataUserExternal)) {
                $nome = $dataUserExternal["name"];
            } else {
                $nome = '';
            }
            if (isset($dataUserExternal) && array_key_exists("idUser", $dataUserExternal)) {
                $cpf = $dataUserExternal["idUser"];
            } else {
                $cpf = '';
            }
            if (isset($dataUserExternal) && array_key_exists("telefone", $dataUserExternal)) {
                $telefone = $dataUserExternal["telefone"];
            } else {
                $telefone = '';
            }

            if (isset($dataUserExternal) && array_key_exists("codigo_entidade", $dataUserExternal)) {
                $codigo_entidade = $dataUserExternal["codigo_entidade"];
            } else {
                $codigo_entidade = '';
            }
            if (isset($dataUserExternal) && array_key_exists("operador", $dataUserExternal)) {
                $operador = $dataUserExternal["operador"];
            } else {
                $operador = '';
            }
     
            if (isset($dataUserExternal) && array_key_exists("token", $dataUserExternal)) {
                $token = $dataUserExternal["token"];
            } else {
                $token = '';
            }

            update_user_meta( $idUser, 'user-cpf', $cpf );
            update_user_meta( $idUser, 'user-seller', $nome );
            update_user_meta( $idUser, 'user-telephone', $telefone );
            update_user_meta( $idUser, 'user-code-operating', $operador );
            update_user_meta( $idUser, 'user-code-entity', $codigo_entidade );
            update_user_meta( $idUser, 'token', $token );
           
        }
        public  static function UrlLocomotiva(){

            if(is_user_logged_in() && current_user_can('administrator')) {
                return "[login_redireciona_locomotiva]";
             }
        	if(is_user_logged_in()) {
                $user_id = get_current_user_id();
                $token = get_user_meta($user_id, 'token', true);
                $tokenEncode = base64_encode("Bearer " . $token);
                if ($token !== null && trim($token) !== '') {
                   // echo "<br> https://homol.spc.funifier.com/?token=". $tokenEncode;
                    wp_redirect( "https://spc.funifier.com?token=". $tokenEncode);
                    exit();
                  }else{
                    wp_redirect( get_site_url());
                    exit();
               }
                
           }else{
                wp_redirect( get_site_url(). "/login");
                exit();
           }
        }
        public static function split_name($namaFull){
            $arr = explode(' ', $namaFull);
            $num = count($arr);
            $first_name = $middle_name = $last_name = null;
            if ($num == 2) {
                list($first_name, $last_name) = $arr;
            } else {
                list($first_name, $middle_name, $last_name) = $arr;
            }
            return (empty($first_name) || $num > 3) ? "" : compact(
                'first_name', 'middle_name', 'last_name'
            );
        }
    }
?>