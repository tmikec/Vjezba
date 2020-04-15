<?php
//Klasa je predlozak za sve stranice koje cemo imati
session_start();

require "AuthSystem.php";

//abstract oznacava da se ne mogu stvarati objekti klase
//Ona sluzi kao predlozak za nasljedivanje
abstract class Page
{
    //Varijable u koje cemo spremiti objekte klase AuthSystem i PDO spoj na bazu
    public $_authenticator;
    public $_database;

    //Konstruktor u kojem se spajamo na bazu i stvaramo objekt klase AuthSystem
    public function __construct()
    {
        //Podaci za spajanje
        $dsn = "mysql:host=localhost;dbname=box";
        $user = "root";
        $pass = "";

        //stvaranje objekata
        //Pomocu objekta $this->_authenticator mozemo pozivati sve funkcije iz AuthSystem
        $this->_authenticator = new AuthSystem($dsn, $user, $pass, null);

        //Spoj na bazu (kao $con u ranijim primjerima)
        $this->_database = new PDO($dsn, $user, $pass, null);
    }

    //Funkcija za prikaz HTML sadrzaja
    //Ona ce se pozivati pri stvaranju bilo koje web stranice
    //Parametar $title ce biti naslov stranice
    public function Display($title)
    {
        //Provjerava zahtijeva li stranica da korisnik bude prijavljen
        //Provjera je li korisnik prijavljen
        if($this->PageRequiresAuthenticUser() && !$this->UserIsAuthenticated())
            $this->BackToLanding();

        print('<!DOCTYPE html>');
        print('<html lang="hr">');
        //Dohvaca zaglavlje HEAD
        print($this->GetHead($title));
        print('<body>');
        //Dohvaca izbornik za navigaciju
        print($this->GetNavigation());
        //Dohvaca sadrzaj stranice
        print($this->GetContent());
        print('</body>');
        print('</html>');
    }
    //Preusmjeri na pocetnu index.php
    public function BackToLanding()
    {
        //PHP naredba za preusmjeravanje
        header("Location: index.php");
    }

    //Provjerava je li korisnik priavljen
    private function UserIsAuthenticated()
    {
        //Pozivamo funkciju iz klase AuthSystem
        return $this->_authenticator->UserIsAuthentic();
    }
    //Funkcija za ispis zaglavlja
    private function GetHead($title)
    {
        //Dodajemo bootstrap datoteke, css i js
        $output = '';
        $output .='<head>';
        $output .='<meta charset="utf-8"';
        $output .='<title>' . $title . '</title>';
        //$output .='<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrat/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">';
        $output .='</head>';

        return $output;
    }

    //Funkcija koja vraca izbornik (navigaciju)
    private function GetNavigation()
    {
        $output = "";
        $output .= "<div class='container-fluid'><ul class='nav navbar-nav'>";
        $output .= '<li><a href="index.php">Pocetna</a></li>';

        //izbornik za prijavljene korisnike
        if($this->UserIsAuthenticated())
        {
            $output .= '<li><a href="moje.php">Moje datoteke</a></li>';
            $output .= '<li><a href="postavke.php">Moje postavke</a></li>';
            $output .= '<li><a href="odjava.php">Odjava</a></li>';
        }
        //Izbornik za neprijavljene korisnike
        else{
            $output .= '<li><a href="prijava.php">Prijava</a></li>';
            $output .= '<li><a href="registracija.php">Registracija</a></li>';
        }
        $output .="</ul></div>";
        return $output;
    }

    //Funkcije ce se definirati samo u klasama koje nasljeduju klasu Page
    abstract protected function PageRequiresAuthenticUser();

    abstract protected function GetContent();
}