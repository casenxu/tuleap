<?php
/**
 * Copyright (c) Enalean, 2012 - 2017. All Rights Reserved.
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

use Tuleap\Agiledashboard\FormElement\BurnupCacheDao;
use Tuleap\Agiledashboard\FormElement\BurnupCacheDateRetriever;
use Tuleap\AgileDashboard\FormElement\BurnupManualValuesAndChildrenListRetriever;
use Tuleap\AgileDashboard\FormElement\BurnupTotalEffortCalculator;
use Tuleap\AgileDashboard\FormElement\BurnupTeamEffortCalculator;
use Tuleap\AgileDashboard\FormElement\BurnupDao;
use Tuleap\Agiledashboard\FormElement\SystemEvent\SystemEvent_BURNUP_DAILY;
use Tuleap\AgileDashboard\FormElement\SystemEvent\SystemEvent_BURNUP_GENERATE;
use Tuleap\AgileDashboard\Kanban\RealTime\KanbanArtifactMessageBuilder;
use Tuleap\AgileDashboard\Kanban\RealTime\KanbanArtifactMessageSender;
use Tuleap\AgileDashboard\Kanban\TrackerReport\TrackerReportDao;
use Tuleap\AgileDashboard\Kanban\TrackerReport\TrackerReportUpdater;
use Tuleap\AgileDashboard\KanbanJavascriptDependenciesProvider;
use Tuleap\AgileDashboard\MonoMilestone\MonoMilestoneBacklogItemDao;
use Tuleap\AgileDashboard\MonoMilestone\MonoMilestoneItemsFinder;
use Tuleap\AgileDashboard\MonoMilestone\ScrumForMonoMilestoneChecker;
use Tuleap\AgileDashboard\MonoMilestone\ScrumForMonoMilestoneDao;
use Tuleap\AgileDashboard\RealTime\RealTimeArtifactMessageController;
use Tuleap\AgileDashboard\RealTime\RealTimeArtifactMessageSender;
use Tuleap\AgileDashboard\Semantic\Dao\SemanticDoneDao;
use Tuleap\AgileDashboard\Semantic\SemanticDone;
use Tuleap\AgileDashboard\Semantic\SemanticDoneFactory;
use Tuleap\AgileDashboard\Semantic\SemanticDoneValueChecker;
use Tuleap\AgileDashboard\Widget\MyKanban;
use Tuleap\AgileDashboard\Widget\ProjectKanban;
use Tuleap\AgileDashboard\Widget\WidgetKanbanCreator;
use Tuleap\AgileDashboard\Widget\WidgetKanbanDao;
use Tuleap\AgileDashboard\Widget\WidgetKanbanDeletor;
use Tuleap\AgileDashboard\Widget\WidgetKanbanRetriever;
use Tuleap\BurningParrotCompatiblePageEvent;
use Tuleap\Dashboard\Project\ProjectDashboardController;
use Tuleap\Dashboard\User\UserDashboardController;
use Tuleap\Layout\IncludeAssets;
use Tuleap\RealTime\NodeJSClient;
use Tuleap\Request\CurrentPage;
use Tuleap\Tracker\Artifact\Event\ArtifactCreated;
use Tuleap\Tracker\Artifact\Event\ArtifactsReordered;
use Tuleap\Tracker\FormElement\Field\ListFields\Bind\CanValueBeHiddenStatementsCollection;
use Tuleap\Tracker\FormElement\FieldCalculator;
use Tuleap\Tracker\Report\Event\TrackerReportDeleted;
use Tuleap\Tracker\Semantic\SemanticStatusCanBeDeleted;
use Tuleap\Tracker\Semantic\SemanticStatusGetDisabledValues;

require_once 'common/plugin/Plugin.class.php';
require_once 'autoload.php';
require_once 'constants.php';

/**
 * AgileDashboardPlugin
 */
class AgileDashboardPlugin extends Plugin {

    const PLUGIN_NAME = 'agiledashboard';
    const PLUGIN_SHORTNAME = 'plugin_agiledashboard';
    const HTTP_CLIENT_UUID = 'HTTP_X_CLIENT_UUID';

    /** @var AgileDashboard_SequenceIdManager */
    private $sequence_id_manager;

    /**
     * Plugin constructor
     */
    public function __construct($id) {
        parent::__construct($id);
        $this->setScope(self::SCOPE_PROJECT);
        bindTextDomain('tuleap-agiledashboard', AGILEDASHBOARD_BASE_DIR . '/../site-content');
    }

