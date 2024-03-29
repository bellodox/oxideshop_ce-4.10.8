<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 * @version   OXID eShop CE
 */

if (!class_exists("report_top_clicked_categories")) {
    /**
     * Top clicked categories reports class
     *
     * @deprecated since 5.3.0 (2016.04.07); It will be moved to statistics_module module.
     */
    class Report_top_clicked_categories extends report_base
    {

        /**
         * Name of template to render
         *
         * @return string
         */
        protected $_sThisTemplate = "report_top_clicked_categories.tpl";

        /**
         * Current month top viewed categories report
         *
         * @return null
         */
        public function render()
        {
            $oDb = oxDb::getDb();

            $aDataX = array();
            $aDataY = array();

            $oSmarty = $this->getSmarty();
            $sTimeFrom = $oDb->quote(date("Y-m-d H:i:s", strtotime($oSmarty->_tpl_vars['time_from'])));
            $sTimeTo = $oDb->quote(date("Y-m-d H:i:s", strtotime($oSmarty->_tpl_vars['time_to'])));

            $sSQL = "select count(*) as nrof, oxcategories.oxtitle from oxlogs, oxcategories " .
                "where oxlogs.oxclass = 'alist' and oxlogs.oxcnid = oxcategories.oxid  " .
                "and oxlogs.oxtime >= $sTimeFrom and oxlogs.oxtime <= $sTimeTo " .
                "group by oxcategories.oxtitle order by nrof desc limit 0, 25";
            $rs = $oDb->execute($sSQL);
            if ($rs != false && $rs->recordCount() > 0) {
                while (!$rs->EOF) {
                    if ($rs->fields[1]) {
                        $aDataX[] = $rs->fields[0];
                        $aDataY[] = $rs->fields[1];
                    }
                    $rs->moveNext();
                }
            }
            $iMax = 0;
            for ($iCtr = 0; $iCtr < count($aDataX); $iCtr++) {
                if ($iMax < $aDataX[$iCtr]) {
                    $iMax = $aDataX[$iCtr];
                }
            }

            $aPoints = array();
            $aPoints["0"] = 0;
            $aAligns["0"] = 'report_searchstrings_scale_aligns_left"';
            $sAlignsCenterPart = 'report_searchstrings_scale_aligns_center" width="';
            $sAlignsRightPart = 'report_searchstrings_scale_aligns_right" width="';
            $iTenth = strlen($iMax) - 1;
            if ($iTenth < 1) {
                $iScaleMax = $iMax;
                $aPoints["" . (round(($iMax / 2))) . ""] = $iMax / 2;
                $aAligns["" . (round(($iMax / 2))) . ""] = $sAlignsCenterPart . (720 / 3) . '"';
                $aPoints["" . $iMax . ""] = $iMax;
                $aAligns["" . $iMax . ""] = $sAlignsRightPart . (720 / 3) . '"';
            } else {
                $iDeg = bcpow(10, $iTenth);
                //$iScaleMax = $iDeg * (round($iMax/$iDeg));
                $iScaleMax = $iMax;
                $ctr = 0;
                for ($iCtr = 10; $iCtr > 0; $iCtr--) {
                    $aPoints["" . (round(($ctr))) . ""] = $ctr += $iScaleMax / 10;
                    $aAligns["" . (round(($ctr))) . ""] = $sAlignsCenterPart . (720 / 10) . '"';
                }
                $aAligns["" . (round(($ctr))) . ""] = $sAlignsRightPart . (720 / 10) . '"';
            }

            $aAligns["0"] .= ' width="' . (720 / count($aAligns)) . '"';

            for ($iCtr = 0; $iCtr < count($aDataY); $iCtr++) {
                $aDataVals[$aDataY[$iCtr]] = round($aDataX[$iCtr] / $iMax * 100);
            }

            if (count($aDataY) > 0) {
                $oSmarty->assign("drawStat", true);
            } else {
                $oSmarty->assign("drawStat", false);
            }

            $oSmarty->assign("classes", array($aAligns));
            $oSmarty->assign("allCols", count($aAligns));
            $oSmarty->assign("cols", count($aAligns));
            $oSmarty->assign("percents", array($aDataVals));
            $oSmarty->assign("y", $aDataY);

            return parent::render();
        }

        /**
         * Current week top viewed categories report
         */
        public function graph1()
        {
            $myConfig = $this->getConfig();
            $oDb = oxDb::getDb();

            $aDataX = array();
            $aDataY = array();

            $sTimeFromParameter = oxRegistry::getConfig()->getRequestParameter("time_from");
            $sTimeToParameter = oxRegistry::getConfig()->getRequestParameter("time_to");
            $sTimeFrom = $oDb->quote(date("Y-m-d H:i:s", strtotime($sTimeFromParameter)));
            $sTimeTo = $oDb->quote(date("Y-m-d H:i:s", strtotime($sTimeToParameter)));

            $sSQL = "select count(*) as nrof, oxparameter from oxlogs where oxclass = 'search' " .
                "and oxtime >= $sTimeFrom and oxtime <= $sTimeTo group by oxparameter order by nrof desc";
            $rs = $oDb->execute($sSQL);
            if ($rs != false && $rs->recordCount() > 0) {
                while (!$rs->EOF) {
                    if ($rs->fields[1]) {
                        $aDataX[] = $rs->fields[0];
                        $aDataY[] = $rs->fields[1];
                    }
                    $rs->moveNext();
                }
            }

            header("Content-type: image/png");

            // New graph with a drop shadow
            $graph = new Graph(800, max(640, 20 * count($aDataX)));
            $graph->setBackgroundImage($myConfig->getImageDir(true) . "/reportbgrnd.jpg", BGIMG_FILLFRAME);

            // Use a "text" X-scale
            $graph->setScale("textlin");

            $top = 60;
            $bottom = 30;
            $left = 80;
            $right = 30;
            $graph->set90AndMargin($left, $right, $top, $bottom);

            // Label align for X-axis
            $graph->xaxis->setLabelAlign('right', 'center', 'right');

            // Label align for Y-axis
            $graph->yaxis->setLabelAlign('center', 'bottom');

            $graph->setShadow();
            // Description
            $graph->xaxis->setTickLabels($aDataY);

            // Set title and subtitle
            $graph->title->set("Suchwörter");

            // Use built in font
            $graph->title->setFont(FF_FONT1, FS_BOLD);

            // Create the bar plot
            $bplot = new BarPlot($aDataX);
            $bplot->setFillGradient("navy", "lightsteelblue", GRAD_VER);
            $bplot->setLegend("Hits");

            $graph->add($bplot);

            // Finally output the  image
            $graph->stroke();
        }
    }
}
