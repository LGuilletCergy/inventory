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

        if($row != 1) {

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

                //Est-ce un amphi ?

                if (strstr(utf8_encode($data[4]), "AMPHI", true)) {

                    $roomtoinsert['isamphi'] = 1;
                } else {

                    $roomtoinsert['isamphi'] = 0;
                }

                $roomid = $DB->insert_record('inventory_room', $roomtoinsert);
            } else {

                $roomid = $DB->get_record('inventory_room', array('name' => utf8_encode($data[5])))->id;
            }
        }

        $row++;
   }
}
