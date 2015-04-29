<?php

namespace PushXML;

    /**
     * PushXML 0.7.0 - A SimpleXML lookalike, which allows for push parsing.
     *
     * History :
     *
     * 0.7.0 (2007/09/17) : trims spaces in text once the element is built, not at
     *                      each text fragment.
     * 0.6.0 (2007/06/14) : handles attributes defined multiple times.
     * 0.5.0 (2007/05/10) : initial release.
     *
     * Author : Nicolas LEHUEN, CRM Company Group <nlehuen@crmcompanygroup.com>
     * Copyright 2007, CRM Company Group, and individual contributors as
     * indicated above.
     *
     * This is free software; you can redistribute it and/or modify it
     * under the terms of the GNU Lesser General Public License as
     * published by the Free Software Foundation; either version 2.1 of
     * the License, or (at your option) any later version.
     *
     * This software is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
     * Lesser General Public License for more details.
     *
     * You should have received a copy of the GNU Lesser General Public
     * License along with this software; if not, write to the Free
     * Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA
     * 02110-1301 USA, or see the FSF site: http://www.fsf.org.
     */

/**
 * PushXML implements an XML Push Parser implemented on top of
 * the PHP XML extension (which in turn uses expat).
 * It builds an object hierarchy similar to the one produced by SimpleXML.
 * The main entry point are PushXML::parse_file (static method) or PushXML::parse
 * (instance method). See the example at the end of the source file for more info.
 **/
class PushXML
{
    /**
     * Parses a file.
     * @param $file
     * @param $pivot
     * @param $callback
     * @param $endCallback
     */
    public static function parse_file($file, $pivot, $callback, $endCallback)
    {
        $handler = new PushXML($pivot, $callback, $endCallback);
        $handler->file = $file;

        if (!($fp = fopen($file, "r"))) {
            die("Cannot open $file");
        }

        while ($data = fread($fp, 65536)) {
            if ($handler->finished) {
                fclose($fp);

                return;
            }
            $handler->parse($data, feof($fp));
        }
        fclose($fp);
    }

    private $pivot;
    private $callback;
    private $endCallback;

    private $parser;
    private $file;
    /**
     * @var PushXMLNode
     */
    private $current;

    public $finished;

    /**
     * Builds a parser.
     * @param $pivot
     * @param $callback
     * @param $endCallback
     * @internal param \Salomon\Prologue\ImportBundle\Extension\The $pivot pivot specification. Each time a pivot element has been read,
     *                 the callback is called.
     * @internal param \Salomon\Prologue\ImportBundle\Extension\The $callback callback function to call (or array($instance,$method_name))
     * whenever a pivot element has been read. The two arguments of the
     * callback are the root node and the selected node.
     */
    public function __construct($pivot, $callback, $endCallback)
    {
        if (strncmp('/', $pivot, 1) == 0) {
            $pivot = substr($pivot, 1);
        }
        $this->pivot = explode('/', $pivot);
        $this->callback = $callback;
        $this->endCallback = $endCallback;

        $this->root = null;
        $this->current = null;
        $this->file = '(unknown file)';

        $this->finished = false;

        // Builds and initialize the XML parser.
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($this->parser, array($this, "start_element"), array($this, "end_element"));
        xml_set_character_data_handler($this->parser, array($this, "character_data"));
    }

    public function __destruct()
    {
        xml_parser_free($this->parser);
        $this->parser = null;
    }

    /**
     * Parses a chunk of data.
     * @param $data
     * @param TRUE $final
     */
    public function parse($data, $final)
    {
        if (!xml_parse($this->parser, $data, $final)) {
            die(sprintf(
              "XML error : %s while parsing %d",
              xml_error_string(xml_get_error_code($this->parser)),
              xml_get_current_line_number($this->parser)
            ));
        }
    }

    /**
     * Handles a opening tag.
     **/
    public function start_element($parser, $name, $attrs)
    {
        $element = new PushXMLNode($attrs);

        if ($this->root == null) {
            // This is the root element
            $this->root =& $element;
            $this->current =& $element;

            // Check if the root element has the propername
            $challenge = $this->pivot[0];
            if ($challenge == '' || $challenge == $name) {
                $this->current->__pivot = 1;
            } else {
                $this->current->__pivot = 0;
            }
        } else {
            // The parent of the new element is the current element
            $element->__parent =& $this->current;

            // Let's compute our pivot value
            $pivot = $this->current->__pivot;
            if ($pivot > 0) {
                if ($pivot < count($this->pivot)) {
                    $challenge = $this->pivot[$pivot];
                    if ($challenge == '' || $challenge == $name) {
                        $pivot++;
                    } else {
                        $pivot = 0;
                    }
                } else {
                    $pivot++;
                }
            }
            $element->__pivot = $pivot;

            // We now check to see whether we already have a child with the same name
            $old_value =& $this->current->$name;
            if ($old_value && $pivot > count($this->pivot)) {
                if (is_array($old_value)) {
                    // We only accumulate elements if we've passed the pivot
                    array_push($old_value, $element);
                } else {
                    // We are overwriting a simple attribute
                    $file = $this->file;
                    $line_number = xml_get_current_line_number($this->parser);
                    trigger_error("overwriting attribute '$name' at line $line_number in $file", E_USER_WARNING);
                    $this->current->$name = $element;
                }
            } else {
                if ($pivot > 0) {
                    // We only store elements if we are going
                    // toward the pivot
                    $this->current->$name = array($element);
                }
            }

            // We push the new element on the stack.
            $this->current =& $element;
        }
    }

    /**
     * Handles a closing tag.
     **/
    public function end_element($parser, $name)
    {
        // We save and destroy the special attributes
        // They could be kept but they are quite disgracious
        // and they provoque recursion when dumping the tree
        // with print_r(), for example.
        $parent =& $this->current->__parent;
        unset($this->current->__parent);
        $pivot = $this->current->__pivot;
        unset($this->current->__pivot);

        // Let's see if we can simplify the tree
        // by disposing of redundant arrays.
        $count = count((array) $this->current);
        if ($count == 0) {
            // The element is empty, we set it
            // as a null value in the parent.
            $value =& $parent->$name;
            $count = count($value);
            if ($count > 1) {
                array_pop($value);
            } else {
                $value = null;
            }
        } else {
            if ($count == 1) {
                // We want to trim whitespaces
                $text = trim($this->current->__text);
                // The element contains a single text node
                // We will set it as an attribute in the parent
                // element.
                $value =& $parent->$name;
                $count = count($value);
                if ($count > 1) {
                    array_pop($value);
                    array_push($value, $text);
                } else {
                    $value = $text;
                }
            }
        }

        // If we are closing the pivot element, it's time
        // to use the callback.
        if ($pivot == count($this->pivot)) {
            unset($this->current->__text);
            call_user_func($this->callback, $this->current);
        }

        if ($name == $this->pivot[count($this->pivot) - 2]) {
            call_user_func($this->endCallback);
            $this->finished = true;
        }

        // We pop the stack
        $this->current =& $parent;
    }

    /**
     * Handles text.
     **/
    public function character_data($parser, $data)
    {
        if ($data !== null) {
            if (isset($this->current->__text)) {
                // If we already have a text node,
                // we simply append the character data.
                $this->current->__text .= $data;
            } else {
                $this->current->__text = $data;
            }
        }
    }
}
