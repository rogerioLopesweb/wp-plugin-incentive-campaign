<?php
    class EntitySeller
    {
        public static function init()
        {
            add_shortcode('entidade_vendedor', 'EntitySeller::vendedorEntidadeDados');
        }
        public static function vendedorEntidadeDados($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'campo' => "code"
                ),
                $atts,
                'entidade_vendedor'
            );
            $campo = $atts['campo'];

            //pega o código da entidade do usuário logado
            $current_user_id = get_current_user_id();
            $user_code_entity = get_user_meta($current_user_id, 'user-code-entity', true);
            $user_cpf = get_user_meta($current_user_id, 'user-cpf', true);
            
            $args = array(
                'post_type' => 'entidades',
                'posts_per_page' => 1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'entity-code',
                        'value' =>  $user_code_entity,
                        'compare' => '='
                    )
                ),
            );

            $result = new WP_Query($args);
         
            $retorno = "--";
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    if($campo == "title"){
                        $retorno = get_the_title($post_id);
                    }else{
                        $retorno  = get_post_meta($post_id, $campo, true);
                    }
                }
                wp_reset_postdata();
            }
           return $retorno;
        }
    }
?>