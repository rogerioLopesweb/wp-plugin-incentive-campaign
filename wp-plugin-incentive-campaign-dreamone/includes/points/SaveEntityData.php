<?php
    class SaveEntityData
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
                        '/sincronizacao/entidades/',
                        array(
                          'methods' => 'GET',
                          'callback' => 'SaveEntityData::readSaveEntityData',
                        )
                    );
                }
            );
        }
  
        
        public static function readSaveEntityData() {
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
                    
                    // Access the post data, e.g., title, content, etc.
                    $post_id = get_the_ID();
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
                        
                        update_post_meta($post_id, 'sincronizacao-status', 'OK');
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

                        update_post_meta($post_id, 'sincronizacao-status', 'OK');
                    }
                    
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
    }
    ?>