    public function getHooksAndCallbacks() {
        // Do not load the plugin if tracker is not installed & active
        if (defined('TRACKER_BASE_URL')) {
            require_once dirname(__FILE__) .'/../../tracker/include/autoload.php';
            $this->addHook('cssfile', 'cssfile', false);
            $this->addHook('javascript_file');
            $this->addHook(TRACKER_EVENT_INCLUDE_CSS_FILE, 'tracker_event_include_css_file', false);
            $this->addHook(TRACKER_EVENT_TRACKERS_DUPLICATED, 'tracker_event_trackers_duplicated', false);
            $this->addHook(TRACKER_EVENT_BUILD_ARTIFACT_FORM_ACTION, 'tracker_event_build_artifact_form_action', false);
            $this->addHook(TRACKER_EVENT_ARTIFACT_ASSOCIATION_EDITED, 'tracker_event_artifact_association_edited', false);
            $this->addHook(TRACKER_EVENT_REDIRECT_AFTER_ARTIFACT_CREATION_OR_UPDATE, 'tracker_event_redirect_after_artifact_creation_or_update', false);
            $this->addHook(TRACKER_EVENT_ARTIFACT_PARENTS_SELECTOR, 'event_artifact_parents_selector', false);
            $this->addHook(TRACKER_EVENT_MANAGE_SEMANTICS, 'tracker_event_manage_semantics', false);
            $this->addHook(TRACKER_EVENT_SEMANTIC_FROM_XML, 'tracker_event_semantic_from_xml');
            $this->addHook(TRACKER_EVENT_SOAP_SEMANTICS, 'tracker_event_soap_semantics');
            $this->addHook(TRACKER_EVENT_GET_SEMANTIC_FACTORIES);
            $this->addHook('plugin_statistics_service_usage');
            $this->addHook(TRACKER_EVENT_REPORT_DISPLAY_ADDITIONAL_CRITERIA);
            $this->addHook(TRACKER_EVENT_REPORT_PROCESS_ADDITIONAL_QUERY);
            $this->addHook(TRACKER_EVENT_REPORT_SAVE_ADDITIONAL_CRITERIA);
            $this->addHook(TRACKER_EVENT_REPORT_LOAD_ADDITIONAL_CRITERIA);
            $this->addHook(TRACKER_EVENT_FIELD_AUGMENT_DATA_FOR_REPORT);
            $this->addHook(TRACKER_USAGE);
            $this->addHook(Event::SERVICE_ICON);
            $this->addHook(Event::SERVICES_ALLOWED_FOR_PROJECT);
            $this->addHook(Event::REGISTER_PROJECT_CREATION);
            $this->addHook(TRACKER_EVENT_PROJECT_CREATION_TRACKERS_REQUIRED);
            $this->addHook(TRACKER_EVENT_GENERAL_SETTINGS);
            $this->addHook(Event::IMPORT_XML_PROJECT_CARDWALL_DONE);
            $this->addHook(Event::REST_RESOURCES);
            $this->addHook(Event::REST_RESOURCES_V2);
            $this->addHook(Event::REST_PROJECT_ADDITIONAL_INFORMATIONS);
            $this->addHook(Event::REST_PROJECT_AGILE_ENDPOINTS);
            $this->addHook(Event::REST_GET_PROJECT_PLANNINGS);
            $this->addHook(Event::REST_OPTIONS_PROJECT_PLANNINGS);
            $this->addHook(Event::REST_PROJECT_RESOURCES);
            $this->addHook(Event::REST_GET_PROJECT_MILESTONES);
            $this->addHook(Event::REST_OPTIONS_PROJECT_MILESTONES);
            $this->addHook(Event::REST_GET_PROJECT_BACKLOG);
            $this->addHook(Event::REST_PUT_PROJECT_BACKLOG);
            $this->addHook(Event::REST_PATCH_PROJECT_BACKLOG);
            $this->addHook(Event::REST_OPTIONS_PROJECT_BACKLOG);
            $this->addHook(Event::GET_PROJECTID_FROM_URL);
            $this->addHook(Event::COLLECT_ERRORS_WITHOUT_IMPORTING_XML_PROJECT);
            $this->addHook(ITEM_PRIORITY_CHANGE);
            $this->addHook(Tracker_Artifact_EditRenderer::EVENT_ADD_VIEW_IN_COLLECTION);
            $this->addHook(Event::BURNING_PARROT_GET_STYLESHEETS);
            $this->addHook(Event::BURNING_PARROT_GET_JAVASCRIPT_FILES);
            $this->addHook('widget_instance');
            $this->addHook('widgets');
            $this->addHook(BurningParrotCompatiblePageEvent::NAME);
            $this->addHook(CanValueBeHiddenStatementsCollection::NAME);
            $this->addHook(SemanticStatusGetDisabledValues::NAME);
            $this->addHook(SemanticStatusCanBeDeleted::NAME);
            $this->addHook(ArtifactCreated::NAME);
            $this->addHook(ArtifactsReordered::NAME);
            $this->addHook(TRACKER_EVENT_ARTIFACT_POST_UPDATE);
            $this->addHook(TrackerReportDeleted::NAME);
            $this->addHook(Tracker_FormElementFactory::GET_CLASSNAMES);
            $this->addHook(Event::GET_SYSTEM_EVENT_CLASS);
            $this->addHook('codendi_daily_start');
            $this->addHook(Event::SYSTEM_EVENT_GET_TYPES_FOR_DEFAULT_QUEUE);
        }

        if (defined('CARDWALL_BASE_URL')) {
            $this->addHook(CARDWALL_EVENT_USE_STANDARD_JAVASCRIPT,'cardwall_event_use_standard_javascript');
        }

        if (defined('TESTMANAGEMENT_BASE_URL')) {
            $this->addHook(\Tuleap\TestManagement\Event\GetMilestone::NAME);
            $this->addHook(\Tuleap\TestManagement\Event\GetItemsFromMilestone::NAME);
        }

        return parent::getHooksAndCallbacks();
    }

    public function tracker_formelement_get_classnames($params)
    {
        $params['dynamic']['burnup'] = "Tuleap\AgileDashboard\FormElement\Burnup";
    }

    /**
     * @see Plugin::getDependencies()
     */
    public function getDependencies() {
        return array('tracker', 'cardwall');
    }

    public function getServiceShortname() {
        return self::PLUGIN_SHORTNAME;
    }

    public function service_icon($params) {
        $params['list_of_icon_unicodes'][$this->getServiceShortname()] = '\e80e';
    }

    public function register_project_creation($params) {
        if ($params['project_creation_data']->projectShouldInheritFromTemplate()) {
            $this->getConfigurationManager()->duplicate(
                $params['group_id'],
                $params['template_id']
            );
        }
    }

    public function collect_errors_without_importing_xml_project($params)
    {
        $is_mono_milestone_enabled = $this->getMonoMilestoneChecker()->isMonoMilestoneEnabled(
            $params['project']->getId()
        );

        if ($is_mono_milestone_enabled && count($params['xml_content']->agiledashboard->plannings->planning) > 1) {
            $params['errors'] = $GLOBALS['Language']->getText(
                'plugin_agiledashboard',
                'cannot_import_planning_in_scrum_v2'
            );
        }
    }

    /**
     * @return AgileDashboard_ConfigurationManager
     */
    private function getConfigurationManager() {
        return new AgileDashboard_ConfigurationManager(
            new AgileDashboard_ConfigurationDao()
        );
    }

    public function cardwall_event_get_swimline_tracker($params) {
        $planning_factory = $this->getPlanningFactory();
        if ($planning = $planning_factory->getPlanningByPlanningTracker($params['tracker'])) {
            $params['backlog_trackers'] = $planning->getBacklogTrackers();
        }
    }

    /**
     * @see TRACKER_EVENT_REPORT_DISPLAY_ADDITIONAL_CRITERIA
     */
    public function tracker_event_report_display_additional_criteria($params) {
        $backlog_tracker = $params['tracker'];
        if (! $backlog_tracker) {
            return;
        }

        $planning_factory = $this->getPlanningFactory();
        $user             = $this->getCurrentUser();
        $provider         = new AgileDashboard_Milestone_MilestoneReportCriterionProvider(
            new AgileDashboard_Milestone_SelectedMilestoneProvider(
                $params['additional_criteria'],
                $this->getMilestoneFactory(),
                $user,
                $backlog_tracker->getProject()
            ),
            new AgileDashboard_Milestone_MilestoneReportCriterionOptionsProvider(
                new AgileDashboard_Planning_NearestPlanningTrackerProvider($planning_factory),
                new AgileDashboard_Milestone_MilestoneDao(),
                Tracker_HierarchyFactory::instance(),
                $planning_factory,
                TrackerFactory::instance(),
                Tracker_ArtifactFactory::instance()
            )
        );
        $additional_criterion = $provider->getCriterion($backlog_tracker, $user);

        if (! $additional_criterion) {
            return;
        }

        $params['array_of_html_criteria'][] = $additional_criterion;
    }

