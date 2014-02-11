<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Paypal\Loop;

use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Translation\Translator;

/**
 * Class CheckRightsLoop
 * @package Paypal\Looop
 * @author Thelia <info@thelia.net>
 */

class CheckRightsLoop extends BaseLoop implements ArraySearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildArray()
    {
        $ret = array();
        $dirs = array("Config", "logs");
        while($dir=array_pop($dirs)) {
            $dir_path = __DIR__."/../".$dir."/";
            if(!is_dir($dir_path) && !is_writable(__DIR__."/../")) {
                $ret[] = array("ERRMES"=>Translator::getInstance()->trans("This directory doesn't exists and Paypal plugin directory isn't writable: "), "ERRFILE"=>$dir);
            }
            elseif (is_dir($dir_path) && !is_readable($dir_path)) {
                $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't read directory "), "ERRFILE"=>$dir);
            }
            elseif (is_dir($dir_path) && !is_writable($dir_path)) {
                $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't write directory "), "ERRFILE"=>$dir);
            }
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 5 && substr($file, -5) === ".json") {
                    if (!is_readable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't read file"), "ERRFILE"=>"Icirelais/Config/".$file);
                    }
                    if (!is_writable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans("Can't write file"), "ERRFILE"=>"Icirelais/Config/".$file);
                    }
                }
            }
        }

        return $ret;
    }
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $arr) {
            $loopResultRow = new LoopResultRow();
            $loopResultRow->set("ERRMES", $arr["ERRMES"])
                ->set("ERRFILE", $arr["ERRFILE"]);
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
