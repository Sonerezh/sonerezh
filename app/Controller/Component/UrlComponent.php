<?php

App::uses('Component', 'Controller');

class UrlComponent extends Component {

    /**
     * Encode data to base64 without URL unsafe characters
     * Thanks to @bdelespierre (http://bdelespierre.fr/article/bien-plus-quun-simple-jeton/)
     *
     * @param $data
     * @return string
     */
    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode base64url_encode date
     * Thanks to @bdelespierre (http://bdelespierre.fr/article/bien-plus-quun-simple-jeton/)
     *
     * @param $data
     * @return string
     */
    public function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

