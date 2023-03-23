<?php
    class SaveEntityData
    {
        public static function init()
        {
            add_action('admin_footer', 'SaveEntityData::my_add_sincronizar_button');
            add_action('wp_ajax_my_ajax_action', 'SaveEntityData::readSaveEntityData'); // For logged-in users
            add_action( 'wp_ajax_nopriv_get_data', 'SaveEntityData::readSaveEntityData' );
            add_action('admin_footer','SaveEntityData::sinc_enqueue_frontend_scripts');

            /*add_action( 'init',  array('SaveEntityData', 'create_custom_post_status'),10,0);
            add_action('save_post', array('SaveEntityData', 'custom_display_post_states'), 10, 3);
            add_action( 'save_post',  array('SaveEntityData', 'changeStatus'),10,3 );
            //add_action('save_post', array('SaveEntityData', 'readSaveEntityData'), 999, 3);*/
        }
  
        public static function my_add_sincronizar_button() {
            // Check if the current screen is the 'edit' screen for the 'extrato-trimestral-g' post type
            $screen = get_current_screen();
            if ('edit' === $screen->base && 'extrato-trimestral-g' === $screen->post_type) {
                // Output the button and JavaScript code
                ?>
                <style>
                    #sincronizar-button {
                        background-color: #007cba;
                        border-color: #007cba;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 3px;
                        padding: 4px 8px;
                        margin-top: 4px;
                        display: inline-block;
                        margin-left:100px;
                    }
                </style>
                 <script>
            jQuery(document).ready(function($) {
                // Check if the button already exists
                if ($('#sincronizar-button').length === 0) {
                    // Create the button element
                    var sincronizarButton = $('<a href="#" id="sincronizar-button">Sincronizar</a>');
                    // Append the button to the existing '.alignleft' container
                    $('.page-title-action').append(sincronizarButton);
                    // Attach click event to the button
                    sincronizarButton.on('click', function(e) {
                        e.preventDefault();
                        // Your synchronization logic here
                        // e.g., make an AJAX request to your synchronization script

                        jQuery.ajax({
                            url: 'admin-ajax.php', // The WordPress AJAX handler URL (for logged-in users)
                            type: 'POST',
                            data: {
                                action: 'SaveEntityData::readSaveEntityData', // The action hook name
                                // Additional data to send with the request (e.g., nonce, form data, etc.)
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Handle the successful response
                                    console.log(response.message);
                                } else {
                                    // Handle the error response
                                    console.error('An error occurred while processing the AJAX request.');
                                }
                            },
                            error: function() {
                                // Handle the AJAX request error
                                console.error('An error occurred while sending the AJAX request.');
                            },
                        });
                        alert('Sincronização iniciada!');
                    });
                }
            });
        </script>
                <?php
            }
        } 
        public static function sinc_enqueue_frontend_scripts() {
            // Register the JavaScript code
           // wp_enqueue_script('sinc-ajax-script', get_template_directory_uri() . '/js/my-ajax-script.js', array('jquery'), '1.0.0', true);
        
            // Localize the script with the AJAX URL and nonce (if necessary)
            wp_localize_script('sinc-ajax-script', 'SincAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('sinc-ajax-nonce'),
            ));
        }
        public static function readSaveEntityData() {
            // Define the query arguments
            $args = array(
                'post_type'      => 'extrato-trimestral-g',
                'posts_per_page' => 100, 
                'meta_key'       => 'sicronizacao-status',
                'meta_value'     => 'PENDENTE',
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
                        'meta_key' => 'entity-code',
                        'meta_value' => $codigo_entidade,
                        'posts_per_page' => 1,
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

                        update_post_meta($post_id, 'sicronizacao-status', 'OK');
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