    /**
     * @see TRACKER_EVENT_REPORT_PROCESS_ADDITIONAL_QUERY
     */
    public function tracker_event_report_process_additional_query($params) {
        $backlog_tracker = $params['tracker'];

        $user    = $params['user'];
        $project = $backlog_tracker->getProject();

        $milestone_provider = new AgileDashboard_Milestone_SelectedMilestoneProvider($params['additional_criteria'], $this->getMilestoneFactory(), $user, $project);
        $milestone          = $milestone_provider->getMilestone();

        if ($milestone) {
            $provider = new AgileDashboard_BacklogItem_SubBacklogItemProvider(new Tracker_ArtifactDao(), $this->getBacklogStrategyFactory(), $this->getBacklogItemCollectionFactory());
            $params['result'][]         = $provider->getMatchingIds($milestone, $backlog_tracker, $user);
            $params['search_performed'] = true;
        }
    }

    /**
     * @see TRACKER_EVENT_REPORT_SAVE_ADDITIONAL_CRITERIA
     */
    public function tracker_event_report_save_additional_criteria($params) {
        $dao     = new MilestoneReportCriterionDao();
        $project = $params['report']->getTracker()->getProject();
        $user    = $this->getCurrentUser();

        $milestone_provider = new AgileDashboard_Milestone_SelectedMilestoneProvider($params['additional_criteria'], $this->getMilestoneFactory(), $user, $project);

        if ($milestone_provider->getMilestone()) {
            $dao->save($params['report']->getId(), $milestone_provider->getMilestoneId());
        } else {
            $dao->delete($params['report']->getId());
        }
    }

    /**
     * @see TRACKER_EVENT_REPORT_LOAD_ADDITIONAL_CRITERIA
     */
    public function tracker_event_report_load_additional_criteria($params) {
        $dao        = new MilestoneReportCriterionDao();
        $report_id  = $params['report']->getId();
        $field_name = AgileDashboard_Milestone_MilestoneReportCriterionProvider::FIELD_NAME;

        $row = $dao->searchByReportId($report_id)->getRow();
        if ($row){
            $params['additional_criteria_values'][$field_name]['value'] = $row['milestone_id'];
        }
    }

    public function event_artifact_parents_selector($params) {
        $artifact_parents_selector = new Planning_ArtifactParentsSelector(
            $this->getArtifactFactory(),
            PlanningFactory::build(),
            $this->getMilestoneFactory(),
            $this->getHierarchyFactory()
        );
        $event_listener = new Planning_ArtifactParentsSelectorEventListener($this->getArtifactFactory(), $artifact_parents_selector, HTTPRequest::instance());
        $event_listener->process($params);
    }

    public function tracker_event_include_css_file($params) {
        $params['include_tracker_css_file'] = true;
    }

    public function tracker_event_general_settings($params) {
        $hierarchyChecker = new AgileDashboard_HierarchyChecker(
                                $this->getPlanningFactory(),
                                $this->getKanbanFactory(),
                                $this->getTrackerFactory()
                            );
        $params['cannot_configure_instantiate_for_new_projects']= $hierarchyChecker->isPartOfScrumOrKanbanHierarchy($params['tracker']);
    }

    public function tracker_event_project_creation_trackers_required($params) {
        $hierarchyChecker = new AgileDashboard_HierarchyChecker(
                                $this->getPlanningFactory(),
                                $this->getKanbanFactory(),
                                $this->getTrackerFactory()
                            );
        $params['tracker_ids_list'] = array_merge(
            $params['tracker_ids_list'],
            $hierarchyChecker->getADTrackerIdsByProjectId($params['project_id'])
        );
    }

    public function tracker_event_trackers_duplicated($params) {
        PlanningFactory::build()->duplicatePlannings(
            $params['group_id'],
            $params['tracker_mapping'],
            $params['ugroups_mapping']
        );

        $this->getKanbanManager()->duplicateKanbans($params['tracker_mapping'], $params['field_mapping']);
    }

    public function tracker_event_redirect_after_artifact_creation_or_update($params) {
        $params_extractor        = new AgileDashboard_PaneRedirectionExtractor();
        $artifact_linker         = new Planning_ArtifactLinker($this->getArtifactFactory(), PlanningFactory::build());
        $last_milestone_artifact = $artifact_linker->linkBacklogWithPlanningItems($params['request'], $params['artifact']);
        $requested_planning      = $params_extractor->extractParametersFromRequest($params['request']);

        if ($requested_planning) {
            $this->redirectOrAppend($params['request'], $params['artifact'], $params['redirect'], $requested_planning, $last_milestone_artifact);
        }
    }

    public function tracker_usage($params) {
        $tracker    = $params['tracker'];
        $tracker_id = $tracker->getId();

        $is_used_in_planning = PlanningFactory::build()->isTrackerIdUsedInAPlanning($tracker_id);
        $is_used_in_backlog  = PlanningFactory::build()->isTrackerUsedInBacklog($tracker_id);
        $is_used_in_kanban   = $this->getKanbanManager()->doesKanbanExistForTracker($tracker);

        if ($is_used_in_planning || $is_used_in_backlog || $is_used_in_kanban) {
            $result['can_be_deleted'] = false;
            $result['message']        = 'Agile Dashboard';
            $params['result']         = $result;
        }

    }

    public function widgetInstance($params)
    {
        if ($params['widget'] !== MyKanban::NAME && $params['widget'] !== ProjectKanban::NAME) {
            return;
        }

        $widget_kanban_dao       = new WidgetKanbanDao();
        $widget_kanban_creator   = new WidgetKanbanCreator(
            $widget_kanban_dao
        );
        $widget_kanban_retriever = new WidgetKanbanRetriever(
            $widget_kanban_dao
        );
        $widget_kanban_deletor   = new WidgetKanbanDeletor(
            $widget_kanban_dao
        );
        $permission_manager      = new AgileDashboard_PermissionsManager();
        $kanban_factory          = new AgileDashboard_KanbanFactory(
            TrackerFactory::instance(),
            new AgileDashboard_KanbanDao()
        );

        switch ($params['widget']) {
            case MyKanban::NAME:
                $request           = HTTPRequest::instance();
                $tracker_report_id = $request->get('tracker_report_id');

                $params['instance'] = new MyKanban(
                    $widget_kanban_creator,
                    $widget_kanban_retriever,
                    $widget_kanban_deletor,
                    $kanban_factory,
                    TrackerFactory::instance(),
                    $permission_manager,
                    $tracker_report_id
                );
                break;
            case ProjectKanban::NAME:
                $request           = HTTPRequest::instance();
                $tracker_report_id = $request->get('tracker_report_id');

                $params['instance'] = new ProjectKanban(
                    $widget_kanban_creator,
                    $widget_kanban_retriever,
                    $widget_kanban_deletor,
                    $kanban_factory,
                    TrackerFactory::instance(),
                    $permission_manager,
                    $tracker_report_id
                );
                break;
            default:
                break;
        }
    }

