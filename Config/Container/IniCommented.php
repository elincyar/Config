<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                      |
// +----------------------------------------------------------------------+
//
// $Id$

require_once('Config.php');

/**
* Config parser for PHP .ini files with comments
*
* @author      Bertrand Mansion <bmansion@mamasam.com>
* @package     Config
*/
class Config_Container_IniCommented {

    /**
    * Parses the data of the given configuration file
    *
    * @access public
    * @param string $datasrc    path to the configuration file
    * @return mixed returns a PEAR_ERROR, if error occurs or true if ok
    */
    function &parseDatasrc($datasrc)
    {
        if (!file_exists($datasrc)) {
            return PEAR::raiseError("Datasource file does not exist.", null, PEAR_ERROR_RETURN);
        }
        $lines = file($datasrc);
        $n = 0;
        $lastline = '';
        $currentSection =& $this->container;
        foreach ($lines as $line) {
            $n++;
            if (preg_match('/^\s*;(.*?)\s*$/', $line, $match)) {
                // a comment
                $currentSection->createComment($match[1]);
            } elseif (preg_match('/^\s*$/', $line)) {
                // a blank line
                $currentSection->createBlank();
            } elseif (preg_match('/^([a-zA-Z1-9_\-\.]*)\s*=(\s*(.*))$/', $line, $match)) {
                // a directive
                $currentSection->createDirective($match[1], $match[3]);
            } elseif (preg_match('/^\s*\[\s*(.*)\s*\]\s*$/', $line, $match)) {
                // a section
                $currentSection =& $this->container->createSection($match[1]);
            } else {
                return PEAR::raiseError("Syntax error in '$datasrc' at line $n.", null, PEAR_ERROR_RETURN);
            }
        }
        return true;
    } // end func parseDatasrc

    /**
    * Returns a formatted string of the object
    * @access public
    * @return string
    */
    function toString($configType = 'inicommented')
    {
        if (!isset($string)) {
            $string = '';
        }
        switch ($this->type) {
            case 'blank':
                $string = "\n";
                break;
            case 'comment':
                $string = ';'.$this->content."\n";
                break;
            case 'directive':
                $string = $this->name.' = '.$this->content."\n";
                break;
            case 'section':
                if (!$this->isRoot()) {
                    $string = '['.$this->name."]\n";
                }
                if (count($this->children) > 0) {
                    for ($i = 0; $i < count($this->children); $i++) {
                        $string .= $this->children[$i]->toString($configType);
                    }
                }
                break;
            default:
                $string = '';
        }
        return $string;
    } // end func toString
} // end class Config_Container_IniCommented
?>