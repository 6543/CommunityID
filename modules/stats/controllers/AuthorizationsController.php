<?php

/*
* @copyright Copyright (C) 2005-2009 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Stats_AuthorizationsController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $this->view->weekSelected = '';
        $this->view->yearSelected = '';

        switch ($this->_getParam('type')) {
            case 'year':
                $this->view->yearSelected = 'selected="true"';
                $this->view->type = 'year';
                break;
            default:
                $this->view->weekSelected = 'selected="true"';
                $this->view->type = 'week';
        }

        $this->view->rand = rand(0, 1000);
    }

    public function graphAction()
    {
        require_once 'libs/jpgraph/jpgraph.php';
        require_once 'libs/jpgraph/jpgraph_bar.php';

        $this->_helper->viewRenderer->setNeverRender(true);
        $this->_helper->layout->disableLayout();

        $graph = new Graph(300,200 ,'auto');
        $graph->SetMarginColor('white');
        $graph->SetFrame(false);
        $graph->SetScale("textlin");
        $graph->img->SetMargin(0,30,20,40);
        $graph->yaxis->scale->SetGrace(20);
        $graph->yaxis->HideLabels();
        $graph->yaxis->HideTicks();
        $graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');

        $labelsy = array();
        $datay = array();

        switch ($this->_getParam('type')) {
            case 'year':
                $this->_populateYearData($labelsy, $datay);
                break;
            default:
                $this->_populateWeekData($labelsy, $datay);
        }

        $graph->xaxis->SetTickLabels($labelsy);
        $bplot = new BarPlot($datay);

        $bplot->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
        $bplot->value->Show();
        $bplot->value->SetFormat('%d');
        $graph->Add($bplot);

        $graph->Stroke();
    }

    private function _populateWeekData(&$labelsy, &$datay)
    {
        $stats = new Stats_Model_Stats();
        $authorizations = $stats->getNumAuthorizationsDays(strtotime('-1 week'), time());

        for ($i = -7; $i < 0; $i++) {
            $time = strtotime("$i days");
            $date = date('Y-m-d', $time);
            $labelsy[] = Stats_Model_Stats::$weekDays[date('w', $time)];
            if (isset($authorizations[$date])) {
                $datay[] = $authorizations[$date]['entry'];
            } else {
                $datay[] = 0;
            }
        }
    }

    private function _populateYearData(&$labelsy, &$datay)
    {
        $stats = new Stats_Model_Stats();
        $firstDayOfMonth = date('Y-' . date('m') . '-01');
        $authorizations = $stats->getNumAuthorizationsYear(strtotime('-11 months', strtotime($firstDayOfMonth)), time());

        for ($i = -11; $i <= 0; $i++) {
            $time = strtotime("$i months");
            $monthNumber = date('n', $time);
            $labelsy[] = Stats_Model_Stats::$months[$monthNumber];
            if (isset($authorizations[$monthNumber])) {
                $datay[] = $authorizations[$monthNumber]['entry'];
            } else {
                $datay[] = 0;
            }
        }
    }
}