    public function widgets($params)
    {
        if ($params['owner_type'] == UserDashboardController::LEGACY_DASHBOARD_TYPE) {
            $params['codendi_widgets'][] = MyKanban::NAME;
        }
        if ($params['owner_type'] == ProjectDashboardController::LEGACY_DASHBOARD_TYPE) {
            $params['codendi_widgets'][] = ProjectKanban::NAME;
        }
    }

    private function redirectOrAppend(Codendi_Request $request, Tracker_Artifact $artifact, Tracker_Artifact_Redirect $redirect, $requested_planning, Tracker_Artifact $last_milestone_artifact = null) {
        $planning = PlanningFactory::build()->getPlanning($requested_planning['planning_id']);

        if ($planning && ! $redirect->stayInTracker()) {
            $this->redirectToPlanning($artifact, $requested_planning, $planning, $redirect);
        } elseif (! $redirect->stayInTracker()) {
            $this->redirectToTopPlanning($artifact, $requested_planning, $redirect);
        } else {
             $this->setQueryParametersFromRequest($request, $redirect);
             // Pass the right parameters so parent can be created in the right milestone (see updateBacklogs)
             if ($planning && $last_milestone_artifact && $redirect->mode == Tracker_Artifact_Redirect::STATE_CREATE_PARENT) {
                 $redirect->query_parameters['child_milestone'] = $last_milestone_artifact->getId();
             }
        }
    }

    private function redirectToPlanning(Tracker_Artifact $artifact, $requested_planning, Planning $planning, Tracker_Artifact_Redirect $redirect) {
        $redirect_to_artifact = $requested_planning[AgileDashboard_PaneRedirectionExtractor::ARTIFACT_ID];
        if ($redirect_to_artifact == -1) {
            $redirect_to_artifact = $artifact->getId();
        }
        $redirect->base_url = '/plugins/agiledashboard/';
        $redirect->query_parameters = array(
            'group_id'    => $planning->getGroupId(),
            'planning_id' => $planning->getId(),
            'action'      => 'show',
            'aid'         => $redirect_to_artifact,
            'pane'        => $requested_planning[AgileDashboard_PaneRedirectionExtractor::PANE],
        );
    }

    private function redirectToTopPlanning(Tracker_Artifact $artifact, $requested_planning, Tracker_Artifact_Redirect $redirect) {
        $redirect->base_url = '/plugins/agiledashboard/';
        $group_id = null;

        if ($artifact->getTracker() &&  $artifact->getTracker()->getProject()) {
            $group_id = $artifact->getTracker()->getProject()->getID();
        }

        $redirect->query_parameters = array(
            'group_id'    => $group_id,
            'action'      => 'show-top',
            'pane'        => $requested_planning['pane'],
        );
    }

    public function tracker_event_build_artifact_form_action($params) {
        $this->setQueryParametersFromRequest($params['request'], $params['redirect']);
        if ($params['request']->exist('child_milestone')) {
            $params['redirect']->query_parameters['child_milestone'] = $params['request']->getValidated('child_milestone', 'uint', 0);
        }
    }

    private function setQueryParametersFromRequest(Codendi_Request $request, Tracker_Artifact_Redirect $redirect) {
        $params_extractor   = new AgileDashboard_PaneRedirectionExtractor();
        $requested_planning = $params_extractor->extractParametersFromRequest($request);
        if ($requested_planning) {
            $key   = 'planning['. $requested_planning[AgileDashboard_PaneRedirectionExtractor::PANE] .']['. $requested_planning[AgileDashboard_PaneRedirectionExtractor::PLANNING_ID] .']';
            $value = $requested_planning[AgileDashboard_PaneRedirectionExtractor::ARTIFACT_ID];
            $redirect->query_parameters[$key] = $value;
        }
    }

    /**
     * @return AgileDashboardPluginInfo
     */
    public function getPluginInfo() {
        if (!$this->pluginInfo) {
            $this->pluginInfo = new AgileDashboardPluginInfo($this);
        }
        return $this->pluginInfo;
    }

    public function cssfile($params)
    {
        if ($this->isAnAgiledashboardRequest() && ! $this->isKanbanURL()) {
            $theme_include_assets = new IncludeAssets(
                AGILEDASHBOARD_BASE_DIR . '/../www/themes/FlamingParrot/assets',
                $this->getThemePath() . '/assets'
            );
            $css_file_url = $theme_include_assets->getFileURL('style.css');
            echo '<link rel="stylesheet" type="text/css" href="' . $css_file_url . '" />';

            if ($this->isPlanningV2URL()) {
                echo '<link rel="stylesheet" type="text/css" href="' . $this->getPluginPath() . '/js/planning-v2/dist/planning-v2.css" />';
            }
        }
    }

    public function javascript_file()
    {
        if ($this->isAnAgiledashboardRequest()) {
            if ($this->isPlanningV2URL()) {
                $planning_v2_include_assets = new IncludeAssets(
                    AGILEDASHBOARD_BASE_DIR . '/../www/js/planning-v2/dist',
                    AGILEDASHBOARD_BASE_URL . '/js/planning-v2/dist'
                );

                echo $planning_v2_include_assets->getHTMLSnippet('planning-v2.js') ."\n";
            } else {
                echo $this->getMinifiedAssetHTML()."\n";
            }
        }
    }

    public function burning_parrot_get_stylesheets(array $params)
    {
        $theme_include_assets = new IncludeAssets(
            AGILEDASHBOARD_BASE_DIR . '/../www/themes/BurningParrot/assets',
            $this->getThemePath() . '/assets'
        );

        if ($this->isInDashboard() || $this->isKanbanURL()) {
            $variant = $params['variant'];
            $params['stylesheets'][] = $theme_include_assets->getFileURL('style-' . $variant->getName() . '.css');
        }
    }

    public function burning_parrot_get_javascript_files(array $params)
    {
        if ($this->isKanbanURL()) {
            $provider = new KanbanJavascriptDependenciesProvider();
            $params['javascript_files'][] = '/scripts/codendi/Tooltip.js';
            $params['javascript_files'][] = '/scripts/codendi/Tooltip-loader.js';
            foreach ($provider->getDependencies() as $javascript) {
                if (isset($javascript['snippet'])) {
                    $GLOBALS['HTML']->includeFooterJavascriptSnippet($javascript['snippet']);
                } else {
                    $params['javascript_files'][] = $javascript['file'];
                }
            }
        }
    }

