<?php
    class Ranking
    {
        public static function init()
        {
            add_shortcode('ranking_entidades', 'Ranking::entidades');
            add_shortcode('ranking_vendedores_nacional', 'Ranking::vendedoresNacional');
        }
        public static function entidades($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                ),
                $atts,
                'ranking_entidades'
            );
            $ano = get_option('configuracao-rankings')['ano-de-exibicao'];
            $trimestre = get_option('configuracao-rankings')['trimestre-de-exibicao'];
            $qtd = $atts['qtd'];
           // echo $ano . '-' .$trimestre .'-'.$qtd; 
           // $ano = 2023;
           // $trimestre = 1;
           // $qtd = 3;

           $args = array(
            'post_type' => 'ranking-entidades',
            'posts_per_page' => $qtd,
            'orderby' => 'meta_value_num',
            'meta_key' => 'entidade-posicao',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'entidade-ano',
                    'value' => $ano,
                    'compare' => '='
                ),
                array(
                    'key' => 'entidade-trimestre',
                    'value' =>  $trimestre,
                    'compare' => '='
                )
            ),
        );
            
            $result = new WP_Query($args);
            
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_entidade = get_the_title($post_id);
                    $entidade_pontos = get_post_meta($post_id, 'entidade-pontos', true);
                    $entidade_posicao = get_post_meta($post_id, 'entidade-posicao', true);
            
                    switch ($entidade_posicao) {
                        case 1:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-1-icon.png"/>';
                            break;
                        case 2:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-2-icon.png"/>';
                            break;
                        case 3:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-3-icon.png"/>';
                            break;
                        default:
                            $posicaoIcon = $entidade_posicao . 'º';
                            break;
                    }
                    echo '<div class="ranking-line"><div class="ranking-position">'.$posicaoIcon.'</div><div class="ranking-text">'.$nome_entidade.' '.$entidade_pontos.' pontos</div></div>';
                }
                wp_reset_postdata();
            }else{
                echo '<div class="ranking-text">Nada encontrado</div>';
            }
            wp_reset_query();
        }
        public static function vendedoresNacional($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                    'tipo' => 'hunter', // Tipo pode ser hunter farmer
                ),
                $atts,
                'ranking_vendedores_nacional'
            );
            $ano = get_option('configuracao-rankings')['ano-de-exibicao'];
            $trimestre = get_option('configuracao-rankings')['trimestre-de-exibicao'];
            $qtd = $atts['qtd'];
            $tipo = $atts['tipo'];
            
           // echo $ano . '-' .$trimestre .'-'.$qtd; 
           // $ano = 2023;
           // $trimestre = 1;
           // $qtd = 3;

           $args = array(
            'post_type' => 'ranking-vendedores',
            'posts_per_page' => $qtd,
            'orderby' => 'meta_value_num',
            'meta_key' => 'nacional-posicao-'.$tipo,
            'order' => 'ASC',
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
            
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_vendedor = get_the_title($post_id);
                    $pontos_vendas = get_post_meta($post_id, 'pontos-vendas-'.$tipo, true);
                    $pontos_trilha = get_post_meta($post_id, 'pontos-trilha', true);
                    $pontos_total  = get_post_meta($post_id, 'pontos-total-'.$tipo, true);
                    $ranking_posicao = get_post_meta($post_id, 'nacional-posicao-'.$tipo, true);
            
                    switch ($ranking_posicao) {
                        case 1:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-1-icon.png"/>';
                            break;
                        case 2:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-2-icon.png"/>';
                            break;
                        case 3:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-3-icon.png"/>';
                            break;
                        default:
                            $posicaoIcon = $ranking_posicao . 'º';
                            break;
                    }
                    echo '<div class="ranking-line"><div class="ranking-position">'.$posicaoIcon.'</div><div class="ranking-text">'.$nome_vendedor.' '.$pontos_total.' pontos</div></div>';
                }
                wp_reset_postdata();
            }else{
                echo '<div class="ranking-text">Nada encontrado</div>';
            }
            wp_reset_query();
        }
        public static function vendedoresRegional($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                    'tipo' => 'hunter', // Tipo pode ser hunter farmer
                ),
                $atts,
                'ranking_vendedores_regional'
            );
            $ano = get_option('configuracao-rankings')['ano-de-exibicao'];
            $trimestre = get_option('configuracao-rankings')['trimestre-de-exibicao'];
            $qtd = $atts['qtd'];
            $tipo = $atts['tipo'];
            
            //pega o código da entidade do usuário logado
            $current_user_id = get_current_user_id();
            $user_code_entity = get_user_meta($current_user_id, 'user-code-entity', true);
            $user_cpf = get_user_meta($current_user_id, 'user-cpf', true);
            
            //pega a região do vendedor
            $args = array(
                'post_type' => 'vendedores',
                'posts_per_page' => 1, 
                'meta_query' => array(
                    array(
                        'key' => 'cpf-vendedor',
                        'value' => $user_cpf,
                        'compare' => '='
                    )
                ),
            );
            
            $query = new WP_Query($args);
            $regiao_vendedor = "---";
            if ($query->have_posts()) {
                $post_id = get_the_ID();
                $regiao_vendedor  = get_post_meta($post_id, 'regiao-vendedor', true);
            }

            //pega todos os vendedores da mesma regiao
            $args = array(
                'post_type' => 'vendedores',
                'posts_per_page' => -1, 
                'meta_query' => array(
                    array(
                        'key' => 'regiao-vendedor',
                        'value' => $regiao_vendedor,
                        'compare' => '='
                    )
                ),
            );
            
            $query = new WP_Query($args);
            $regiao_vendedor = "---";
            if ($query->have_posts()) {
                $post_id = get_the_ID();
                $regiao_vendedor  = get_post_meta($post_id, 'regiao-vendedor', true);
            }

           

           $args = array(
            'post_type' => 'ranking-vendedores',
            'posts_per_page' => $qtd,
            'orderby' => 'meta_value_num',
            'meta_key' => 'regional-posicao-'.$tipo,
            'order' => 'ASC',
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
            
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_vendedor = get_the_title($post_id);
                    $pontos_vendas = get_post_meta($post_id, 'pontos-vendas-'.$tipo, true);
                    $pontos_trilha = get_post_meta($post_id, 'pontos-trilha', true);
                    $pontos_total  = get_post_meta($post_id, 'pontos-total-'.$tipo, true);
                    $ranking_posicao = get_post_meta($post_id, 'regional-posicao-'.$tipo, true);
            
                    switch ($ranking_posicao) {
                        case 1:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-1-icon.png"/>';
                            break;
                        case 2:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-2-icon.png"/>';
                            break;
                        case 3:
                            $posicaoIcon = '<img src="' . get_site_url() . '/wp-content/uploads/2023/03/Ativo-3-icon.png"/>';
                            break;
                        default:
                            $posicaoIcon = $ranking_posicao . 'º';
                            break;
                    }
                    echo '<div class="ranking-line"><div class="ranking-position">'.$posicaoIcon.'</div><div class="ranking-text">'.$nome_vendedor.' '.$pontos_total.' pontos</div></div>';
                }
                wp_reset_postdata();
            }else{
                echo '<div class="ranking-text">Nada encontrado</div>';
            }
            wp_reset_query();
        }
    }
    ?>
