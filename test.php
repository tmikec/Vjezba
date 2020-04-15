<?php
session_start();
include "classes/AuthSystem.php";

//Podaci za spoj na bazu
$dsn = "mysql:host=localhost;dbname=box";
$user = "root";
$pass = "";

//Stvaranje novog objekta klase AuthSystem
$auth = new AuthSystem($dsn, $user, $pass, null);

//Stvaranje korisnika pozivom funkcije CreateUser
//$auth->CreateUser('userssa','lozinka');

//Stvorimo korisnika pozivom funkcije CreateUser
//$auth->CreateUser("test1sss","mojalozinka");
//if(true==$auth->slow_equals("mikec1s", "mikec1"))
//{
//    echo "<br>Isti su.";
//}
//else{
//    echo "<br>Nisu isti.";
//} 

//$auth->AuthenticateUser("mikec1,lozinka");
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

//Provjera je li korisnik prijavljen
if ($auth->UserIsAuthentic())
{
    echo "<br>Korisnik je prijavljen";
}
else{
    echo "<br>Korisnik nije prijavljen";
} 

echo "Id trenutnog usera je: " . $auth->GetCurrentUserId() ."<br/>";

echo "Username trenutnog usera je: " . $auth->GetCurrentUserName();