    private function isInDashboard()
    {
        $current_page = new CurrentPage();

        return $current_page->isDashboard();
    }

    private function isAnAgiledashboardRequest() {
        return $this->currentRequestIsForPlugin();
    }

    private function isKanbanURL() {
        $request = HTTPRequest::instance();

        return $request->get('action') === 'showKanban';
    }

    private function isPlanningV2URL() {
        $request = HTTPRequest::instance();
        $pane_info_identifier = new AgileDashboard_PaneInfoIdentifier();

        return $pane_info_identifier->isPaneAPlanningV2($request->get('pane'));
    }

    public function process(Codendi_Request $request)
    {
        $project = $request->getProject();

        if ($project->isDeleted()) {
            $GLOBALS['Response']->addFeedback(
                Feedback::ERROR,
                $GLOBALS['Language']->getText(
                    'include_exit', 'project_status_' . $project->getStatus()
                )
            );

            $GLOBALS['Response']->redirect('/');
        }

        $builder = new AgileDashboardRouterBuilder();
        $router  = $builder->build($request);

        $router->route($request);
    }

    /**
     * Builds a new PlanningFactory instance.
     *
     * @return PlanningFactory
     */
    protected function getPlanningFactory() {
        return PlanningFactory::build();
    }

    /**
     * Builds a new Planning_MilestoneFactory instance.
     * @return Planning_MilestoneFactory
     */
    protected function getMilestoneFactory() {
        return new Planning_MilestoneFactory(
            $this->getPlanningFactory(),
            $this->getArtifactFactory(),
            Tracker_FormElementFactory::instance(),
            $this->getTrackerFactory(),
            $this->getStatusCounter(),
            new PlanningPermissionsManager(),
            new AgileDashboard_Milestone_MilestoneDao(),
            $this->getMonoMilestoneChecker()
        );
    }

    private function getArtifactFactory() {
        return Tracker_ArtifactFactory::instance();
    }

    private function getHierarchyFactory() {
        return Tracker_HierarchyFactory::instance();
    }

    private function getBacklogStrategyFactory() {
        return new AgileDashboard_Milestone_Backlog_BacklogStrategyFactory(
            new AgileDashboard_BacklogItemDao(),
            $this->getArtifactFactory(),
            PlanningFactory::build(),
            $this->getMonoMilestoneChecker(),
            $this->getMonoMilestoneItemsFinder()
        );
    }

    public function tracker_event_artifact_association_edited($params) {
        if ($params['request']->isAjax()) {

            $milestone_factory = $this->getMilestoneFactory();
            $milestone = $milestone_factory->getBareMilestoneByArtifact($params['user'], $params['artifact']);

            $milestone_with_contextual_info = $milestone_factory->updateMilestoneContextualInfo($params['user'], $milestone);

            $capacity         = $milestone_with_contextual_info->getCapacity();
            $remaining_effort = $milestone_with_contextual_info->getRemainingEffort();

            header('Content-type: application/json');
            echo json_encode(array(
                'remaining_effort' => $remaining_effort,
                'is_over_capacity' => $capacity !== null && $remaining_effort !== null && $capacity < $remaining_effort,
            ));
        }
    }

    /**
     * @see Event::TRACKER_EVENT_MANAGE_SEMANTICS
     */
    public function tracker_event_manage_semantics($parameters) {
        $tracker   = $parameters['tracker'];
        /* @var $semantics Tracker_SemanticCollection */
        $semantics = $parameters['semantics'];

        $effort_semantic = AgileDashBoard_Semantic_InitialEffort::load($tracker);
        $semantics->add($effort_semantic->getShortName(), $effort_semantic);

        $done_semantic = SemanticDone::load($tracker);
        $semantics->add($done_semantic->getShortName(), $done_semantic);
    }

    /**
     * @see Event::TRACKER_EVENT_SEMANTIC_FROM_XML
     */
    public function tracker_event_semantic_from_xml(&$parameters) {
        $tracker           = $parameters['tracker'];
        $xml               = $parameters['xml'];
        $full_semantic_xml = $parameters['full_semantic_xml'];
        $xmlMapping        = $parameters['xml_mapping'];
        $type              = $parameters['type'];

        if ($type == AgileDashBoard_Semantic_InitialEffort::NAME) {
            $parameters['semantic'] = $this->getSemanticInitialEffortFactory()->getInstanceFromXML($xml, $xmlMapping, $tracker);
        }

        if ($type == SemanticDone::NAME) {
            $factory = new SemanticDoneFactory(new SemanticDoneDao(), new SemanticDoneValueChecker());
            $parameters['semantic'] = $factory->getInstanceFromXML($xml, $full_semantic_xml, $xmlMapping, $tracker);
        }
    }

    /**
     * @see TRACKER_EVENT_GET_SEMANTIC_FACTORIES
     */
    public function tracker_event_get_semantic_factories($params) {
        $params['factories'][] = $this->getSemanticInitialEffortFactory();
    }

    protected function getSemanticInitialEffortFactory() {
        return AgileDashboard_Semantic_InitialEffortFactory::instance();
    }

    /**
     * Augment $params['semantics'] with names of AgileDashboard semantics
     *
     * @see TRACKER_EVENT_SOAP_SEMANTICS
     */
    public function tracker_event_soap_semantics(&$params) {
        $params['semantics'][] = AgileDashBoard_Semantic_InitialEffort::NAME;
    }

    /**
     *
     * @param array $param
     *  Expected key/ values:
     *      project_id  int             The ID of the project for the import
     *      xml_content SimpleXmlObject A string of valid xml
     *      mapping     array           An array of mappings between xml tracker IDs and their true IDs
     *
     */
    public function import_xml_project_cardwall_done($params) {
        $request = new HTTPRequest($params);
        $request->set('action', 'import');
        $request->set('xml_content', $params['xml_content']);
        $request->set('mapping', $params['mapping']);
        $request->set('project_id', $params['project_id']);
        $request->set('group_id', $params['project_id']);

        $this->process($request);
    }

    public function plugin_statistics_service_usage($params) {
        $dao                  = new AgileDashboard_Dao();
        $statistic_aggregator = new AgileDashboardStatisticsAggregator();
        $params['csv_exporter']->buildDatas($dao->getProjectsWithADActivated(), "Agile Dashboard activated");
        foreach ($statistic_aggregator->getStatisticsLabels() as $statistic_key => $statistic_name) {
            $statistic_data = $statistic_aggregator->getStatistics(
                $statistic_key,
                $params['start_date'],
                $params['end_date']
            );
            $params['csv_exporter']->buildDatas($statistic_data, $statistic_name);
        }
    }

