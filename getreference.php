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
 * File : getreference.php
 * Internal page used to fill the list of references in editdevice.php
 *
 */

require_once('../../config.php');

// Echo the list of options for the newly selected brand.

if (isset($_REQUEST["brandid"])) {

    // The list of references is set depending on the references available for the current brand.

    $initialtablereferences = $DB->get_records_sql('SELECT id, name FROM {inventory_reference} '
            . 'WHERE brandid=:brandid ORDER BY name', array('brandid' => $_REQUEST["brandid"]));

    $unorderedtablereferences = $DB->get_records_sql('SELECT id, name FROM {inventory_reference} '
            . 'WHERE brandid=:brandid', array('brandid' => $_REQUEST["brandid"]));

    // To order the references by name, we need to use a sql statement.
    // However, we still want undefined to be the first element of the list.

    $tablereferences = array();

    foreach ($unorderedtablereferences as $temptablereference) {

        $tablereferences[$temptablereference->id] = $temptablereference->name;

        $firstreferenceid = $temptablereference->id;

        break;
    }

    foreach ($initialtablereferences as $temptablereference) {

        if ($temptablereference->id != $firstreferenceid) {

            $tablereferences[$temptablereference->id] = $temptablereference->name;
        }
    }

    foreach ($tablereferences as $referencekey => $referencevalue) {

        echo "<option value=$referencekey>$referencevalue</option>";
    }

} else {
    echo "<option value='-1'>".get_string('notfound', 'inventory')."</option>";
}
