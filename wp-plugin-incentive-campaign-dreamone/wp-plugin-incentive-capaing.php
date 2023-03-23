<?php

/*
Plugin Name: Dreamone Campaign Incentive

Description: Campanha de incentivo Montanha de Vendas

Version: 1.0

Author: TECH LEAD - Rogério Lopes 

Author URI: https://www.linkedin.com/in/rogerio-tech-lead/

License: GPL2
*/
define( 'CP_PLUGIN_URL', __FILE__ );
include __DIR__ . '/includes/login/Login.php';
include __DIR__ . '/includes/points/SaveEntityData.php';

Login::init();
SaveEntityData::init();

?>