    /**
     * @see REST_PROJECT_ADDITIONAL_INFORMATIONS
     */
    public function rest_project_additional_informations($params) {
        $planning_representation_class = '\\Tuleap\\AgileDashboard\\REST\\v1\\PlanningRepresentation';

        $root_planning = $this->getPlanningFactory()->getRootPlanning($this->getCurrentUser(), $params['project']->getGroupId());
        if (! $root_planning) {
            return;
        }

        $planning_representation = new $planning_representation_class();
        $planning_representation->build($root_planning);

        $params['informations'][$this->getName()]['root_planning'] = $planning_representation;
    }

    /**
     * @see REST_RESOURCES
     */
    public function rest_resources($params) {
        $injector = new AgileDashboard_REST_ResourcesInjector();
        $injector->populate($params['restler']);

        EventManager::instance()->processEvent(
            AGILEDASHBOARD_EVENT_REST_RESOURCES,
            $params
        );
    }

    /**
     * @see REST_RESOURCES_V2
     */
    public function rest_resources_v2($params) {
        $injector = new AgileDashboard_REST_v2_ResourcesInjector();
        $injector->populate($params['restler']);
    }

    /**
     * @see REST_GET_PROJECT_PLANNINGS
     */
    public function rest_get_project_plannings($params) {
        $user              = $this->getCurrentUser();
        $planning_resource = $this->buildRightVersionOfProjectPlanningsResource($params['version']);

        $params['result'] = $planning_resource->get(
            $user,
            $params['project'],
            $params['limit'],
            $params['offset']
        );
    }

    /**
     * @see REST_OPTIONS_PROJECT_PLANNINGS
     */
    public function rest_options_project_plannings($params) {
        $user              = $this->getCurrentUser();
        $planning_resource = $this->buildRightVersionOfProjectPlanningsResource($params['version']);

        $params['result'] = $planning_resource->options(
            $user,
            $params['project'],
            $params['limit'],
            $params['offset']
        );
    }

    private function buildRightVersionOfProjectPlanningsResource($version) {
        $class_with_right_namespace = '\\Tuleap\\AgileDashboard\\REST\\'.$version.'\\ProjectPlanningsResource';
        return new $class_with_right_namespace;
    }

    /**
     * @see Event::REST_PROJECT_RESOURCES
     */
    public function rest_project_resources(array $params) {
        $injector = new AgileDashboard_REST_ResourcesInjector();
        $injector->declareProjectPlanningResource($params['resources'], $params['project']);
    }

    /**
     * @see REST_GET_PROJECT_MILESTONES
     */
    public function rest_get_project_milestones($params) {
        $user               = $this->getCurrentUser();
        $milestone_resource = $this->buildRightVersionOfProjectMilestonesResource($params['version']);

        $params['result'] = $milestone_resource->get(
            $user,
            $params['project'],
            $params['representation_type'],
            $params['query'],
            $params['limit'],
            $params['offset'],
            $params['order']
        );
    }

    /**
     * @see REST_OPTIONS_PROJECT_MILESTONES
     */
    public function rest_options_project_milestones($params) {
        $user               = $this->getCurrentUser();
        $milestone_resource = $this->buildRightVersionOfProjectMilestonesResource($params['version']);

        $params['result'] = $milestone_resource->options(
            $user,
            $params['project'],
            $params['limit'],
            $params['offset']
        );
    }

    private function buildRightVersionOfProjectMilestonesResource($version) {
        $class_with_right_namespace = '\\Tuleap\\AgileDashboard\\REST\\'.$version.'\\ProjectMilestonesResource';
        return new $class_with_right_namespace;
    }

    /**
     * @see REST_GET_PROJECT_BACKLOG
     */
    public function rest_get_project_backlog($params) {
        $user                     = $this->getCurrentUser();
        $project_backlog_resource = $this->buildRightVersionOfProjectBacklogResource($params['version']);

        $params['result'] = $project_backlog_resource->get(
            $user,
            $params['project'],
            $params['limit'],
            $params['offset']
        );
    }

    /**
     * @see REST_OPTIONS_PROJECT_BACKLOG
     */
    public function rest_options_project_backlog($params) {
        $user                     = $this->getCurrentUser();
        $project_backlog_resource = $this->buildRightVersionOfProjectBacklogResource($params['version']);

        $params['result'] = $project_backlog_resource->options(
            $user,
            $params['project'],
            $params['limit'],
            $params['offset']
        );
    }

    /**
     * @see REST_PUT_PROJECT_BACKLOG
     */
    public function rest_put_project_backlog($params) {
        $user                     = $this->getCurrentUser();
        $project_backlog_resource = $this->buildRightVersionOfProjectBacklogResource($params['version']);

        $params['result'] = $project_backlog_resource->put(
            $user,
            $params['project'],
            $params['ids']
        );
    }

    /**
     * @see REST_PATCH_PROJECT_BACKLOG
     */
    public function rest_patch_project_backlog($params) {
        $user                     = UserManager::instance()->getCurrentUser();
        $project_backlog_resource = $this->buildRightVersionOfProjectBacklogResource($params['version']);

        $params['result'] = $project_backlog_resource->patch(
            $user,
            $params['project'],
            $params['order'],
            $params['add']
        );
    }

    /**
    * @see ITEM_PRIORITY_CHANGE
    */
    public function item_priority_change($params) {
        $planning_id = $this->getPlanningIdFromParameters($params);

        $params['user_is_authorized'] = $this->getPlanningPermissionsManager()->userHasPermissionOnPlanning(
            $planning_id,
            $params['group_id'],
            $params['user'],
            PlanningPermissionsManager::PERM_PRIORITY_CHANGE
        );
    }

    private function getPlanningIdFromParameters($params) {
        if ($params['milestone_id'] == 0) {
            $planning = $this->getPlanningFactory()->getRootPlanning(
                $params['user'],
                $params['group_id']
            );

            return $planning->getId();
        }

        $artifact    = $this->getArtifactFactory()->getArtifactById($params['milestone_id']);
        $milestone   = $this->getMilestoneFactory()->getMilestoneFromArtifact($artifact);

        return $milestone->getPlanningId();

    }

    private function buildRightVersionOfProjectBacklogResource($version) {
        $class_with_right_namespace = '\\Tuleap\\AgileDashboard\\REST\\'.$version.'\\ProjectBacklogResource';
        return new $class_with_right_namespace;
    }

    private function getStatusCounter() {
        return new AgileDashboard_Milestone_MilestoneStatusCounter(
            new AgileDashboard_BacklogItemDao(),
            new Tracker_ArtifactDao(),
            $this->getArtifactFactory()
        );
    }

