<?php
require "classes/Page.php";

//Klasa index nasljeduje klasu page
//Znaci da sadrzi sve njezine varijable i funkcije
class Index extends page
{
    //Funkcija za ispis sadrzaja stranice
    protected function GetContent()
    {
        $output = '';

        $output .= '<h1>Dobrodosli u Algebrabox</h1>';
        $output .= '<p>Pohranite svoje datoteke kod nas </p>';
        $output .= '<img style="width:200px;" src="https://repozitorij.algebra.hr/sites/repozitorij.algebra.hr/files/algebra_subbrand_vu_color-black_h_1.png">';
        return $output;
    }
    protected function PageRequiresAuthenticUser()
    {
        //Ova stranica zahtijeva da je korisnik prijavljen
        return false;
    }
}

//Stvarano objekt klase Index
$site = new Index();
//Pozivamo funkciju Display definirani u klasi Page
//Ova funkcija ce pozvati funkcije getHead GetContent, GetNavigation
$site->Display('Algebrabox Index');