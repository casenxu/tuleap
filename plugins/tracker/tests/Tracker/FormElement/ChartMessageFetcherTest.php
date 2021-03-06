<?php
/**
 *  Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Tracker\FormElement;

use EventManager;

require_once TRACKER_BASE_DIR . '/../tests/bootstrap.php';

class ChartMessageFetcherTest extends \TuleapTestCase
{
    /**
     * @var \Tracker
     */
    private $tracker;

    /**
     * @var ChartMessageFetcher
     */
    private $message_fetcher;

    public function setUp()
    {
        parent::setUp();

        $hierarchy_factory     = mock('Tracker_HierarchyFactory');
        $this->message_fetcher = new ChartMessageFetcher(
            $hierarchy_factory,
            mock('Tuleap\Tracker\FormElement\ChartConfigurationFieldRetriever')
        );
        stub($hierarchy_factory)->getChildren()->returns(array());

        $this->tracker = aMockTracker()->build();
    }

    public function itDisplaysWarningsWhenFieldsAreMissingInChartConfiguration()
    {
        $chart_configuration = new ChartFieldUsage(true, true, false, false, false, false);
        stub($this->tracker)->hasFormElementWithNameAndType('start_date', 'date')->returns(false);
        stub($this->tracker)->hasFormElementWithNameAndType('duration', 'int')->returns(false);

        $expected_warning = '<ul class="feedback_warning"><li>' .
            $GLOBALS['Language']->getText('plugin_tracker', 'burndown_missing_start_date_warning') .
            '</li><li>' .
            $GLOBALS['Language']->getText('plugin_tracker', 'burndown_missing_duration_warning') .
            '</li></ul>';

        $this->assertEqual(
            $expected_warning,
            $this->message_fetcher->fetchWarnings($this->tracker, $chart_configuration)
        );
    }

    public function itDoesnotDisplayAnyErrorsWhenNoFieldsAreMissingInChartConfiguration()
    {
        $chart_configuration = new ChartFieldUsage(true, true, false, false, false, false);

        stub($this->tracker)->hasFormElementWithNameAndType('start_date', 'date')->returns(true);
        stub($this->tracker)->hasFormElementWithNameAndType('duration', 'int')->returns(true);

        $expected_warning = '';

        $this->assertEqual(
            $expected_warning,
            $this->message_fetcher->fetchWarnings($this->tracker, $chart_configuration)
        );
    }
}
