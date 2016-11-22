<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 *
 * The inventory module is used to list the devices available in a room
 *
 * @package    mod_inventory
 * @author     Laurent Guillet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * File : initBDD.php
 * File used to initialise the database.
 *
 */

define('CLI_SCRIPT', true);
require_once(__DIR__.'/config.php');
require_once($CFG->libdir.'/clilib.php');

$fichiercsv = fopen('videoprojecteurs.csv', 'r');

$row = 1;
if ($fichiercsv == FALSE) {
    echo "Impossible d'ouvrir le fichier CSV\n";
} else {

    while (($data = fgetcsv($fichiercsv, 1000, ";")) !== FALSE) {

        //print_object(utf8_encode($data[4]));

        if($row != 1 && utf8_encode($data[0]) != "") {

            if (!$DB->record_exists('inventory_building', array('name' => utf8_encode($data[4]))) && utf8_encode($data[4]) != "") {

                $buildingtoinsert['name'] = utf8_encode($data[4]);
                $buildingtoinsert['city'] = utf8_encode($data[3]);
                $buildingtoinsert['department'] = utf8_encode($data[2]);
                $buildingtoinsert['address'] = "";
                $buildingtoinsert['phone'] = "";

                $buildingid = $DB->insert_record('inventory_building', $buildingtoinsert);
            } else {

                $buildingid = $DB->get_record('inventory_building', array('name' => utf8_encode($data[4])))->id;
            }

            if (!$DB->record_exists('inventory_room', array('buildingid' => $buildingid, 'name' => utf8_encode($data[5]))) && utf8_encode($data[5]) != "") {

                $roomtoinsert['buildingid'] = $buildingid;
                $roomtoinsert['name'] = utf8_encode($data[5]);
                $roomtoinsert['isamphi'] = 0;

                $roomid = $DB->insert_record('inventory_room', $roomtoinsert);
            } else {

                $roomid = $DB->get_record('inventory_room', array('name' => utf8_encode($data[5])))->id;
            }

            if(utf8_encode($data[6]) != "" && utf8_encode($data[6]) != "N") {

                if (!$DB->record_exists('inventory_brand', array('categoryid' => 1, 'name' => utf8_encode($data[6])))) {

                    $brandtoinsert['name'] = utf8_encode($data[6]);
                    $brandtoinsert['categoryid'] = 1;

                    $brandid = $DB->insert_record('inventory_brand', $brandtoinsert);
                } else {

                    $brandid = $DB->get_record('inventory_brand', array('categoryid' => 1, 'name' => utf8_encode($data[6])))->id;
                }
            } else {

                $brandid = 1;
            }

            if(utf8_encode($data[7]) != "" && utf8_encode($data[7]) != "INCONNU") {

                if (!$DB->record_exists('inventory_reference', array('brandid' => $brandid, 'name' => utf8_encode($data[7])))) {

                    $referencetoinsert['name'] = utf8_encode($data[7]);
                    $referencetoinsert['brandid'] = $brandid;

                    $referenceid = $DB->insert_record('inventory_reference', $referencetoinsert);
                } else {

                    $referenceid = $DB->get_record('inventory_reference', array('brandid' => $brandid, 'name' => utf8_encode($data[7])))->id;
                }
            } else {

                $listreference = $DB->get_records('inventory_reference', array('brandid' => $brandid));

                foreach ($listreference as $firstreference) {

                    $referenceid = $firstreference->id;
                    break;
                }
            }

            if(!$DB->record_exists('inventory_device', array('categoryid' => 1, 'roomid' => $roomid))) {

                $videoprojecteurtoinsert['roomid'] = $roomid;
                $videoprojecteurtoinsert['categoryid'] = 1;
                $videoprojecteurtoinsert['refid'] = $referenceid;
                $videoprojecteurtoinsert['isworking'] = 1;

                $videoprojecteurid = $DB->insert_record('inventory_device', $videoprojecteurtoinsert);

                $valuetoinsert['fieldid'] = 1;
                $valuetoinsert['deviceid'] = $videoprojecteurid;
                $valuetoinsert['value'] = utf8_encode($data[9]);

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);

                $valuetoinsert['fieldid'] = 2;
                $valuetoinsert['deviceid'] = $videoprojecteurid;
                $valuetoinsert['value'] = utf8_encode($data[10]);

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);

                $valuetoinsert['fieldid'] = 3;
                $valuetoinsert['deviceid'] = $videoprojecteurid;
                $valuetoinsert['value'] = utf8_encode($data[12]);

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);

                $valuetoinsert['fieldid'] = 4;
                $valuetoinsert['deviceid'] = $videoprojecteurid;
                $valuetoinsert['value'] = utf8_encode($data[13]);

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);

                $valuetoinsert['fieldid'] = 5;
                $valuetoinsert['deviceid'] = $videoprojecteurid;
                $valuetoinsert['value'] = utf8_encode($data[21]);

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);

                $valuetoinsert['fieldid'] = 9;
                $valuetoinsert['deviceid'] = $videoprojecteurid;

                $finalvalue = utf8_encode($data[27])." ".utf8_encode($data[28])." ".utf8_encode($data[29])." ".utf8_encode($data[30]);

                $valuetoinsert['value'] = $finalvalue;

                $DB->insert_record('inventory_devicevalue', $valuetoinsert);
            }

            if(!$DB->record_exists('inventory_device', array('categoryid' => 2, 'roomid' => $roomid))) {

                if (utf8_encode($data[15]) == "OUI" || utf8_encode($data[15]) == "VGA") {


                    $vgatoinsert['roomid'] = $roomid;
                    $vgatoinsert['categoryid'] = 2;
                    $vgatoinsert['refid'] = 1;

                    if (utf8_encode($data[16]) == "OK") {

                        $vgatoinsert['isworking'] = 1;
                    } else {

                        $vgatoinsert['isworking'] = 0;
                    }

                    $vgaid = $DB->insert_record('inventory_device', $vgatoinsert);

                    $valuetoinsert['fieldid'] = 10;
                    $valuetoinsert['deviceid'] = $vgaid;
                    $valuetoinsert['value'] = utf8_encode($data[25]);

                    $DB->insert_record('inventory_devicevalue', $valuetoinsert);
                }
            }

            if(!$DB->record_exists('inventory_device', array('categoryid' => 3, 'roomid' => $roomid))) {

                if (utf8_encode($data[18]) == "OUI") {


                    $vgatoinsert['roomid'] = $roomid;
                    $vgatoinsert['categoryid'] = 3;
                    $vgatoinsert['refid'] = 1;

                    if (utf8_encode($data[19]) == "OK") {

                        $vgatoinsert['isworking'] = 1;
                    } else {

                        $vgatoinsert['isworking'] = 0;
                    }

                    $vgaid = $DB->insert_record('inventory_device', $vgatoinsert);

                    $valuetoinsert['fieldid'] = 10;
                    $valuetoinsert['deviceid'] = $vgaid;
                    $valuetoinsert['value'] = utf8_encode($data[26]);

                    $DB->insert_record('inventory_devicevalue', $valuetoinsert);
                }
            }
        }
        
        $row++;
   }
}
