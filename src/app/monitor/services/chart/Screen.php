<?php
class Service_Chart_Screen
{
    private $api = "http://127.0.0.1:3333";

    public function get($config) {
        $results = null;

        $globalOptions = '{ "global": { "useUTC": false }, "lang": { "numericSymbols": null }, "credits": { "enabled": false }, "series": { "showInLegend": false}}';

        $data = json_encode(array(
            "infile"        => $config,
            "globaloptions" => $globalOptions,
        ));

        if ( ! empty($data) ) {
            $header = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            );

            $results = Sys_Tools::curl($this->api, "POST", $data, $header);

            return $results;
        }

        return $results;
    }

}