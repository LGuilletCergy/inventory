<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    global $DB, $CFG;

include ('../../config.php');
require_once($CFG->libdir.'/csvlib.class.php');

require_login();

if (is_siteadmin()) {

    $csvexporter = new csv_export_writer();

    $csvexporter->set_filename('inventorystats');

    $title = array(utf8_decode("Statistiques de l'inventaire"));
    $csvexporter->add_data($title);

    $firstline = array();
    $firstline[] = utf8_decode("Marque");
    $firstline[] = utf8_decode("Référence");
    $firstline[] = utf8_decode("Nombre");
    $csvexporter->add_data($firstline);

    $listbrands = $DB->get_records('inventory_brand', array('categoryid' => 1));

    foreach ($listbrands as $brand) {

        $listreferences = $DB->get_records('inventory_reference', array('brandid' => $brand->id));

        foreach ($listreferences as $reference) {

            $nbdevice = $DB->count_records('inventory_device', array('refid' => $reference->id));

            $newline = array();
            $newline[] = $brand->name;
            $newline[] = $reference->name;
            $newline[] = $nbdevice;
            $csvexporter->add_data($newline);
        }
    }

    $csvexporter->download_file();
}