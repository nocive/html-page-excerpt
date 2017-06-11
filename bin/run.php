#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

$urls = array(
    'http://circuspt.blogs.sapo.pt/276442.html',
    'https://www.publico.pt/2017/06/11/mundo/noticia/trump-suspende-visita-ao-reino-unido-1775331',
    'http://www.youtube.com/watch?v=EmLHOGT0v4c&feature=related',
    'http://photosynth.net/',
    'http://www.almadeviajante.com/travelnews/005273.php',
    'http://www.dn.pt/inicio/opiniao/interior.aspx?content_id=1882957&seccao=Jo%E3o+C%E9sar+das+Neves&tag=Opini%E3o+-+Em+Foco&page=-1',
    'http://cavalinhoselvagem.blogspot.com/2011/07/aniversariantes-de-julho.html',
    'http://mfmodafeminina.blogs.sapo.pt/21640.html',
    'http://enfermagemnopc.blogs.sapo.pt/707.html',
    'http://www.noticiasdevilareal.com/',
    'http://pplware.sapo.pt/windows/software/auslogics-duplicate-file-finder-remova-ficheiros-repetidos/',
    'http://musica.sapo.pt/noticias/kaossilator_-_sintetizador_de_bolso',
    'http://www.setubalnarede.pt/content/index.php?action=articlesDetailFo&rec=9255',
    'https://ionline.sapo.pt/artigo/567448/legislativas-francesas-primeiras-projecoes-colocam-partido-de-macron-em-vantagem?seccao=Mundo_i',
    'http://www.sodoliva.com',
    'http://www.sabado.pt/',
    'https://www.sugarsync.com/',
    'http://www.jogossantacasa.pt/',
    'http://www.cmjornal.xl.pt/detalhe/noticias/exclusivo-cm/angelico-gnr-investiga-crime-de-homicidio',
    'http://www.agencia.ecclesia.pt/cgi-bin/noticia.pl?id=31713',
    'http://www.jn.pt/PaginaInicial/Mundo/Interior.aspx?content_id=1770243',

//    'https://www.auto1-group.com/',
//    'https://www.auto1.com/',
//    'https://www.crunchbase.com/organization/auto1-group',
//    'https://www.bloomberg.com/research/stocks/private/snapshot.asp?privcapid=337644871',
//    'https://techcrunch.com/2017/05/22/auto1-group-a-young-used-car-marketplace-serving-europe-is-now-worth-2-8-billion/',
//    'https://www.stepstone.de/cmp/de/AUTO1-Group-GmbH-140454/jobs.html',
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
