<?php
	require "classes/Page.php";
	
	//Nasljeđuje klasu Page
	class Registracija extends Page
	{
		//Funkcija za dohvat sadržaja
		protected function GetContent()
		{
			//Poziv funkcije za obradu podataka iz obrasca ispod
			$this->HandleFormData();
			
			$output = '';
			//Obrazac sa POST metodom u koji unosi korisničko ime i lozinka
			$output .= '<form method="POST">';
			$output .= '<table>';
			$output .= '<tr><th>Korisnicko ime:</th><td><input class="form-control" type="text" name="un"/></td></tr>';
			$output .= '<tr><th>Zaporka:</th><td><input class="form-control" type="password" name="p1"/></td></tr>';
			$output .= '<tr><th>Ponovljena zaporka:</th><td><input class="form-control" type="password" name="p2"/></td></tr>';
			$output .= '<tr><th></th><td><input type="submit" class="btn btn-info" name="btnSub" value="Registriraj se"/></td></tr>';
			$output .= '</table>';
			$output .= '</form>';
			
			return $output;
		}
		
		//Funkcija koja sprema podatke iz obrasca ako se klikne na gumb Registriraj se
		private function HandleFormData()
		{
			//Ako nije kliknuto na gumb zaustavi funkciju as return
			if(!isset($_POST["btnSub"])) return;
			
			//Provjera jesu li lozinka i ponovljena lozinka iste ako nisu zaustavi funkciju
			if($_POST["p1"] !== $_POST["p2"])
			{
				echo "Zaporke se moraju poklapati!";
				return;
			}
			
			//Spremi u varijable korisničko ime i lozinku iz POST-a
			$username = $_POST["un"];
			$password = $_POST["p1"];
			
			//Unosimo novog korisnika u bazu funkcijom iz klase AuthSystem
			//Unosi u tablicu users korisničko ime i hash lozinke
			$this->_authenticator->CreateUser($username, $password);
			
			//Prijavi korisnika odmah nakon registracije
			$this->_authenticator->AuthenticateUser($username, $password);
			
			//Vrati na početnu stranicu
			if($this->_authenticator->UserIsAuthentic())
				$this->BackToLanding();
		}
		
		protected function PageRequiresAuthenticUser()
		{
			//Stranica ne zahtijeva prijavljeno korisnika
			return false;
		}
	}
	
	//Stvaranja objekta klase Registracija i poziv funkcije za prikaz sadržja
	$site = new Registracija();
	$site->Display('AlgebraBox Registracija');