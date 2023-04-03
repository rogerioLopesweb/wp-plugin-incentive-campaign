<?php
    class General
    {
        public static function init()
        {
            add_shortcode('maracadores_geral', 'General::marcadoresGeral');
        }
        public static function marcadoresGeral($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'campo' => "ano"
                ),
                $atts,
                'maracadores_geral'
            );
            $ano = get_option('configuracao-rankings')['ano-de-exibicao'];
            $trimestre = get_option('configuracao-rankings')['trimestre-de-exibicao'];
            $campo = $atts['campo'];

           
            
            $args = array(
                'post_type' => 'marcadores-geral',
                'posts_per_page' => 1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ano',
                        'value' => $ano,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'trimestre',
                        'value' =>  $trimestre,
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
            $retorno = str_replace('%', '', $retorno);
           echo $retorno;
        }

    }
    ?>