<?php

App::uses('FormHelper', 'View/Helper');

class BootstrapFormHelper extends FormHelper{

    public function create($model = null, $options = array()) {

        $defaultOptions = array(
            'inputDefaults' => array(
                'div'   => array('class' => 'form-group'),
                'class' => 'form-control',
                'error' => array('attributes' => array('wrap' => 'p', 'class' => 'text-danger'))
            )
        );

        $options = array_merge($defaultOptions, $options);
        return parent::create($model, $options);
    }

    public function input($fieldName, $options = array()) {

        $this->setEntity($fieldName);
        $defaultOptions = $this->_parseOptions($options);

        if ($defaultOptions['type'] == 'checkbox') {
            $newDefaultOptions = array(
                'div'   => array('class' => 'checkbox'),
                'class' => false,
                'type'  => 'checkbox'
            );

            if (isset($defaultOptions["disabled"]) && $defaultOptions['disabled'] == 'disabled') {
                $newDefaultOptions['div']['class'] .= ' disabled';
            }
            $options = array_merge($newDefaultOptions, $options);
        }

        if ($defaultOptions['type'] == 'radio') {
            $defaultOptions = array(
                'div'       => array('class' => 'radio'),
                'separator' => '</div><div class="radio">',
                'class'     => false,
                'legend'    => false
            );
            $options = array_merge($defaultOptions, $options);
        }
        return parent::input($fieldName, $options);
    }


    protected function _confirm($message, $okCode, $cancelCode = '', $options = array()) {
        $ok = explode('.', $okCode);
        $okCode = "$('#".$ok[1]."').submit();";
        return parent::_confirm($message, $okCode, $cancelCode, $options);
    }

}
