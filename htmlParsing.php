<!DOCTYPE html>
<html>
<head>
    <meta charset = "utf-8">
    <title>
        HtmlParcing
    </title>
</head>
<body>
<?php

$user = "andrew";
$password = "Qq123456789";
$database = "mydb";
$host = "localhost";

$sqlLink = mysqli_connect($host, $user, $password, $database);

$link = 'https://etpgpb.ru/procedures/';
$page = file_get_contents($link);
$pattern_1 = '/[№]+[ ]+[А-Г]+[0-9]{1,20}|[№]+[ ]+[0-9]{1,20}/'; /* получение кода процедуры */
$pattern_2 = '/[a-z=]+[\"]+[\/]+[a-z]+[\/]+[a-z]+[\/]+[0-9]+[\/]+[\?]+[\"]/'; /* получение ссылки на следующую страницу, нужно использовать preg_match (чтобы идти сразу на вторую страницу) */
$pattern_3 = '/[a-z=]+[\"]+[\/]+[a-z]{9}+[\/]+[a-z]{6}+[\/]+[a-z_]{2,20}+[\/]+[a-z0-9-]+[\/]+[\"]/'; /* получение ссылки на страницу со сведениями о торгах */

/* it works. function for parsing html code */

function htmlParsing($p1,$p2,$p3,$htmlPage, $sqlLink) {

    /* it works. condition for getting massive of procedure links */

    if ( preg_match_all($p3,$htmlPage,$matches) ) {
       
        $result = array_unique($matches[0]);
        //print_r ($result);
        
       
       /* it works. getting link and redirectinng on page */
       
        $r = rtrim($result[0],'"');
        $l = ltrim($r,'href="/'); 
        $link = "https://etpgpb.ru/$l";
        $page_1 = file_get_contents($link);
        print_r ($link);
        //print_r ($page_1);
        
       /*-----------------------------------------------------------------------*/
       
       /* it works. getting procedure code in link */

       $pattern_4 = '/<b>+[А-Яа-я]+[0-9]{6,15}+<\/b>|<b>+[0-9]{6,15}+<\/b>/';
       preg_match_all($pattern_4,$page_1,$matches_1);
       $rc = rtrim($matches_1[0][0],'</b>');
       $lc = ltrim($rc,'<b>');
       print_r ($lc);

       /*-----------------------------------------------------------------------*/
       
       /* it works. getting links on inner documentation */

       $pattern_5 = '/<a.class="block__docs_container_document_link block__docs_container_document_link_word".href=["\'](.*?)["\'].*?>(.*?)</i'; //универсальный поиск
       if (preg_match_all($pattern_5,$page_1,$matches_2)) {
        $matches_2 =  array_unique($matches_2[0]);
        //print_r ($matches_2);
        $newArr = array();
        $t = ' ';
           for ($i = 0; $i < count($matches_2); $i++) {        
                $tmp = substr($matches_2[$i],94);               
                $tmp = str_replace('">', " - ", $tmp);               
                $tmp = rtrim($tmp, '<');
                $t .= $tmp."+";        
           };
       $t = rtrim($t, '+');
       print_r ($t);
       };
       
       /*-----------------------------------------------------------------------*/
       
    };
/*-----------------------------------------------------------------------*/   
    
};

/*-----------------------------------------------------------------------*/   

/* it works. calling procedure, which gives results searching */

htmlParsing($pattern_1, $pattern_2, $pattern_3, $page, $sqlLink);

/*-----------------------------------------------------------------------*/

/* it works. loading data in table */

function sqlProcedurePut($sqlLink, $procCode, $procLink, $procDoc) {
    
    $query ="INSERT INTO procinfo (procCode, procLink, procDoc) VALUES('$procCode','$procLink', '$procDoc')";
    mysqli_close($sqlLink);

};

/*-----------------------------------------------------------------------*/

function sqlProcedureGet($sqlLink) {

    $query ="SELECT * FROM procinfo";

    $res =  $sqlLink->query($query);
    while($row = mysqli_fetch_array($res)) {
        $docList = explode('+', $row['procDoc']);
        echo '<p>'.$row['idProc'].". "."Код процедуры: ".$row['procCode'].'<br>'."Ссылка на процедуру: ".$row['procLink'].'<br>'."Документация к процедуре: ";
        $docList = explode('+', $row['procDoc']);
        if ($row['procDoc']) {
            for ($i = 0; $i < count($docList); $i++) {
                echo '<ul>'.'<li>'.$docList[$i].'</li>'.'</ul>';
            };
        } else echo "отсутствует";
        echo '</p>';         
    }; 
    mysqli_close($sqlLink);

};

//sqlProcedureGet($sqlLink);

?>
</body>
</html>