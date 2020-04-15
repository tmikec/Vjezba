<?php
    require "classes/Page.php";
    //Klasa u kojoj radimo brisanje datoteke
    class Obrisi extends Page
    {
        protected function GetContent()
        {
            $this->HandleFormData();
            //provjera postoji li GET varijabla i jeli korisnik vlasnik datoteke
            //Da ne dopustimo brisanje tudih datoteka
            //Ako ne postoji id ili korisnik nije vlasnik vrati na pocetnu
            if(!isset($_GET["id"]) || $this->NotFileOwner($_GET["id"]))
                $this->BackToLanding();

            $fileId = $_GET["id"];
            //Pronadi datoteku sa trazenim id u bazi
            $q = "SELECT name FROM files WHERE id = $fileId ;";

            foreach($this->_database->query($q) as $row)
            {
                //Dohvati ime datoteke
                $name = $row["name"];
            }

            $output = '';
            //Obrazac u kojem nas pita zelimo li brisati datoteku
            //Ako kliknemo da poziva se funkcija koja ce odredidi obrazac HandleFormData
            $output .= '<h5>Jeste li sigurno da zelite izbrisati datoteku <b>'.$name.'</b>?</b></h5>';
            $output .= '<form method="post">';
            $output .= '<input type="hidden name="fileId" value="'.$fileId.'"/>';
            $output .= '<input type="submit" class="btn btn-danger" name="btnSub" value="Da"/>';
            $output .= '</form>';
            $output .= '<a href="moje.php"> Povratak </a>';
            return $output;
        }

        //Dohvacamo direktorij u kojem je spremljena datoteka files/korisnickoime
        private function GetUploadPath()
        {
            $user = $this->_authenticator->GetCurretnUserName();
            $base = getcwd();
            return "$base\\files\\$user\\";
        }

        //Funkcija za obradu podataka iz obrasca
        private function HandleFormData()
        {
            if(!isset($_POST["btnSub"])) return;

            $fileId = $_POST ["fileId"];

            $q = "SELECT name FROM files WHERE id = $fileId;";
            // Dohvacamo ime datoteke
            foreach($this->_database->query($q) as $row)
            {
                $name = $row["name"];
            }
            //Putanja do datoteke, upload direktorij pus ime datoteke
            $path = $this->GetUploadPath().$name;
            //Upit za brisanje datoteke iz baze
            $q = "DELETE FROM files WHERE id = $fileId";
            //Zapocinjemo transakciju da mzoemo ponistiti brisanje ako dode do pogreske
            $this->_database->beginTransaction();

            //Ako birsanje nije uspjeno ponistiti transakciju i prekini funkciju
            //Prekidom funkcije nece se obrisati ni datoteka ni direktorij
            if($this->_database->exec($q) !==1)
            {
                echo "Pogreska pri brisanju datoteke!";
                $this->_database->rollBack();
                return;
            }
            //Sa funkcijom unlink brisemo datoteku iz direktorija
            //Ako dode do pgreske poonistiti transakciju, to jestponistiti i brisanje iz baze
            //Mora se obaviti brisanje u bazi i direktoriju ili se ponistava transakcija
            if(!unlink($path))
            {
                echo "Pogreska pri brisanju datoteke!";
                $this->database->rollBack();
                return;
            }
            //Ako je uspjesno obrisano potvrdi transakciju
            $this->_database->commit();
            //Vrati na pocetnu stranicu
            $this->BackToLanding();
        }
        //Provjera jel i korisnik vlasnik datoteke 
        private function NotFileOwner($fileid)
        {
            $ownerId = $this->_authenticator->GetCurrentUserId();
            //Upit nad bazom koji trazi id datoteke i id vlasnika
            $q = "SELECT 1 FROM files WHERE id = $fileId AND ownerId = $ownerId;";

            $count = 0;
            //Pokretanje upita koji vraca broj datoteka, ako je 0 nije vlasnik
            foreach($this->_database->query($q) as $row)
            {
                $count++;
            }
            //Ako je broj datoteka nula vrati true, tj korisnik nije vlasnik
            return $count === 0 ;
        }
        //Stranica zahtijeva prijavljenog korisnika
        protected function PageRequiresAuthenticUser()
        {
            return true;
        }
    }
    //Stvaranje objekta klase Obrisi i prikaz sadrzaja
    $site = new Obrisi();
    $site->Display('AlgebraBox Brisanje datoteke');