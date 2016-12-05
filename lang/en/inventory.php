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
 * File : inventory.php
 * Contain the strings displayed on screen
 *
 */
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['createinventory'] = 'Create a new inventory resource';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselectexplain'] = 'Select display type.';
$string['legacyfiles'] = 'Migration of old course file';
$string['legacyfilesactive'] = 'Active';
$string['legacyfilesdone'] = 'Finished';
$string['modulename'] = 'Inventory';
$string['modulename_help'] = 'The inventory module is a blank activity created for development purposes';
$string['modulename_link'] = 'mod/inventory/view';
$string['modulenameplural'] = 'inventories';
$string['optionsheader'] = 'Display options';
$string['page-mod-inventory-x'] = 'Any page module inventory';
$string['inventory:addinstance'] = 'Add a new inventory resource';
$string['inventory:newview'] = 'View inventory content';
$string['pluginadministration'] = 'inventory module administration';
$string['pluginname'] = 'inventory';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printheading'] = 'Display inventory name';
$string['printheadingexplain'] = 'Display inventory name above content?';
$string['printintro'] = 'Display inventory description';
$string['printintroexplain'] = 'Display inventory description above content?';
$string['addfileheader'] = 'Add/modify an element to/of the database'; // Check if still relevant later on.
$string['name'] = 'Name';
$string['city'] = 'City';
$string['department'] = 'Department';
$string['address'] = 'Address';
$string['phone'] = 'Phone Number';
$string['image'] = 'Image';
$string['commentary'] = 'Commentary';
$string['attachment'] = 'Attachment';
$string['type'] = 'Device type';
$string['addbrand'] = 'Add a brand';
$string['editbrand'] = 'Edit the current brand';
$string['deletebrand'] = 'Delete the current brand';
$string['choosebrand'] = 'Choose a brand';
$string['addreference'] = 'Add a reference';
$string['editreference'] = 'Edit the current reference';
$string['deletereference'] = 'Delete the current reference';
$string['choosereference'] = 'Choose a reference';
$string['manuelspecific'] = 'User guide';
$string['manuel'] = 'Manual';
$string['isworking'] = 'Is this working ?';
$string['failurelink'] = 'Link in case of failure';
$string['failuretext'] = 'Text to display to the user if he reports a failure';
$string['oldfield'] = 'Field';
$string['field'] = 'New field';
$string['icon'] = 'Icon';
$string['addfile'] = 'Update database'; // Check if relevant later on.
$string['editdevice'] = 'Edit device'; // Check if relevant later on.
$string['editroom'] = 'Edit room'; // Check if relevant later on.
$string['editbrand'] = 'Edit brand'; // Check if relevant later on.
$string['editbuilding'] = 'Edit building'; // Check if relevant later on.
$string['editcommentary'] = 'Edit commentary'; // Check if relevant later on.
$string['editdevicetype'] = 'Edit device type'; // Check if relevant later on.
$string['editreference'] = 'Edit reference'; // Check if relevant later on.
$string['failureintro'] = 'Find below the information of the device that you will need';
$string['devicetype'] = 'Type of the device';
$string['buildingname'] = 'Name of the building';
$string['roomname'] = 'Name of the room';
$string['reference'] = 'Reference of the device';
$string['iddevice'] = 'ID of the device in the database';
$string['brand'] = 'Brand of the device';
$string['manuelreference'] = 'Reference manual'; // Check if relevant later on.
$string['publicattachment'] = 'Public attachment'; // Check if relevant later on.
$string['privateattachment'] = 'Private attachment'; // Check if relevant later on.
$string['createinventory'] = 'Create an inventory module';
$string['page-mod-inventory-x'] = 'Page'; // Check if relevant later on.
$string['publiccommentary'] = 'PUBLIC commentary';
$string['privatecommentary'] = 'PRIVATE commentary';
$string['nocategory'] = 'All categories';
$string['isamphi'] = 'Is this an amphitheater ?';
$string['configdisplayoptions'] = 'Config display options';
$string['allbuildings'] = 'All buildings';
$string['contact'] = 'Contact';
$string['databaseerror'] = 'Problem with the database';
$string['deleteerror'] = 'Element could not be deleted, you cannot delete the first brand of a category (undefined) or the first reference of a brand (undefined).';
$string['neverdisplayed'] = 'This should only be displayed if you tried to delete an element from the database with the wrong sesskey. This could happen if you reached this page through a malevolent link';
$string['notfound'] = 'NOT FOUND';
$string['addbuilding'] = 'Add a new building';
$string['adddevicetype'] = 'Add a new device type';
$string['addroom'] = 'Add a new room';
$string['add'] = 'Add a ';
$string['reportfailure'] = 'Device does not work';
$string['reportworking'] = 'Device is now working';
$string['reportfailuretitle'] = 'Report a failure';
$string['reportworkingtitle'] = 'Report that the device is working';
$string['managedevicestype'] = 'Manage devices type';
$string['deleterule'] = 'If a field is empty, it will not be added to the device type and will be deleted if it was previously part od the device type';
$string['yes'] = 'Yes';
$string['no'] = 'No';
$string['redirect'] = 'Redirect';
$string['buildingdata'] = 'Data of the building';
$string['roomdata'] = 'Data of the room';
$string['commentarydata'] = 'Data of the commentary';
$string['devicedata'] = 'Data of the device';
$string['referencedata'] = 'Data of the reference';
$string['branddata'] = 'Data of the brand';
$string['devicetypedata'] = 'Data for this type of device';
$string['editerror'] = 'Element could not be edited, you cannot edit the first brand of a category (undefined) or the first reference of a brand (undefined).';
$string['inventory:edit'] = 'Edit the inventory';
$string['inventory:reportfailure'] = 'Report the failure of a device';
$string['deleteelement'] = 'Delete an element from the database';
$string['adddevice'] = 'Add a device';
$string['failure'] = 'Equipment is not working';
$string['csvtitle'] = 'List of devices in rooms';
$string['exportroomsascsv'] = 'Export the devices of selected rooms in a csv file';
$string['confirmdeletebuilding'] = 'Are you sure you wan to delete this building ? This will also delete all of its rooms and the devices of these rooms.';
$string['confirmdeleteroom'] = 'Are you sure you want to delete this room ? This will also delete all of its devices.';
$string['confirmdeletedevice'] = 'Are you sure you want to delete this device ?';
$string['confirmdeletereference'] = 'Are you sure you want to delete this reference ? This will also delete all devices with this reference.';
$string['confirmdeletebrand'] = 'Are you sure you want to delete this brand ? This will also delete all the references of this brand and all the devices of this brand.';
$string['confirmdeletedevicecategory'] = 'Are you REALLY sure you want to delete this category of device ? This will also delete all devices of this category and all brands and references associated with this category.';
$string['confirmdeletefields'] = 'Are you sure you want to delete these fields from the database ? All devices of this category will lose all the information related to these fields.';
$string['navbarfailure'] = 'Report the failure of a device';
$string['navbarfailure'] = 'Report that a device is working';
