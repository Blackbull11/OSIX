<?php
session_start();
/*---------------------------------------------------------------*/
/*
    Titre : Calcul et affiche le nombre de ligne de plusieurs fichiers

    URL   : https://phpsources.net/code_s.php?id=435
    Auteur           : bud
    Date édition     : 23 Juil 2008
    Date mise à jour : 29 Aout 2019
    Rapport de la maj:
    - fonctionnement du code vérifié
*/
/*---------------------------------------------------------------*/

function counter($dir)
{
    $handle = opendir($dir);

    $nbLines = 0;

    while( ($file = readdir($handle)) != false )
    {
        if( $file != "." && $file != "..")
        {
            if( !is_dir($dir."/".$file) )
            {
                if( preg_match("#\.(php|html|js|txt)$#", $file) )
                {
                    $nb = count(file($dir."/".$file));
                    echo $dir,"/",$file," => <strong>",$nb,"</strong><br />\n";
                    $nbLines += $nb;
                }
            }
            else
            {
                $nbLines += counter($dir."/".$file);
            }
        }
    }
    closedir($handle);

    return $nbLines;
}
if ($_SESSION['is_admin'] == 1) {
    echo '<h1 style="padding : 2em;" >Nombre total de lignes : '.counter(".") . '</h1>';
}

?>