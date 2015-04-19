<?php
/**
 * Created by PhpStorm.
 * User: Samuel
 * Date: 4/19/2015
 * Time: 10:05 PM
 */

namespace LWM;


class Request {
    private $url;

    public function __construct($url) {
        $this->url = $url;
    }

    public function get() {
        return $this->_send();
    }

    /**
     * @return \pQuery\DomNode
     */
    public function getDom() {
        $resp = $this->_send();

        $dom = \pQuery::parseStr($resp);
        return $dom;
    }

    private function _send() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->url
        ));

        $resp = curl_exec($curl);
        curl_close($curl);

        return $resp;
    }
}