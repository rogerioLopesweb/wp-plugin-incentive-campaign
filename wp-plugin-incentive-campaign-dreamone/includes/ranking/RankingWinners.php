<?php
    class RankingWinners
    {
        public static function init()
        {
            add_shortcode('rankwinners_entidades', 'RankingWinners::ganhadoresEntidades');
            add_shortcode('rankwinners_nacional', 'RankingWinners::ganhadoresNacional');
            add_shortcode('rankwinners_regional', 'RankingWinners::ganhadoresRegional');
           
        }
        
        public static function ganhadoresEntidades($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                ),
                $atts,
                'rankingwinners_entidades'
            );
            $ano = get_option('configuracao-rankings')['ganhadores-ano'];
            $trimestre = get_option('configuracao-rankings')['ganhadores-trimestre'];
            $qtd = $atts['qtd'];
           // echo $ano . '-' .$trimestre .'-'.$qtd; 
           // $ano = 2023;
           // $trimestre = 1;
           // $qtd = 3;

            echo '<div class="table-responsive">';
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th data-th="POSIÇÃO">POSIÇÃO</th>';
            echo '<th data-th="ENTIDADE">ENTIDADE</th>';
            //echo '<th data-th="PONTOS">PONTOS</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $args = array(
                'post_type' => 'ranking-entidades',
                'post_status' => 'publish',
                'posts_per_page' => $qtd,
                'orderby' => 'meta_value_num',
                'meta_key' => 'entidade-posicao',
                'order' => 'ASC',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ganhador-nacional-entidade',
                        'value' => 'S',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'entidade-ano',
                        'value' => $ano,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'entidade-trimestre',
                        'value' => $trimestre,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'entidade-posicao',
                        'value' => 0,
                        'compare' => '!='
                    )
                ),
            );
            
            $result = new WP_Query($args);
            
            $counter =0;
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_entidade = get_the_title($post_id);
                    $entidade_pontos = get_post_meta($post_id, 'entidade-pontos', true);
                    $entidade_posicao = get_post_meta($post_id, 'entidade-posicao', true);
                    
                    //if( $entidade_posicao != "0"){
                        $counter++;
                        // Adicionar a classe CSS de acordo com a posição da linha
                        $class = '';
                        switch ($counter) {
                            case 1:
                                $class = ' class="golden"';
                                break;
                            case 2:
                                $class = ' class="silver"';
                                break;
                            case 3:
                                $class = ' class="bronze"';
                                break;
                            default:
                                $class = ' class=""';
                                break;
                        }

                        // Código HTML da linha da tabela com a classe CSS
                        echo '<tr' . $class . '>';
                        echo '<td data-th="POSIÇÃO">' . $entidade_posicao . 'º</td>';
                        echo '<td data-th="ENTIDADE">' . $nome_entidade . '</td>';
                        //echo '<td data-th="PONTOS">' . $entidade_pontos . '</td>';
                        echo '</tr>';
                    //}
                    
                }
                 wp_reset_postdata();
            }else{
                echo '<tr>';
                echo '<td colspan="2">Nada encontrado</td>';
                echo '</tr>';
            }
         // Fechando a tabela e a div da tabela responsiva
         echo '</tbody>';
         echo '</table>';
         echo '</div>';
        }
        public static function ganhadoresNacional($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                    'tipo' => 'hunter' // Tipo pode ser hunter farmer
                ),
                $atts,
                'rankiwinners_nacional'
            );
            $ano = get_option('configuracao-rankings')['ganhadores-ano'];
            $trimestre = get_option('configuracao-rankings')['ganhadores-trimestre'];
            $qtd = $atts['qtd'];
            $tipo = $atts['tipo'];

            echo '<div class="table-responsive">';
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th data-th="POSIÇÃO">POSIÇÃO</th>';
            echo '<th data-th="ENTIDADE">ENTIDADE</th>';
            echo '<th data-th="NOME">NOME</th>';
            //echo '<th data-th="PONTOS">PONTOS</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $args = array(
                'post_type' => 'ranking-vendedores',
                'posts_per_page' => $qtd,
                'orderby' => 'meta_value_num',
                'meta_key' => 'nacional-posicao-'.$tipo,
                'order' => 'ASC',
                'post_status' => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'ganhador-nacional-'.$tipo,
                        'value' => 'S',
                        'compare' => '='
                    ),
                    array(
                        'key' => 'ano',
                        'value' => $ano,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'trimestre',
                        'value' =>  $trimestre,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'nacional-posicao-'.$tipo,
                        'value' =>  "0",
                        'compare' => '!='
                    )
                ),
            );
            $result = new WP_Query($args);
            $counter = 0;
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                $result->the_post();
                $post_id = get_the_ID();
                $nome_vendedor = get_the_title($post_id);
                $cpf_vendedor = get_post_meta($post_id, 'cpf-vendedor', true);
                $nome_entidade = RankingWinners::getEntidade($cpf_vendedor);
                $pontos_vendas = get_post_meta($post_id, 'pontos-vendas-'.$tipo, true);
                $pontos_trilha = get_post_meta($post_id, 'pontos-trilha', true);
                $pontos_total  = get_post_meta($post_id, 'pontos-total-'.$tipo, true);
                $ranking_posicao = get_post_meta($post_id, 'nacional-posicao-'.$tipo, true);

                $counter++;
                // Adicionar a classe CSS de acordo com a posição da linha
                $class = '';
                switch ($counter) {
                    case 1:
                        $class = ' class="golden"';
                        break;
                    case 2:
                        $class = ' class="silver"';
                        break;
                    case 3:
                        $class = ' class="bronze"';
                        break;
                    default:
                        $class = ' class=""';
                        break;
                }

                // Código HTML da linha da tabela com a classe CSS
                echo '<tr' . $class . '>';
                echo '<td data-th="POSIÇÃO">' . $ranking_posicao . 'º</td>';
                echo '<td data-th="ENTIDADE">' . $nome_entidade . '</td>';
                echo '<td data-th="NOME">' . $nome_vendedor . '</td>';
                //echo '<td data-th="PONTOS">' . $pontos_total . '</td>';
                echo '</tr>';
            }
            wp_reset_postdata();
        } else {
            echo '<tr>';
            echo '<td colspan="3">Nada encontrado</td>';
            echo '</tr>';
        }

        // Fechando a tabela e a div da tabela responsiva
        echo '</tbody>';
        echo '</table>';
        echo '</div>';

                
        }
		
        public static function ganhadoresRegional($atts){
            // Atributos padrão
            $atts = shortcode_atts(
                array(
                    'qtd' => 3, // Quantidade padrão de itens a serem exibidos
                    'tipo' => 'hunter' // Tipo pode ser hunter farmer
                ),
                $atts,
                'rankwinners_regional'
            );
            $ano = get_option('configuracao-rankings')['ganhadores-ano'];
            $trimestre = get_option('configuracao-rankings')['ganhadores-trimestre'];
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
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'cpf-vendedor',
                        'value' => $user_cpf,
                        'compare' => '='
                    )
                ),
            );
            
            $result = new WP_Query($args);
            $regiao_vendedor = "---";
            if ($result->have_posts()) {
                $result->the_post();
                $post_id = get_the_ID();
                $regiao_vendedor  = get_post_meta($post_id, 'regiao-vendedor', true);
            }

            

            //pega todos os vendedores da mesma regiao
            $args = array(
                'post_type' => 'vendedores',
                'posts_per_page' => -1, 
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'regiao-vendedor',
                        'value' => $regiao_vendedor,
                        'compare' => '='
                    )
                ),
            );
            
            $result = new WP_Query($args);

            $lista_vendedores = array();
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $cpf_vendedor = get_post_meta($post_id, 'cpf-vendedor', true);
                    $lista_vendedores[] = $cpf_vendedor;
                }
                wp_reset_postdata();
            }
                
           $args = array(
            'post_type' => 'ranking-vendedores',
            'posts_per_page' => $qtd,
            'orderby' => 'meta_value_num',
            'meta_key' => 'regional-posicao-'.$tipo,
            'order' => 'ASC',
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'ganhador-regional-'.$tipo,
                    'value' => 'S',
                    'compare' => '='
                ),
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
                ,
                array(
                    'key' => 'cpf-vendedor',
                    'value' =>  $lista_vendedores,
                    'compare' => 'in'
                ),
                array(
                    'key' => 'regional-posicao-'.$tipo,
                    'value' =>  "0",
                    'compare' => '!='
                )
            )
        );
            
        $result = new WP_Query($args);
        $counter = 0;
           echo '<div class="table-responsive">';
            echo '<table class="table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th data-th="POSIÇÃO">POSIÇÃO</th>';
            echo '<th data-th="ENTIDADE">ENTIDADE</th>';
            echo '<th data-th="NOME">NOME</th>';
            //echo '<th data-th="PONTOS">PONTOS</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            if ($result->have_posts() && count($lista_vendedores) > 0) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_entidade = RankingWinners::getEntidade($cpf_vendedor);
                    $nome_vendedor = get_the_title($post_id);
                    $pontos_vendas = get_post_meta($post_id, 'pontos-vendas-'.$tipo, true);
                    $pontos_trilha = get_post_meta($post_id, 'pontos-trilha', true);
                    $pontos_total  = get_post_meta($post_id, 'pontos-total-'.$tipo, true);
                    $ranking_posicao = get_post_meta($post_id, 'regional-posicao-'.$tipo, true);
            
                    $counter++;
                    // Adicionar a classe CSS de acordo com a posição da linha
                    $class = '';
                    switch ($counter) {
                        case 1:
                            $class = ' class="golden"';
                            break;
                        case 2:
                            $class = ' class="silver"';
                            break;
                        case 3:
                            $class = ' class="bronze"';
                            break;
                        default:
                            $class = ' class=""';
                            break;
                    }
                    // Código HTML da linha da tabela com a classe CSS
                    echo '<tr' . $class . '>';
                    echo '<td data-th="POSIÇÃO">' . $ranking_posicao . 'º</td>';
                    echo '<td data-th="ENTIDADE">' . $nome_entidade . '</td>';
                    echo '<td data-th="NOME">' . $nome_vendedor . '</td>';
                    //echo '<td data-th="PONTOS">' . $pontos_total . '</td>';
                    echo '</tr>';
                }
                wp_reset_postdata();
            }else{
                echo '<tr>';
                echo '<td colspan="3">Nada encontrado</td>';
                echo '</tr>';
            }

            // Fechando a tabela e a div da tabela responsiva
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
    
        }

        public static function getEntidade($cpf)
        {
            //pega o codigo da entidade
            $args = array(
                'post_type' => 'vendedores',
                'post_status' => 'publish',
                'posts_per_page' => 1, 
                'meta_query' => array(
                    array(
                        'key' => 'cpf-vendedor',
                        'value' => $cpf,
                        'compare' => '='
                    )
                ),
            );
            
            $result = new WP_Query($args);
            
            $nome_entidade = "-";
            $codigo_entidade = "-";
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $codigo_entidade= get_post_meta($post_id, 'codigo-entidade', true);
                }
                wp_reset_postdata();
            }


            //pega o codigo da entidade
            $args = array(
                'post_type' => 'entidades',
                'posts_per_page' => 1, 
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'entity-code',
                        'value' => $codigo_entidade,
                        'compare' => '='
                    )
                ),
            );
            
            $result = new WP_Query($args);
            
            $nome_entidade = "--";
            if ($result->have_posts()) {
                while ($result->have_posts()) {
                    $result->the_post();
                    $post_id = get_the_ID();
                    $nome_entidade = get_the_title();
                }
                wp_reset_postdata();
            }
            
            return $nome_entidade;

        }
    }
    ?>
