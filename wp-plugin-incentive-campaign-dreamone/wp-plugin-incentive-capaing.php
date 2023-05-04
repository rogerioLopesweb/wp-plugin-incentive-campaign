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
include __DIR__ . '/includes/general/General.php';
include __DIR__ . '/includes/points/SaveDataImport.php';
include __DIR__ . '/includes/ranking/Ranking.php';
include __DIR__ . '/includes/ranking/RankingWinners.php';
include __DIR__ . '/includes/entity/EntitySeller.php';


Login::init();
General::init();
SaveDataImport::init();
Ranking::init();
EntitySeller::init();
RankingWinners::init();

?>