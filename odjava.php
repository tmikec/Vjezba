<?php
    require "classes/Page.php";

    class Odjava extends Page
    {
        protected function GetContent()
        {
            //Sa naredbom brisemo sve session varijable
            session_unset();
            //Zatvaramo session
            session_destroy();
            //Preusmjeravamo na pocetnu stranicu
            $this->BackToLanding();
        }
        protected function PageRequiresAuthenticUser()
        {
            //Korisnik mora biti prijavljen
            return true;
        }
    }

    //Stvaranje objekta i prikaz stranice
    $site = new Odjava();
    $site->Display('AlgebraBox Logout');