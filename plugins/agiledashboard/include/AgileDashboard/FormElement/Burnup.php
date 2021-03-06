<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\AgileDashboard\FormElement;

use Codendi_HTMLPurifier;
use PFUser;
use SystemEventManager;
use Tracker_Artifact;
use Tracker_Artifact_Changeset;
use Tracker_Artifact_ChangesetValue;
use Tracker_FormElement_Chart_Field_Exception;
use Tracker_FormElement_Field;
use Tracker_FormElement_Field_ReadOnly;
use Tracker_FormElement_FieldVisitor;
use Tracker_HierarchyFactory;
use Tuleap\Tracker\FormElement\ChartCachedDaysComparator;
use Tuleap\Tracker\FormElement\ChartConfigurationFieldRetriever;
use Tuleap\Tracker\FormElement\ChartConfigurationValueChecker;
use Tuleap\Tracker\FormElement\ChartConfigurationValueRetriever;
use Tuleap\Tracker\FormElement\ChartFieldUsage;
use Tuleap\Tracker\FormElement\ChartMessageFetcher;
use Tuleap\Tracker\FormElement\FieldCalculator;
use Tuleap\Tracker\FormElement\TrackerFormElementExternalField;
use UserManager;

class Burnup extends Tracker_FormElement_Field implements Tracker_FormElement_Field_ReadOnly, TrackerFormElementExternalField
{
    public function accept(Tracker_FormElement_FieldVisitor $visitor)
    {
        return $visitor->visitExternalField($this);
    }

    public function getFormAdminVisitor(Tracker_FormElement_Field $element, array $used_element)
    {
        return new ViewAdminBurnupField($element, $used_element);
    }

    public function afterCreate($formElement_data = array())
    {
    }

    public function canBeUsedAsReportCriterion()
    {
        return false;
    }

    public function canBeUsedToSortReport()
    {
        return false;
    }

    /**
     * @return string html
     */
    public function fetchAdminFormElement()
    {
        $field_usage = $this->getChartFieldUsage();

        $html = $this->getChartMessageFetcher()->fetchWarnings($this->getTracker(), $field_usage);
        $html .= '<img src="' . AGILEDASHBOARD_BASE_URL . '/images/fake-burnup-admin.png" />';

        return $html;
    }

    private function getChartMessageFetcher()
    {
        return new ChartMessageFetcher(
            Tracker_HierarchyFactory::instance(),
            $this->getConfigurationFieldRetriever()
        );
    }

    private function getConfigurationFieldRetriever()
    {
        return new ChartConfigurationFieldRetriever($this->getFormElementFactory(), $this->getLogger());
    }

    public function fetchArtifactForOverlay(Tracker_Artifact $artifact, $submitted_values = array())
    {
    }

    public function fetchArtifactValue(
        Tracker_Artifact $artifact,
        Tracker_Artifact_ChangesetValue $value = null,
        $submitted_values = array()
    ) {
    }

    public function fetchArtifactValueReadOnly(
        Tracker_Artifact $artifact,
        Tracker_Artifact_ChangesetValue $value = null
    ) {
        try {
            $user        = UserManager::instance()->getCurrentUser();
            $burnup_data = $this->getBurnupDataBuilder()->buildBurnupData($artifact, $user);

            if ($burnup_data->isBeingCalculated()) {
                return "<div class='feedback_warning'>" .
                    dgettext(
                        'tuleap-agiledashboard',
                        "Burnup is under calculation. It will be available in few minutes."
                    ) .
                    "</div>";
            }

            $purifier = Codendi_HTMLPurifier::instance();

            $artifact_value = "<br/><div class='feedback_warning'>" .
                dgettext('tuleap-agiledashboard', "Field under implementation") .
                "</div>";

            $artifact_value .= '<table class="tracker_report_table table tlp-table">';
            $artifact_value .= '<thead><tr><th></th><th>Team</th><th>Total</th></tr></thead><tbody>';
            foreach ($burnup_data->getEfforts() as $timestamp => $burnup_effort) {
                $artifact_value .= '<tr>';
                $date = new \DateTime("@$timestamp");
                $artifact_value .= '<td>' . $purifier->purify($date->format('d-m-Y')) . '</td>';
                $artifact_value .= '<td>' . $purifier->purify($burnup_effort->getTeamEffort()) . '</td>';
                $artifact_value .= '<td>' . $purifier->purify($burnup_effort->getTotalEffort()) . '</td>';
                $artifact_value .= '</tr>';
            }
            $artifact_value .= '</tbody></table>';

            return $artifact_value;
        } catch (Tracker_FormElement_Chart_Field_Exception $e) {
            return "<div class='feedback_warning'>" .
                $e->getMessage() .
                "</div>";
        }
    }

    public function fetchCSVChangesetValue($artifact_id, $changeset_id, $value, $report)
    {
    }

    public function fetchChangesetValue($artifact_id, $changeset_id, $value, $report = null, $from_aid = null)
    {
    }

    public function fetchCriteriaValue($criteria)
    {
    }

