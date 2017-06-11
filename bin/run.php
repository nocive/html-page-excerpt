#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

$urls = array(
    'http://vimeo.com/24997574',
    'http://circuspt.blogs.sapo.pt/276442.html',
    'http://www.publico.pt/Tecnologia/nova-legislacao-para-combate-a-pirataria-no-prazo-maximo-de-um-ano_1500632',
    'http://www.youtube.com/watch?v=EmLHOGT0v4c&feature=related',
    'http://photosynth.net/',
    'http://www.almadeviajante.com/travelnews/005273.php',
    'http://www.dn.pt/inicio/opiniao/interior.aspx?content_id=1882957&seccao=Jo%E3o+C%E9sar+das+Neves&tag=Opini%E3o+-+Em+Foco&page=-1',
    'http://cavalinhoselvagem.blogspot.com/2011/07/aniversariantes-de-julho.html',
    'http://mfmodafeminina.blogs.sapo.pt/21640.html',
    'http://enfermagemnopc.blogs.sapo.pt/707.html',
    'http://alentejomagazine.com/2006/02/632/',
    'http://www.noticiasdevilareal.com/noticias/index.php?action=getDetalhe&id=762',
    'http://pplware.sapo.pt/windows/software/auslogics-duplicate-file-finder-remova-ficheiros-repetidos/',
    'http://musica.sapo.pt/noticias/kaossilator_-_sintetizador_de_bolso',
    'http://www.setubalnarede.pt/content/index.php?action=articlesDetailFo&rec=9255',
    'http://www.ionline.pt/conteudo/7749-europeias-ferreira-leite-apela-ao-dever-civico-que-todos-tem-votar',
    'http://www.sodoliva.com',
    'http://www.sabado.pt/Actualidade/Especial-Europeias/Blogues.aspx',
    'https://www.sugarsync.com/',
    'http://www.jogossantacasa.pt/',
    'http://www.jn.pt/PaginaInicial/Policia/Interior.aspx?content_id=1902690',
    'http://www.cmjornal.xl.pt/detalhe/noticias/exclusivo-cm/angelico-gnr-investiga-crime-de-homicidio',
    'http://www.agencia.ecclesia.pt/cgi-bin/noticia.pl?id=31713',
    'http://www.jn.pt/PaginaInicial/Mundo/Interior.aspx?content_id=1770243',
);

$pe = \HTMLPageExcerpt\Main::createFromIni(__DIR__ . '/../config.ini');

echo "\n\n";
foreach ($urls as $url) {
    echo "** $url\n";
    try {
        $pe->loadURL($url);
        $data = $pe->get('*', true);
        var_dump($data);
    } catch (\HTMLPageExcerpt\Exception\CommunicationException $e) {
        echo ">> Fetching error, skipping\n";
    }
}