    /** @see Event::GET_PROJECTID_FROM_URL */
    public function get_projectid_from_url($params) {
        if (strpos($params['url'],'/plugins/agiledashboard/') === 0) {
            $params['project_id'] = $params['request']->get('group_id');
        }
    }

    /**
     * @see TRACKER_EVENT_FIELD_AUGMENT_DATA_FOR_REPORT
     */
    public function tracker_event_field_augment_data_for_report($params) {
        if (! $this->isFieldPriority($params['field'])) {
            return;
        }

        $params['result'] = $this->getFieldPriorityAugmenter()->getAugmentedDataForFieldPriority(
            $this->getCurrentUser(),
            $params['field']->getTracker()->getProject(),
            $params['additional_criteria'],
            $params['artifact_id']
        );
    }

    private function getFieldPriorityAugmenter() {
        return new AgileDashboard_FieldPriorityAugmenter(
            $this->getSequenceIdManager(),
            $this->getMilestoneFactory()
        );
    }

    private function isFieldPriority(Tracker_FormElement_Field $field) {
        return $field instanceof Tracker_FormElement_Field_Priority;
    }

    private function getSequenceIdManager() {
        if (! $this->sequence_id_manager) {
            $this->sequence_id_manager = new AgileDashboard_SequenceIdManager(
                    $this->getBacklogStrategyFactory(),
                    $this->getBacklogItemCollectionFactory()
            );
        }

        return $this->sequence_id_manager;
    }

    private function getBacklogItemCollectionFactory() {
        return new AgileDashboard_Milestone_Backlog_BacklogItemCollectionFactory(
            new AgileDashboard_BacklogItemDao(),
            $this->getArtifactFactory(),
            Tracker_FormElementFactory::instance(),
            $this->getMilestoneFactory(),
            $this->getPlanningFactory(),
            new AgileDashboard_Milestone_Backlog_BacklogItemBuilder()
        );
    }

    public function cardwall_event_use_standard_javascript($params) {
        $request = HTTPRequest::instance();
        $pane_info_identifier = new AgileDashboard_PaneInfoIdentifier();
        if ($pane_info_identifier->isPaneAPlanningV2($request->get('pane')) || $this->isKanbanURL()) {
            $params['use_standard'] = false;
        }
    }


    public function rest_project_agile_endpoints($params) {
        $params['available'] = true;
    }

    /** @see Tracker_Artifact_EditRenderer::EVENT_ADD_VIEW_IN_COLLECTION */
    public function tracker_artifact_editrenderer_add_view_in_collection(array $params)
    {
        $user       = $params['user'];
        $request    = $params['request'];
        $artifact   = $params['artifact'];
        $collection = $params['collection'];

        $milestone = $this->getMilestoneFactory()->getBareMilestoneByArtifact($user, $artifact);
        if ($milestone) {
            $collection->add(new Tuleap\AgileDashboard\Milestone\ArtifactView($milestone, $request, $user));
        }
    }

    public function burning_parrot_compatible_page(BurningParrotCompatiblePageEvent $event)
    {
        if ($this->isKanbanURL()) {
            $event->setIsInBurningParrotCompatiblePage();
        }
    }

    /**
     * @return TrackerFactory
     */
    private function getTrackerFactory() {
        return TrackerFactory::instance();
    }

    /**
     * @return AgileDashboard_KanbanManager
     */
    private function getKanbanManager() {
        return new AgileDashboard_KanbanManager(
            new AgileDashboard_KanbanDao(),
            $this->getTrackerFactory(),
            $this->getHierarchyChecker()
        );
    }

    private function getCurrentUser() {
        return UserManager::instance()->getCurrentUser();
    }

    private function getPlanningPermissionsManager() {
        return new PlanningPermissionsManager();
    }

    /**
     * @return AgileDashboard_HierarchyChecker
     */
    private function getHierarchyChecker() {
        return new AgileDashboard_HierarchyChecker(
            $this->getPlanningFactory(),
            $this->getKanbanFactory(),
            $this->getTrackerFactory()
        );
    }

    /**
     * @return AgileDashboard_KanbanFactory
     */
    private function getKanbanFactory() {
        return new AgileDashboard_KanbanFactory(
            TrackerFactory::instance(),
            new AgileDashboard_KanbanDao()

        );
    }

    /**
     * @return ScrumForMonoMilestoneChecker
     */
    private function getMonoMilestoneChecker()
    {
        return new ScrumForMonoMilestoneChecker(new ScrumForMonoMilestoneDao(), $this->getPlanningFactory());
    }

    private function getMonoMilestoneItemsFinder()
    {
        return new MonoMilestoneItemsFinder(
            new MonoMilestoneBacklogItemDao(),
            $this->getArtifactFactory()
        );
    }

    public function testmanagementGetMilestone(\Tuleap\TestManagement\Event\GetMilestone $event)
    {
        $milestone_factory = $this->getMilestoneFactory();
        $milestone         = $milestone_factory->getBareMilestoneByArtifactId($event->getUser(), $event->getMilestoneId());
        $event->setMilestone($milestone);
    }

    public function testmanagementGetItemsFromMilestone(\Tuleap\TestManagement\Event\GetItemsFromMilestone $event)
    {
        $milestone_factory               = $this->getMilestoneFactory();
        $backlog_strategy_factory        = $this->getBacklogStrategyFactory();
        $backlog_item_collection_factory = $this->getBacklogItemCollectionFactory();

        $user          = $event->getUser();
        $milestone_id  = $event->getMilestoneId();
        $milestone     = $milestone_factory->getValidatedBareMilestoneByArtifactId($user, $milestone_id);
        $strategy      = $backlog_strategy_factory->getSelfBacklogStrategy($milestone);
        $backlog_items = $backlog_item_collection_factory->getAllCollection($user, $milestone, $strategy, '');
        $items_ids     = array();

        foreach ($backlog_items as $item) {
            $items_ids[] = $item->id();

            if ($item->hasChildren()) {
                $this->parseChildrenElements($item, $user, $items_ids);
            }
        }

        $event->setItemsIds(array_unique($items_ids));
    }

    private function parseChildrenElements(AgileDashboard_Milestone_Backlog_BacklogItem $item, PFUser $user, array &$item_ids)
    {
        $tracker_artifact_dao = new Tracker_ArtifactDao();

        $children = $tracker_artifact_dao->getChildren($item->getArtifact()->getId())
            ->instanciateWith(array($this->getArtifactFactory(), 'getInstanceFromRow'));

        foreach ($children as $child) {
            if ($child->userCanView($user)) {
                $item_ids[] = $child->getId();
            }
        }
    }

