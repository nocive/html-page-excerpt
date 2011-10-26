<?php

require_once( 'html_page_excerpt.php' );

$config = array( 'fetcher_timeout' => 20 );

/* example 1 */
$pe = new HTML_PageExcerpt( null, $config );
$pe->load( 'http://www.publico.pt/Tecnologia/nova-legislacao-para-combate-a-pirataria-no-prazo-maximo-de-um-ano_1500632' );
var_dump( $pe->get() );


/* example 2 */
$pe = new HTML_PageExcerpt( 'http://www.publico.pt/Tecnologia/nova-legislacao-para-combate-a-pirataria-no-prazo-maximo-de-um-ano_1500632', $config );
var_dump( $pe->get() );

?>
