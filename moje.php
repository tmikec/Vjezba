<?php
    require "classes/Page.php";

    class Moje extends Page
    {
        //Funkcija za prikaz sadrzaja
        protected function GetContent()
        {
            //Funkcija koja ce odrediti upload datoteke predane u obrascu
            $this->HandleFormData();

            $output= '';
            //Funkcija koja dohvaca ranije uploadane datoteke
            $output = $this->GetFileListTable();
            $output .= '<br/><br/>';
            $output .= '<h2>Dodaj novu datoteku</h2>';
            $output .= '<form method="post enctype="multipart/form-data">';
            $output .= 'Odaberite datoteku: <input type="file" name="fileToUpload" id="fileToUpload"/>';
            $output .= '<input type="submit" value="Dodaj datoteku" class="btn btn-info" name="btnSub"/>';
            $output .= '</form>';
            $output .= '';

            return $output;
        }
        //Za svakog korisnika datoteke se spremaju u direktorij files/korisnickoime
        private function GetUploadPath()
        {
            echo $user = $this->_authenticator->GetCurrentUserName();
            //Trenutni direktorij
            $base = getcwd();
            return "$base\\files\\$user\\";
        }
        private function HandleFormData()
        {
            if(!isset($_POST["btnSub"])) return;
            //Dohvati putanju gdje ce se uploadati datoteka
            echo $path = $this->GetUploadPath();
            //Ime datoteke nakon uploada zuadrzava staro ime(spajamo putanju i ime datoteke)
            $filePath = $path.basename($_FILES["fileToUpload"]["name"]);

            //Ako postoji datoteka s istim imenom zaustavi upload i ispisi poruku
            if(file_exists($filePath))
            {
                echo "Datoteka vec postoji.";
                return;
            }
            //Ako je prvi upload i ne postoji vas direktorij stvoriti cemo ga sa mkdir
            if(!file_exists($this->GetUploadPath()))
            {
                //Funkcija stvara direktorij za channel mode 0777
                mkdir($this->GetUploadPath(), 0777, true);
            }
            //Upload datoteke na server i spremanje u tablicu file podataka o datoteci
            if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"],$filePath));
            {
                $name = $_FILES["fileToUpload"]["name"];
                $size = filesize($filePath);
                $ownerId = $this->_authenticator->GetCurrentUserId();
                //Prepared upit za unis podataka o datoteci ( ime, velicina i id vlasnika)
                $q = "INSERT INTO files (name, size, ownerId) VALUES (:name, :size, :ownerId);";
                //Ako je upit pripremljen
                if($stmt = $this->_database->prepare($q))
                {
                    //Povezivanje parametara upita i varijabli
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR, 255);
                    $stmt->bindParam(':size', $size, PDO::PARAM_INT);
                    $stmt->bindParam(':ownerId', $ownerId, PDO::PARAM_INT);
                    //Ako je uspjesno spremljeno u bazu ispisi poruku
                    if($stmt->execute())
                    {
                        echo "Datoteka uspjesno dodana!";
                    }
                    else
                    {
                        var_dump($stmt->errorInfo());
                        echo "Izvrsavanje upita nije uspjelo!";
                        //Brisemo varijablu sa putanjom datoteke
                        unlink($filePath);
                        return;
                    }
                }
                else
                {
                    echo "Priprema upita nije uspjela!";
                    unlink($filePath);
                    return;
                }
            }
        }
        //Funkcija za dohvacanje postojecih datoteka
        private function GetFileListTable()
        {
            $output = '';

            $output .= '<table border="1" class="table table-striped">';
            //Dohvacamo id prijavljenog korisnika
            $ownerId = $this->_authenticator->GetCurrentUserId();
            //Iz tablice files dohvacamo sve datoteke kojima je vlasnik prijavljeni korisnik
            $q = "SELECT * FROM files WHERE ownerId = $ownerId";
            $output .= '<tr><th>Ime</th><th>Velicina</th><th>Upravljanje</th></tr>';
            $count = 0;
            //Za svaki redak iz tablice
            foreach($this->_database->query($q) as $row)
            {
                $name = $row["name"]; //Ime datoteke iz baze
                $size = $row["size"]; //Velicina
                $id = $row["id"]; //Id vlasnika
                //Korisnicko ime prijavljenog korisnikai na osnovu njega putanja do datoteke
                $owner = $this->_authenticator->GetCurrentUserName();
                $fileLoc = "files/$owner/$name";

                //Link na uredivanje datoteke sa GET varijablom id datoteke
                //Link za brisanje datoteke sa GET vraijablom id datoteke
                //Link za preuzimanje datoteke $fileLock
                $ctrls = '<a href="uredi.php?id=' . $id . '">Preimenuj</a> | <a href="obrisi.php?id=' . $id . '">Obrisi</a> | <a href="'.$fileLoc.'" download>Preuzmi</a>';
                //ispisujemo ime, velicinu i linkove od datoteke
                $output .= "<tr><td>$name</td><td>$size B</td><td>$ctrls</td></tr>";
                //Ukupan broj datoteka povecavamo za 1
                $count++;
            }
            //Ako je broj datoteka 0 ispisi da nema datoteka
            if($count ==0)
            {
                $output .= '<tr><td colspan="3">Nemate pohranjenih datoteka.</td></tr>';
            }
            $output .= '</table>';

            return $output;
        }
        protected function PageRequiresAuthenticUser()
        {
            return true;
        }
    }
    $site = new Moje();
    $site->Display('AlgebraBox Moje datoteke');