    public function canValueBeHiddenStatementsCollection(CanValueBeHiddenStatementsCollection $event)
    {
        $field   = $event->getField();
        $tracker = $field->getTracker();
        $dao     = new SemanticDoneDao();

        $event->add($dao->getSemanticStatement($field->getId(), $tracker->getId()));
    }

    public function semanticStatusGetDisabledValues(SemanticStatusGetDisabledValues $event)
    {
        $field   = $event->getField();
        $tracker = $field->getTracker();
        $dao     = new SemanticDoneDao();

        $disabled_values = array();
        foreach ($dao->getSelectedValues($tracker->getId()) as $value_row) {
            $disabled_values[] = $value_row['value_id'];
        }

        $event->setDisabledValues(array_unique($disabled_values));
    }

    public function semanticStatusCanBeDeleted(SemanticStatusCanBeDeleted $event)
    {
        $tracker = $event->getTracker();
        $dao     = new SemanticDoneDao();

        $selected_values = $dao->getSelectedValues($tracker->getId());

        if ($selected_values->rowCount() === 0) {
            $event->semanticIsDeletable();
        } else {
            $GLOBALS['Response']->addFeedback(
                Feedback::WARN,
                dgettext('tuleap-agiledashboard', 'The semantic status cannot de deleted because the semantic done is defined for this tracker.')
            );
        }
    }

    /**
     * @return KanbanArtifactMessageSender
     */
    private function getKanbanArtifactMessageSender()
    {
        $kanban_item_dao                   = new AgileDashboard_KanbanItemDao();
        $permissions_serializer            = new Tracker_Permission_PermissionsSerializer(
            new Tracker_Permission_PermissionRetrieveAssignee(UserManager::instance())
        );
        $node_js_client                    = new NodeJSClient();
        $realtime_artifact_message_builder = new KanbanArtifactMessageBuilder(
            $kanban_item_dao,
            new Tracker_Artifact_ChangesetFactory(
                new Tracker_Artifact_ChangesetDao(),
                new Tracker_Artifact_Changeset_ValueDao(),
                new Tracker_Artifact_Changeset_CommentDao(),
                new Tracker_Artifact_ChangesetJsonFormatter(
                    TemplateRendererFactory::build()->getRenderer(dirname(TRACKER_BASE_DIR).'/templates')
                ),
                Tracker_FormElementFactory::instance()
            )
        );
        $backend_logger                    = new BackendLogger(ForgeConfig::get('codendi_log') .'/realtime_syslog');
        $realtime_artifact_message_sender  = new RealTimeArtifactMessageSender($node_js_client, $permissions_serializer);

        return new KanbanArtifactMessageSender(
            $realtime_artifact_message_sender,
            $realtime_artifact_message_builder,
            $backend_logger
        );
    }

    /**
     * @return RealTimeArtifactMessageController
     */
    public function getRealtimeMessageController()
    {
        return new RealTimeArtifactMessageController(
            $this->getKanbanFactory(),
            $this->getKanbanArtifactMessageSender()
        );
    }

    public function trackerArtifactCreated(ArtifactCreated $event)
    {
        $artifact  = $event->getArtifact();
        $this->getRealtimeMessageController()->sendMessageForKanban(
            $this->getCurrentUser(),
            $artifact,
            RealTimeArtifactMessageController::EVENT_NAME_ARTIFACT_CREATED
        );
    }

    public function tracker_event_artifact_post_update(array $params)
    {
        $artifact  = $params['artifact'];
        $this->getRealtimeMessageController()->sendMessageForKanban(
            $this->getCurrentUser(),
            $artifact,
            RealTimeArtifactMessageController::EVENT_NAME_ARTIFACT_UPDATED
        );
    }

    public function trackerArtifactsReordered(ArtifactsReordered $event)
    {
        $artifacts_ids = $event->getArtifactsIds();
        $artifacts     = $this->getArtifactFactory()->getArtifactsByArtifactIdList($artifacts_ids);
        foreach ($artifacts as $artifact) {
            $this->getRealtimeMessageController()->sendMessageForKanban(
                $this->getCurrentUser(),
                $artifact,
                RealTimeArtifactMessageController::EVENT_NAME_ARTIFACT_REORDERED
            );
        }
    }

    public function trackerReportDeleted(TrackerReportDeleted $event)
    {
        $report  = $event->getReport();
        $updater = new TrackerReportUpdater(new TrackerReportDao());

        $updater->deleteAllForReport($report);
    }

    public function codendi_daily_start($params)
    {
        SystemEventManager::instance()->createEvent(
            'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_DAILY::NAME,
            "",
            SystemEvent::PRIORITY_MEDIUM,
            SystemEvent::OWNER_APP
        );
    }


    public function get_system_event_class($params)
    {
        switch ($params['type']) {
            case 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_DAILY::NAME:
                $params['class']        = 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_DAILY::NAME;
                $params['dependencies'] = array(
                    $this->getBurnupDao(),
                    $this->getTotalEffortCalculator(),
                    $this->getTeamEffortCalculator(),
                    new BurnupCacheDao(),
                    $this->getLogger(),
                    new BurnupCacheDateRetriever()
                );
                break;
            case 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_GENERATE::NAME:
                $params['class']        = 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_GENERATE::NAME;
                $params['dependencies'] = array(
                    new BurnupDao(),
                    $this->getTotalEffortCalculator(),
                    $this->getTeamEffortCalculator(),
                    new BurnupCacheDao(),
                    $this->getLogger(),
                    new BurnupCacheDateRetriever
                );
                break;
            default:
                break;
        }
    }

    public function system_event_get_types_for_default_queue($params)
    {
        $params['types'][] = 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_DAILY::NAME;
        $params['types'][] = 'Tuleap\\Agiledashboard\\FormElement\\SystemEvent\\' . SystemEvent_BURNUP_GENERATE::NAME;
    }

    /**
     * @return BurnupDao
     */
    private function getBurnupDao()
    {
        return new BurnupDao();
    }

    /**
     * @return BackendLogger
     */
    private function getLogger()
    {
        return new BackendLogger();
    }

    /**
     * @return BurnupManualValuesAndChildrenListRetriever
     */
    private function getBurnupRetreiver()
    {
        return new BurnupManualValuesAndChildrenListRetriever($this->getBurnupDao());
    }

    /**
     * @return FieldCalculator
     */
    private function getTotalEffortCalculator()
    {
        return new FieldCalculator(
            new BurnupTotalEffortCalculator($this->getBurnupRetreiver())
        );
    }

    /**
     * @return FieldCalculator
     */
    private function getTeamEffortCalculator()
    {
        return new FieldCalculator(
            new BurnupTeamEffortCalculator(
                $this->getBurnupRetreiver()
            )
        );
    }

}
