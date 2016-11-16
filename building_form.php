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
 * File : building_form.php
 * Define the form to add and edit a building
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once("{$CFG->libdir}/formslib.php");

class building_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('header', 'addfileheader', get_string('buildingdata', 'inventory'));
        $mform->addElement('text', 'name', get_string('name', 'inventory'));
        $mform->addElement('text', 'city', get_string('city', 'inventory'));
        $mform->addElement('text', 'department', get_string('department', 'inventory'));
        $mform->addElement('text', 'address', get_string('address', 'inventory'));
        $mform->addElement('text', 'phone', get_string('phone', 'inventory'));
        $mform->addElement('filemanager', 'image', get_string('image', 'inventory'),
                null, array('maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image')));

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'moduleid');
        $mform->setType('moduleid', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'editmode');
        $mform->setType('editmode', PARAM_INT);

        $this->add_action_buttons();
    }
}



