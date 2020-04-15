<?php

	//Definiranje konstanti
	//Algoritam za kriptiranje je sha256
	define("PBKDF2_HASH_ALGORITHM", "sha256");
	//Broj iteracija pri kriptiranju
	define("PBKDF2_ITERATIONS", 1000);
	//Veličina salt u bajtovima
	define("PBKDF2_SALT_BYTE_SIZE", 24);
	//Veličina kripirane lozinke u bajtovima
	define("PBKDF2_HASH_BYTE_SIZE", 24);
	
	//Primjer hash lozinke
	//sha256:1000:Aq8s+Fp6YJMndoTIT83BccX8E0BSDOMT:whwoVxhFAg0+cDgeMZlcjXpb5CTjWSH2

	//Konstante potrebne za usporedbu unesene lozinke i lozinke iz baze
	//Da se za obje koristi isto kriptiranje
	//Nakon što se napravi explode() lozinke iz baze indeksi pojedinih dijelova
	define("HASH_SECTIONS", 4);
	//Indeks na kojem se nalazi algoritam sha256
	define("HASH_ALGORITHM_INDEX", 0);
	//Indeks za broj iteracija 1000
	define("HASH_ITERATION_INDEX", 1);
	//Indeks za salt Aq8s+Fp6YJMndoTIT83BccX8E0BSDOMT
	define("HASH_SALT_INDEX", 2);
	//Indeks gdje je loznika whwoVxhFAg0+cDgeMZlcjXpb5CTjWSH2
	define("HASH_PBKDF2_INDEX", 3);
	
	

	class AuthSystem
	{	
		//Varaijabla u koju spremamo spoj na bazu podataka
		private $databaseConnection;
		
		//Konstruktor sa 4 parametra
		function __construct($dsn, $user = null, $pass = null, $options = null)
		{
			
			try 
			{	//Ako se dogodi pogreška unutar try presko na blok catch
				$this->databaseConnection = new PDO($dsn, $user, $pass, $options);
				//echo "Connection successfull";
			} 
			catch (PDOException $e) 
			{
				echo 'Connection failed: ' . $e->getMessage();
			}
			
			if(!$this->DataTablesExists())
				$this->CreateTables();
		}
    
    
		//Provjerava postoji li korisnik u tablici users
		private function DataTablesExists()
		{
			try
			{	
				//Ako se dogodi pogreška preskače se na catch blok
				//Upit nad tablicom users, ako ne postoji korisnik vraća false, ako postoji true
				//$this->databaseConnection u ovu varijablu je spremljen spoj na bazu
				if(!$this->databaseConnection->query("select 1 from users"))
					return false;
				else
					return true;
			}
			catch (PDOException $e)
			{
				return false;
			}
		}
		        	
    //Stvara tablicu users ako ona ne postoji
		private function CreateTables()
		{
			$query = "";
			//SQL upit za stvaranje tablice users
			$query .= "CREATE TABLE users";
			$query .= "(";
			$query .= "id INT NOT NULL AUTO_INCREMENT,";
			$query .= "username VARCHAR(50) NOT NULL UNIQUE,";
			$query .= "hash VARCHAR(255) NOT NULL,";
			$query .= "PRIMARY KEY (id)";
			$query .= ");";
			
			//Pokretanje gornjeg upita
			$this->databaseConnection->exec($query);
			
			//Provjera jesmo li uspješno stvorili tablicu pozivom funkcije DataTablesExists()
			if(!$this->DataTablesExists())
			{
				var_dump($this->databaseConnection->errorInfo());
				throw new Exception("Error while creating database!");
			}
		}
		
    
		//Funkcija koja unosi korisnika pri registraciji
		public function CreateUser($username, $password)
		{
			//Poziv funkcije za kriptiranje lozinke
			$hash = $this->create_hash($password);
			//$hash = $password;
			
			$query = "";
			
			$query .= "INSERT INTO users";
			$query .= "(username, hash)";
			$query .= "VALUES";
			$query .= "(:username, :hash);";
			
			$stmt = $this->databaseConnection->prepare($query);
			
			$stmt->bindParam(':username', $username, PDO::PARAM_STR, 50);
			$stmt->bindParam(':hash', $hash, PDO::PARAM_STR, 255);

			if(!$stmt->execute())
			{
				throw new Exception("Error while creating user!");
			}  
      
		}
    
    ///////////////////password hashing methods/////////////////
		/// Source: https://crackstation.net/hashing-security.htm //
		private function create_hash($password)
		{
			//Generira slučajni niz od 24 znaka je je PBKDF2_SALT_BYTE_SIZE=24
			//$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
      $salt = substr(md5(time()), 0, 24);
			//Funkcija sa return vraća kriptiranu lozinku
			//Vraća hash algoritam:broj iteracija:salt:kriptiranu lozinku
			//Poziv funkcije pbkdf2 koja radi kriptiranje
			//Ovdje koristimo konstante koje smo definirali na početku PBKDF2_HASH_ALGORITHM
			return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
				base64_encode($this->pbkdf2(
					PBKDF2_HASH_ALGORITHM,
					$password,
					$salt,
					PBKDF2_ITERATIONS,
					PBKDF2_HASH_BYTE_SIZE,
					true
				));
		}
    
    
    //Funkcija koja računa kriptiranu lozinku pomoću sha256
		private function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
		{
			$algorithm = strtolower($algorithm);
			//Provjerava postoji li zadani algoritam u popisu algoritama
			//hash_algos() vraća popis algoritama
			if(!in_array($algorithm, hash_algos(), true))
				trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
			
			//Provjera ispravnost dužine ključa i broja iteracija
			if($count <= 0 || $key_length <= 0)
				trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
			
			//Ako postoji hash_pbkdf2 funkcija kriptiraj lozinku
			if (function_exists("hash_pbkdf2")) {
				if (!$raw_output) {
					$key_length = $key_length * 2;
				}
				//Ako se izvede ovaj dio return prekida izvođenje ostatka funkcije
				return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
			}

			$hash_length = strlen(hash($algorithm, "", true));
			$block_count = ceil($key_length / $hash_length);
			
			//Ako ne postoji hash_pbkdf2 funkcija sami kriptiramo lozinku
			$output = "";
			for($i = 1; $i <= $block_count; $i++) {
				$last = $salt . pack("N", $i);
				$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
				for ($j = 1; $j < $count; $j++) {
					$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
				}
				$output .= $xorsum;
			}

			if($raw_output)
				return substr($output, 0, $key_length);
			else
				return bin2hex(substr($output, 0, $key_length));
		}
		///////////////////password hashing methods/////////////////
    
		
		//Funkcija koja vrši prijavu korisnika, parametri su koris. ime i lozinka
		//Ako su jednaki onima u bazi prijaviti će korisnika
		public function AuthenticateUser($username, $password)
		{
			$query = "";
			//Upit nad bazom gdje dohvaćamo hash i id za korisničko ime uneseno u obrazac
			$query .= "SELECT hash, id FROM users ";
			$query .= "WHERE username LIKE :username;";
			
			//Prepared upit za PDO
			$stmt = $this->databaseConnection->prepare($query);
			
			//Povezujemo pripremljeni upit sa varijablama
			$stmt->bindParam(':username', $username, PDO::PARAM_STR, 50);
			
			if($stmt->execute())
			{
				$data = $stmt->fetchAll();
				
				//Ako nema korisničkog imena u bazi postavi SESSION na false
				if(count($data) === 0)
				{
					//Korisnik nije prijavljen
					$_SESSION["authenticated"] = false;
					return;
				}
				
				//U varijable spremamo hash i id iz baze
				$hash = $data[0]['hash'];
				$id = $data[0]['id'];
				
				//Validiramo unesenu lozinku i hash iz baze
				if($this->validate_password($password, $hash))
				{	
					//Ako su isti prijavi korisnika
					$_SESSION["authenticated"] = true;
					$_SESSION["username"] = $username;
					$_SESSION["UserId"] = $id;
				}
				else
				{
					$_SESSION["authenticated"] = false;
				}
			}
			else
			{
				throw new Exception("Failed to prepare statement!");
			}
		}
    
    //Provjera jesu li $a i $b jednaki, gdje su $a i $b stringovi
		private function slow_equals($a, $b)
		{	
			//Jesu li iste dužine, ako jesu $diff će biti jednak 0
			//^ je xor operator nad bitovima
			$diff = strlen($a) ^ strlen($b);
			//Ako su iste dužine provjeramo znak po znak od $a i $b
			//Prvi znak, pa drugi, pa sve do zadnjeg znaka
			for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
			{
				//Ako su isti $diff će biti 0, inače 1
				//Funkcija ord vraća brojačanu vrijednost znaka od 0 do 255
				//Operator |= uspoređuje sa prethodnom vrijednosti $diff i rezultatom usporedbe
				//$a i $b
				$diff |= ord($a[$i]) ^ ord($b[$i]);
			}
			//Ako je $diff ostao 0 stringovi su jednaki
			return $diff === 0;
		}
    
    //Funkcija za validaciju lozinke, je li utipkana lozinka iz obrasca
		//jednaka lozinci iz baze
		private function validate_password($password, $correct_hash)
		{
			//Razdvaja lozinku iz baze na dijelove(algoritam, salt, bro iter. i lozinka)
			$params = explode(":", $correct_hash);
			if(count($params) < HASH_SECTIONS)
			   return false;
			//Računamo hash za utipkanu lozinku i sa slow_equals funkcijom
			//Uspoređujemo sa postojećim hashom u bazi
			//Novi hash se računa sa istim parametrima kao i lozinka iz baze (koristimo $params)
			$pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
			return $this->slow_equals(
				$pbkdf2,
				$this->pbkdf2(
					$params[HASH_ALGORITHM_INDEX],
					$password,
					$params[HASH_SALT_INDEX],
					(int)$params[HASH_ITERATION_INDEX],
					strlen($pbkdf2),
					true
				)
			);
		}
		
		//Funkcija provjerava je li korisnik prijavljen
		//Ovo će se koristiti za provjeru prijave i pristup sadržajima za prijavljene korisnike
		public function UserIsAuthentic()
		{
			//Provjerava je li postavljen session za prijavu, ako nije vraća false
			if(isset($_SESSION["authenticated"]))
				return $_SESSION["authenticated"];
			else
				return false;
		}
		
		//Funkcija za promjenu lozinke, parametri su id i nova lozinka
		public function ChangeUserPassword($id, $newPassword)
		{
			//Pravimo hash lozinke pomoću gotove funkcije create_hash
			$hash = $this->create_hash($newPassword);
			
			$query = "";
			//Upit za promjenu lozinke
			$query .= " UPDATE users";
			$query .= " SET hash = :hash";
			$query .= " WHERE id = :id;";
			
			//Prepared upit za PDO
			$stmt = $this->databaseConnection->prepare($query);
			
			$stmt->bindParam(':hash', $hash, PDO::PARAM_STR, 255);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);

			//Pokretanje upita
			if(!$stmt->execute())
			{
				throw new Exception("Error while updating user password!");
			}
		}
		
		//Vraća id prijavljenog korisnika
		public function GetCurrentUserId()
		{
			return $_SESSION["UserId"];
		}
		
		//Vraća korisničko ime prijavljenog korisnika
		public function GetCurrentUserName()
		{
			return $_SESSION["username"];
		}
			
	}
	