    public function fetchFollowUp($artifact, $from, $to)
    {
    }

    public function fetchMailArtifactValue(
        Tracker_Artifact $artifact,
        PFUser $user,
        $ignore_perms,
        Tracker_Artifact_ChangesetValue $value = null,
        $format = 'text'
    ) {
    }

    public function fetchRawValue($value)
    {
    }

    public function fetchRawValueFromChangeset($changeset)
    {
    }

    public function fetchSubmit($submitted_values = array())
    {
        return '';
    }

    public function fetchSubmitMasschange()
    {
    }

    protected function fetchSubmitValue()
    {
    }

    protected function fetchSubmitValueMasschange()
    {
    }

    protected function fetchTooltipValue(Tracker_Artifact $artifact, Tracker_Artifact_ChangesetValue $value = null)
    {
    }

    public function getChangesetValue($changeset, $value_id, $has_changed)
    {
    }

    protected function getCriteriaDao()
    {
    }

    public function getCriteriaFrom($criteria)
    {
    }

    public function getCriteriaWhere($criteria)
    {
    }

    protected function getDao()
    {
    }

    public static function getFactoryDescription()
    {
        return dgettext('tuleap-tracker', 'Display the burnup chart for the artifact');
    }

    public static function getFactoryIconCreate()
    {
        return $GLOBALS['HTML']->getImagePath('ic/burndown--plus.png');
    }

    public static function getFactoryIconUseIt()
    {
        return $GLOBALS['HTML']->getImagePath('ic/burndown.png');
    }

    public static function getFactoryLabel()
    {
        return dgettext('tuleap-agiledashboard', 'Burnup Chart');
    }

    public function getQueryFrom()
    {
    }

    public function getQuerySelect()
    {
    }

    public function getRESTValue(PFUser $user, Tracker_Artifact_Changeset $changeset)
    {
    }

    public function getSoapAvailableValues()
    {
    }

    protected function getValueDao()
    {
    }

    protected function keepValue(
        $artifact,
        $changeset_value_id,
        Tracker_Artifact_ChangesetValue $previous_changesetvalue
    ) {
    }

    /**
     * @see Tracker_FormElement_Field::postSaveNewChangeset()
     */
    public function postSaveNewChangeset(
        Tracker_Artifact $artifact,
        PFUser $submitter,
        Tracker_Artifact_Changeset $new_changeset,
        Tracker_Artifact_Changeset $previous_changeset = null
    ) {
    }

    protected function saveValue(
        $artifact,
        $changeset_value_id,
        $value,
        Tracker_Artifact_ChangesetValue $previous_changesetvalue = null
    ) {
    }

    public function testImport()
    {
        return true;
    }

    /**
     * @param Tracker_Artifact $artifact The artifact
     * @param mixed            $value data coming from the request.
     *
     * @return bool
     */
    protected function validate(Tracker_Artifact $artifact, $value)
    {
        return true;
    }

    /**
     * @return ChartFieldUsage
     */
    private function getChartFieldUsage()
    {
        $use_start_date        = true;
        $use_duration          = true;
        $use_capacity          = true;
        $use_hierarchy         = false;
        $use_remaining_effort  = false;
        $is_under_construction = true;

        return new ChartFieldUsage(
            $use_start_date,
            $use_duration,
            $use_capacity,
            $use_hierarchy,
            $use_remaining_effort,
            $is_under_construction
        );
    }

    /**
     * @return FieldCalculator
     */
    private function getTeamEffortCalculator()
    {
        return new FieldCalculator(
            new BurnupTeamEffortCalculator(
                new BurnupManualValuesAndChildrenListRetriever(new BurnupDao())
            )
        );
    }

    /**
     * @return FieldCalculator
     */
    private function getTotalEffortCalculator()
    {
        return new FieldCalculator(
            new BurnupTotalEffortCalculator(
                new BurnupManualValuesAndChildrenListRetriever(new BurnupDao())
            )
        );
    }

    /**
     * @return BurnupDataBuilder
     */
    private function getBurnupDataBuilder()
    {
        $burnup_cache_dao = new BurnupCacheDao;
        return new BurnupDataBuilder(
            $this->getLogger(),
            new BurnupCacheChecker(
                new BurnupCacheGenerator(
                    SystemEventManager::instance()
                ),
                new ChartConfigurationValueChecker(
                    $this->getConfigurationFieldRetriever(),
                    $this->getConfigurationValueRetriever()
                ),
                $burnup_cache_dao,
                new ChartCachedDaysComparator($this->getLogger())
            ),
            $this->getConfigurationValueRetriever(),
            $burnup_cache_dao,
            $this->getTeamEffortCalculator(),
            $this->getTotalEffortCalculator()
        );
    }

    /**
     * @return BurnupLogger
     */
    private function getLogger()
    {
        return new BurnupLogger();
    }

    /**
     * @return ChartConfigurationValueRetriever
     */
    private function getConfigurationValueRetriever()
    {
        return new ChartConfigurationValueRetriever(
            $this->getConfigurationFieldRetriever(),
            $this->getLogger()
        );
    }
}
