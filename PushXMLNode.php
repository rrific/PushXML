<?php

namespace PushXML;

/**
 * This is a holder class for XML nodes. Not much to see here.
 **/
class PushXMLNode
{
    public $__parent;
    public $__pivot;
    public $__text;

    public function __construct(array $arr)
    {
        foreach ($arr as $k => $v) {
            $this->$k = $v;
        }
    }
}
