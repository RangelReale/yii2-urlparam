<?php

namespace RangelReale\urlparam;

use yii\base\BaseObject;
use yii\base\NotSupportedException;

class UrlParam extends BaseObject
{
    private $_params = [];
    
    public function addParam($name, $actions = [], $value = null)
    {
        $this->_params[$name] = [
            'value' => $value,
            'actions' => $actions,
        ];
    }
    
    public function setValue($name, $value = null)
    {
        if (!is_array($name))
            $name = [$name => $value];
        
        foreach ($name as $n => $v) {
            if (!isset($this->_params[$n]))
                throw new NotSupportedException('Parameter '.$n.' not found');

            $this->_params[$n]['value'] = $v;
        }
        return $this;
    }
    
    public function url($action, $extraParams = [])
    {
        $ret = [$action];
        foreach ($this->_params as $pname => $pvalue) {
            $include = true;
            if (is_null($pvalue['value']))
                $include = false;
            
            if ($include && count($pvalue['actions']) > 0) {
                $include = false;
                foreach ($pvalue['actions'] as $paramAction) {
                    $is_not = false;
                    if (substr($paramAction, 0, 1) == '!') {
                        $paramAction = substr($paramAction, 1);
                        $is_not = true;
                    }
                    if ($paramAction == '@') {
                        $include = !$is_not;
                    }
                    if ($action == $paramAction) {
                        $include = !$is_not;
                    }
                }
            }
            
            if ($include) {
                $ret[$pname] = (string)$pvalue['value'];
            }
        }
        foreach ($extraParams as $pname => $pvalue) {
            if (is_null($pvalue)) {
                if (isset($ret[$pname]))
                    unset($ret[$pname]);
            } else {
                $ret[$pname] = (string)$pvalue;
            }
        }
        return $ret;
    }
}