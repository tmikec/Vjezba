<?php
    require 'classes/Page.php';

    //Klasa koja se koristi za izmjenu lozinke
    class Postavke extends Page
    {
        protected function GetContent()
        {
            //Funkcija koja ce obraditi podatke iz obrasca nakon klika na gumb
            $this->HandleFormData();

            $output = '';

            $output .= '<form method="POST">';
            $output .= '<table>';
            $output .= '<tr><th>Promjena zaporke</th><td></td></tr>';
            $output .= '<tr><th>Nova zaporka:</th><td><input class="form-control" type="password" name="p1"/></td></tr>';
            $output .= '<tr><th>Ponovljena zaporka:</th><td><input class="form-control" type="password" name="p2"/></td></tr>';
            $output .= '<tr><th></th><td><input type="submit" class="btn btn-info" name="btnSub" value="Promjeni zaporku"/></td></tr>';
            $output .= '</table>';
            $output .= '</form>';
            
            return $output;
        }

        private function HandleFormData()
        {
            //Ako nismo kliknuli na gumb zaustavi funkciju i nece obraditi obrazac
            if(!isset($_POST["btnSub"])) return;
            //Ako zaporke nisu jednake takoder zaustavi
            if($_POST["p1"] !== $_POST["p2"])
            {
                echo "Zaporke se moraju poklapali!";
                return;
            }

            //Dohvacamo id prijavljenog korisnika
            $id = $this->_authenticator->GetCurrentUserId();
            $newPassword = $_POST["p1"];
            //Poziv funkcije iz klase AuthSystem koja ce unijeti novu lozinku u bazu
            $this->_authenticator->ChangeUserPassword($id, $newPassword);

            echo "Zaporka promijenjena!";
        }

        //Stranica zahtijeva prijavljenog korisnika
        protected function PageRequiresAuthenticUser()
        {
            return true;
        }
    }

    $site = new Postavke();
    $site->Display('Algebrabox Moje Postavke');