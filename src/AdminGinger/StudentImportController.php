<?php

namespace AdminGinger;

class StudentImportController {

    public $curDbStudents;
    public $newStudents;

    function __construct($uploadfile) {
        global $DB, $settings;

        if (file_exists($uploadfile)) {
            $fp = fopen($uploadfile, "r");
            $keys = [];
            $this->newStudents = [];
            $this->newStudentsById = [];

            while (!feof($fp)) {
                $row = utf8_encode(trim(str_replace("\n", "", fgets($fp, 8192))));
                if (empty($row))
                    continue;
                elseif (empty($keys))
                    $keys = str_getcsv($row, ';', '"', '"');
                else {
                    $res = str_getcsv($row, ';', '"', '"');
                    if (count($res) != count($keys))
                        continue;
                    $field = [];
                    foreach ($res as $k => $v)
                        $field[$keys[$k]] = $v;

                    if (empty($field['email']))
                        continue;

                    // public static $filieres = array(
                    //     'Intégré'    => 'Intégré',
                    //     'Apprentissage'   => 'Apprentissage',
                    //     'Permanent'  => 'Permanent',
                    //     'mgf'        => 'Master Génie Féroviaire',
                    //     'f_continue' => 'Formation Continue'
                    // );

                    if (substr($field['filiere_site'], 0, 8) == 'ICAM Int')
                        $filiere = 'Intégré';
                    else if (substr($field['filiere_site'], -2) == 'FC')
                        $filiere = 'f_continue';
                    else if (substr($field['filiere_site'], 0, 8) == 'ICAM App')
                        $filiere = 'Apprentissage';
                    else
                        $filiere = 'Permanent';

                    if (strpos($field['anniversaire'], '/') > -1) {
                        $anniv = explode('/', $field['anniversaire']);
                        $anniv = $anniv[2].'-'.$anniv[1].'-'.$anniv[0];
                    } else
                        $anniv = empty($field['anniversaire'])?'0000-00-00':$field['anniversaire'];
                    $user = [
                        "id_icam" => $field['id_icam']*1,
                        "login" => $field['email'],
                        "nom" => ucwords(strtolower($field['nom'])),
                        "prenom" => $field['prenom'],
                        "mail" => $field['email'],
                        "promo" => ($filiere=='Intégré')? $field['annee']-1900 : $field['annee']*1,
                        "filiere" => $filiere,
                        "site" => str_replace(' FC', '', str_replace('ICAM ', '', str_replace($filiere.' ', '', str_replace('Apprentissage ', '', $field['filiere_site'])))),
                        "naissance" => $anniv,
                        "sexe" => $field['isFille']=='VRAI'? 2 : 1,
                        "img_link" => $field['img_link']
                    ];

                    $this->newStudents[$user['mail']] = $user;
                    $this->newStudentsById[$user['id_icam']] = $user;
                }
            }

            fclose($fp);
            unlink($uploadfile);

            $this->curDbStudents = $DB->query("SELECT * FROM users");

            $this->usersPerGroup = [
                'nouveau' => [],
                'update' => [],
                'updateRedoublants' => [],
                'pasMaj' => []
            ];

            foreach ($this->curDbStudents as $userDB) {
                // 'id_icam'
                // 'login'
                // 'nom'
                // 'prenom'
                // 'mail'
                // 'promo'
                // 'filiere'
                // 'site'
                // 'badge_uid'
                // 'expiration_badge'
                // 'naissance'
                // 'sexe'
                // 'img_link'

                $idIcam = substr($userDB['img_link'], -5);
                if (!empty($this->newStudents[$userDB['mail']])) { // MAJ
                    $this->usersPerGroup['update'][] = array_merge($userDB, $this->newStudents[$userDB['mail']]);
                    unset($this->newStudents[$userDB['mail']]);
                } else if (!empty($this->newStudentsById[$idIcam])) { // redoublant
                    $this->usersPerGroup['updateRedoublants'][$userDB['mail']] = array_merge($userDB, $this->newStudentsById[$idIcam]);
                    unset($this->newStudents[$userDB['mail']]);
                } else {
                    $this->usersPerGroup['pasMaj'][] = $userDB;
                }
            }
            $this->usersPerGroup['nouveau'] = $this->newStudents;
            $this->usersPerGroup['problemes'] = [];

            $this->counts = [
                'pasMaj' => count($this->usersPerGroup['pasMaj']),
                'update' => count($this->usersPerGroup['update']),
                'updateRedoublants' => count($this->usersPerGroup['updateRedoublants']),
                'nouveau' => count($this->usersPerGroup['nouveau'])
            ];
            $this->counts['global'] = $this->counts['pasMaj'] + $this->counts['update'] + $this->counts['updateRedoublants'] + $this->counts['nouveau'];
            // echo json_encode($this->counts);
            // echo 'pasMaj: '. count($this->usersPerGroup['pasMaj']).' // ';
            // echo 'update: '. count($this->usersPerGroup['update']).' // ';
            // echo 'updateRedoublants: '. count($this->usersPerGroup['updateRedoublants']).' // ';
            // echo 'nouveau: '. count($this->usersPerGroup['nouveau']).' // ';
            // pasMaj: 296 // update: 793 // updateRedoublants: 18 // nouveau: 282 //

            $fileName = 'uploads/etudiantsUpload'.date("Ymd").'.csv';
            $fp = fopen($fileName, 'w');
            fwrite($fp, "id_icam;login;nom;prenom;mail;promo;filiere;site;badge_uid;expiration_badge;naissance;sexe;img_link\n");

            $updateQuery = "";
            foreach ($this->usersPerGroup['update'] as $user) {
                fwrite($fp, implode(";", array_values($user)) . "\n");
                $d = $user;
                unset($d['login'], $d['badge_uid'], $d['site'], $d['expiration_badge']);
                try {
                    $re = $DB->query("UPDATE users SET
                                    id_icam = :id_icam,
                                    nom = :nom,
                                    prenom = :prenom,
                                    promo = :promo,
                                    filiere = :filiere,
                                    site = 'Lille',
                                    naissance = :naissance,
                                    sexe = :sexe,
                                    img_link = :img_link
                                WHERE login = :mail", $d);
                } catch (\Exception $e) {
                    $this->usersPerGroup['problemes'][$user['login']] = $user;
                    echo '<h4>Erreur avec <small>'.$user['login'].'</small></h4><p>'.$e->getMessage().'</p>';
                }
            }
            foreach ($this->usersPerGroup['updateRedoublants'] as $oldMail => $user) {
                fwrite($fp, implode(";", array_values($user)) . "\n");
                $d = $user;
                unset($d['badge_uid'], $d['expiration_badge']);
                $d['oldMail'] = $oldMail;
                try {
                $DB->query("UPDATE users SET
                                id_icam = :id_icam,
                                nom = :nom,
                                prenom = :prenom,
                                login = :login,
                                mail = :mail,
                                promo = :promo,
                                filiere = :filiere,
                                site = :site,
                                naissance = :naissance,
                                sexe = :sexe,
                                img_link = :img_link
                            WHERE login = :oldMail", $d);
                } catch (\Exception $e) {
                    $this->usersPerGroup['problemes'][$user['login']] = $user;
                    echo '<h4>Erreur avec <small>'.$user['login'].'</small></h4><p>'.$e->getMessage().'</p>';
                }
            }
            foreach ($this->usersPerGroup['nouveau'] as $user) {
                fwrite($fp, implode(";", array_values($user)) . "\n");
                $d = $user;
                unset($d['badge_uid'], $d['expiration_badge']);
                $DB->query("INSERT INTO users (id_icam,nom,prenom,login,mail,promo,filiere,site,naissance,sexe,img_link) VALUES
                        (:id_icam,:nom,:prenom,:login,:mail,:promo,:filiere,:site,:naissance,:sexe,:img_link)", $d);

            }
            foreach ($this->usersPerGroup['pasMaj'] as $user) {
                fwrite($fp, implode(";", array_values($user)) . "\n");
            }

            fclose($fp);
        } else throw new \Exception("fichier inconnu");
    }

}