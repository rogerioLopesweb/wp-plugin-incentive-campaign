<?php
    class SaveDataImport
    {
        public static function init()
        {
            /*add_action( 'init',  array('SaveEntityData', 'create_custom_post_status'),10,0);
            add_action('save_post', array('SaveEntityData', 'custom_display_post_states'), 10, 3);
            add_action( 'save_post',  array('SaveEntityData', 'changeStatus'),10,3 );
            //add_action('save_post', array('SaveEntityData', 'readSaveEntityData'), 999, 3);*/
           //dominio/wp-json/api/v2/sincronizacao/entidades
            add_action(
                'rest_api_init',
                function () {
                    register_rest_route(
                        'api/v2',
                        '/sincronizacao/importacao/',
                        array(
                          'methods' => 'GET',
                          'callback' => 'SaveEntityData::readSaveData',
                        )
                    );
                }
            );
        }
  
        
        public static function readSaveData() {
            // Define the query arguments
            $args = array(
                'post_type'      => 'extrato-trimestral-g',
                'post_status'     => 'publish',
                'posts_per_page' => -1, 
                'meta_query' => array(
                    array(
                        'key' => 'sincronizacao-status',
                        'value' => 'PENDENTE',
                        'compare' => '=',
                        'type' => 'CHAR')
                     ),
            );

            // Create a new instance of WP_Query
            $query = new WP_Query($args);

            // Check if there are any posts matching the query
            if ($query->have_posts()) {
                // Loop through the posts
                while ($query->have_posts()) {
                    $query->the_post();

                    $post_id = get_the_ID();
                    SaveDataImport::readSaveEntityData($post_id);
                    SaveDataImport::readSaveRankingEntityData($post_id);
                    SaveDataImport::readSaveRankingSellersData($post_id);
                    
                    update_post_meta($post_id, 'sincronizacao-status', 'OK');
                }
                
                // Reset the post data after the loop
                wp_reset_postdata();

                $response = array(
                    'success' => true,
                    'message' => 'Sinscronizacao realiazada',
                );
            
                // Send the JSON response
                return wp_send_json($response);
            } else {
                $response = array(
                    'success' => true,
                    'message' => 'Nenhum registro para sicronizar',
                );
            
                // Send the JSON response
                return wp_send_json($response);
            }

           
        }

        public static function readSaveEntityData($post_id)
        {
            $codigo_entidade = get_post_meta($post_id, 'codigo-entidade', true);
            $nome_entidade = get_post_meta($post_id, 'nome-entidade', true);
            $curva_entidade = get_post_meta($post_id, 'curva-entidade', true);
            $uf_vendedor = get_post_meta($post_id, 'uf-vendedor', true);
            $regiao_vendedor = get_post_meta($post_id, 'regiao-vendedor', true);

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
                // Atualizar o post existente
                $entidade_post = $entidade_query->posts[0];
                $entidade_post_id = $entidade_post->ID;
                // Atualizar o título do post
                $entidade_post->post_title = $nome_entidade;
                wp_update_post($entidade_post);
                update_post_meta($entidade_post_id, 'entity-code', $codigo_entidade);
                update_post_meta($entidade_post_id, 'entity-curve', $curva_entidade);
                update_post_meta($entidade_post_id, 'entity-uf', $uf_vendedor);
                update_post_meta($entidade_post_id, 'entity-region', $regiao_vendedor);
            } else {
                // Inserir no post_type 'entidades'
                $entidade_post = array(
                    'post_title'    => $nome_entidade,
                    'post_type'     => 'entidades',
                    'post_status'   => 'publish',
                );
                // Insere o post e retorna o ID
                $entidade_post_id = wp_insert_post($entidade_post);
                // Adiciona os metadados ao post_type 'entidades'
                update_post_meta($entidade_post_id, 'entity-code', $codigo_entidade);
                update_post_meta($entidade_post_id, 'entity-curve', $curva_entidade);
                update_post_meta($entidade_post_id, 'entity-uf', $uf_vendedor);
                update_post_meta($entidade_post_id, 'entity-region', $regiao_vendedor);
            }
        }

       

        public static function readSaveRankingEntityData($post_id)
        {
            $ano =  get_post_meta($post_id, 'ano', true);
            $trimestre =  get_post_meta($post_id, 'trimestre', true);
            $codigo_entidade = get_post_meta($post_id, 'codigo-entidade', true);
            $nome_entidade = get_post_meta($post_id, 'nome-entidade', true);
            $pontos =  get_post_meta($post_id, 'pontos-entidade', true);
            $posicao = get_post_meta($post_id, 'ranking-premio-nacional-entidade', true);
            $meta = get_post_meta($post_id, 'faturamento-minimo-entidade-nacional', true);
            $vendas = get_post_meta($post_id, 'realizado-total-entidade', true);
            $porcentual = get_post_meta($post_id, 'atingimento-entidade', true);

            $args = array(
                'post_type' => 'ranking-entidades',  
                'posts_per_page' => 1,
                'meta_query' => array(
                'relation' => 'AND',
                    [
                        'meta_key' => 'codigo-entidade',
                        'meta_value' => $codigo_entidade,
                    ],
                    [
                        'meta_key' => 'entidade-ano',
                        'meta_value' => $ano,
                    ],
                    [
                        'meta_key' => 'entidade-trimestre',
                        'meta_value' => $trimestre,
                    ]
                ),
            );
            $result = new WP_Query($args);
            if ($result->have_posts()) {
                // Atualizar o post existente
                $post= $result->posts[0];
                // Atualizar o título do post
                $post->post_title = $nome_entidade;
                wp_update_post($post);
                update_post_meta($post->ID, 'codigo-entidade', $codigo_entidade);
                update_post_meta($post->ID, 'entidade-ano', $ano);
                update_post_meta($post->ID, 'entidade-trimestre', $trimestre);
                update_post_meta($post->ID, 'entidade-posicao', $posicao);
                update_post_meta($post->ID, 'entidade-pontos', $pontos);
                update_post_meta($post->ID, 'entidade-meta', $meta);
                update_post_meta($post->ID, 'entidade-vendas', $vendas);
                update_post_meta($post->ID, 'entidade-porcentual', $porcentual);
         
            } else {
                // Inserir no post_type 'ranking-entidades'
                $entidade_post = array(
                    'post_title'    => $nome_entidade,
                    'post_type'     => 'ranking-entidades',
                    'post_status'   => 'publish',
                );
                // Insere o post e retorna o ID
                $new_post_id = wp_insert_post($entidade_post);
                // Adiciona os metadados ao post_type 'entidades'
                update_post_meta($new_post_id, 'codigo-entidade', $codigo_entidade);
                update_post_meta($new_post_id, 'entidade-ano', $ano);
                update_post_meta($new_post_id, 'entidade-trimestre', $trimestre);
                update_post_meta($new_post_id, 'entidade-posicao', $posicao);
                update_post_meta($new_post_id, 'entidade-pontos', $pontos);
                update_post_meta($new_post_id, 'entidade-meta', $meta);
                update_post_meta($new_post_id, 'entidade-vendas', $vendas);
                update_post_meta($new_post_id, 'entidade-porcentual', $porcentual);
            }
        }

        public static function readSaveRankingSellersData($post_id)
        {
            $ano =  get_post_meta($post_id, 'ano', true);
            $trimestre =  get_post_meta($post_id, 'trimestre', true);
            $cpf_vendedor = get_post_meta($post_id, 'cpf-vendedor', true);
            $vendedor_nome = get_post_meta($post_id, 'nome-vendedor', true);

            $pontos_trilha = get_post_meta($post_id, 'pontos-trilha', true);

            $pontos_vendas_hunter = get_post_meta($post_id, 'pontos-hunter-vendas', true);
            $pontos_total_hunter = get_post_meta($post_id, 'pontos-hunter-vendas-trilha-total', true);
            $nacional_posicao_hunter =  get_post_meta($post_id, 'ranking-premio-nacional-hunter', true);
            $regional_posicao_hunter  =  get_post_meta($post_id, 'ranking-premio-regional-hunter', true);
           
            $vendas_hunter  =  get_post_meta($post_id, 'realizado-total-hunter', true);
            $meta_hunter  =  get_post_meta($post_id, 'faturamento-minimo-hunter', true);
            $porcentual_atingimento_hunter  =  get_post_meta($post_id, '', true);

            $pontos_vendas_farmer =  get_post_meta($post_id, 'pontos-farmer-vendas', true);
            $pontos_total_farmer =  get_post_meta($post_id, 'pontos-farmer-vendas-trilha-total', true);
            $nacional_posicao_farmer =  get_post_meta($post_id, 'ranking-premio-nacional-farmer', true);
            $farmer_posicao_farmer =  get_post_meta($post_id, 'ranking-premio-regional-farmer', true);
            $vendas_farmer =  get_post_meta($post_id, 'realizado-total-farmer', true);
            $meta_farmer =  get_post_meta($post_id, 'faturamento-minimo-farmer', true);
            $porcentual_atingimento_farmer =  get_post_meta($post_id, 'atingimento-farmer', true);

            $args = array(
                'post_type' => 'ranking-vendedores',  
                'posts_per_page' => 1,
                'meta_query' => array(
                'relation' => 'AND',
                    [
                        'meta_key' => 'cpf-vendedor',
                        'meta_value' => $cpf_vendedor,
                    ],
                    [
                        'meta_key' => 'ano',
                        'meta_value' => $ano,
                    ],
                    [
                        'meta_key' => 'trimestre',
                        'meta_value' => $trimestre,
                    ]
                ),
            );

            $result = new WP_Query($args);
            if ($result->have_posts()) {
                // Atualizar o post existente
                $post= $result->posts[0];
                // Atualizar o título do post
                $post->post_title = $vendedor_nome;
                wp_update_post($post);
                update_post_meta($post->ID, 'cpf-vendedor', $cpf_vendedor);
                update_post_meta($post->ID, 'ano', $ano);
                update_post_meta($post->ID, 'trimestre', $trimestre);
                update_post_meta($post->ID, 'pontos-trilha', $pontos_trilha);
                update_post_meta($post->ID, 'pontos-vendas-hunter', $pontos_vendas_hunter);
                update_post_meta($post->ID, 'pontos-total-hunter', $pontos_total_hunter);
                update_post_meta($post->ID, 'nacional-posicao-hunter', $nacional_posicao_hunter);
                update_post_meta($post->ID, 'regional-posicao-hunter', $regional_posicao_hunter);
                update_post_meta($post->ID, 'vendas-hunter', $vendas_hunter);
                update_post_meta($post->ID, 'meta-hunter', $meta_hunter);
                update_post_meta($post->ID, 'porcentual-atingimento-hunter', $porcentual_atingimento_hunter);
                update_post_meta($post->ID, 'pontos-vendas-farmer', $pontos_vendas_farmer);
                update_post_meta($post->ID, 'pontos-total-farmer', $pontos_total_farmer);
                update_post_meta($post->ID, 'nacional-posicao-farmer', $nacional_posicao_farmer);
                update_post_meta($post->ID, 'regional-posicao-farmer', $regional_posicao_farmer);
                update_post_meta($post->ID, 'vendas-farmer', $vendas_farmer);
                update_post_meta($post->ID, 'meta-farmer', $meta_farmer);
                update_post_meta($post->ID, 'porcentual-atingimento-farmer', $porcentual_atingimento_farmer);
         
            } else {
                // Inserir no post_type 'ranking-entidades'
                $entidade_post = array(
                    'post_title'    => $vendedor_nome,
                    'post_type'     => 'ranking-vendedores',
                    'post_status'   => 'publish',
                );
                // Insere o post e retorna o ID
                $new_post_id = wp_insert_post($entidade_post);
                // Adiciona os metadados ao post_type 'ranking-vendedores'
                update_post_meta($new_post_id, 'cpf-vendedor', $cpf_vendedor);
                update_post_meta($new_post_id, 'ano', $ano);
                update_post_meta($new_post_id, 'trimestre', $trimestre);
                update_post_meta($new_post_id, 'pontos-trilha', $pontos_trilha);
                update_post_meta($new_post_id, 'pontos-vendas-hunter', $pontos_vendas_hunter);
                update_post_meta($new_post_id, 'pontos-total-hunter', $pontos_total_hunter);
                update_post_meta($new_post_id, 'nacional-posicao-hunter', $nacional_posicao_hunter);
                update_post_meta($new_post_id, 'regional-posicao-hunter', $regional_posicao_hunter);
                update_post_meta($new_post_id, 'vendas-hunter', $vendas_hunter);
                update_post_meta($new_post_id, 'meta-hunter', $meta_hunter);
                update_post_meta($new_post_id, 'porcentual-atingimento-hunter', $porcentual_atingimento_hunter);
                update_post_meta($new_post_id, 'pontos-vendas-farmer', $pontos_vendas_farmer);
                update_post_meta($new_post_id, 'pontos-total-farmer', $pontos_total_farmer);
                update_post_meta($new_post_id, 'nacional-posicao-farmer', $nacional_posicao_farmer);
                update_post_meta($new_post_id, 'regional-posicao-farmer', $regional_posicao_farmer);
                update_post_meta($new_post_id, 'vendas-farmer', $vendas_farmer);
                update_post_meta($new_post_id, 'meta-farmer', $meta_farmer);
                update_post_meta($new_post_id, 'porcentual-atingimento-farmer', $porcentual_atingimento_farmer);
            }

        }



    }
    ?>