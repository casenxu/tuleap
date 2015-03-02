<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
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

namespace Tuleap\Testing\REST\v1;

use Luracast\Restler\RestException;
use Tracker_FormElementFactory;
use ProjectManager;
use PFUser;
use Tuleap\Testing\Config;
use TrackerFactory;
use Tuleap\Tracker\REST\TrackerReference;
use Tuleap\Tracker\REST\Artifact\ArtifactReference;
use Tuleap\Tracker\REST\v1\ArtifactPOSTValues;
use Tracker_REST_Artifact_ArtifactCreator;

class CampaignCreator {

    /**
     * @var ExecutionCreator
     */
    private $execution_creator;

    /** @var Tracker_FormElementFactory */
    private $formelement_factory;

    /** @var Config */
    private $config;

    /** @var ProjectManager */
    private $project_manager;

    /** @var TrackerFactory */
    private $tracker_factory;

    /** @var Tracker_REST_Artifact_ArtifactCreator */
    private $artifact_creator;

    public function __construct(
        Tracker_FormElementFactory $formelement_factory,
        Config $config,
        ProjectManager $project_manager,
        TrackerFactory $tracker_factory,
        Tracker_REST_Artifact_ArtifactCreator $artifact_creator,
        ExecutionCreator $execution_creator
    ) {
        $this->formelement_factory = $formelement_factory;
        $this->config              = $config;
        $this->project_manager     = $project_manager;
        $this->tracker_factory     = $tracker_factory;
        $this->artifact_creator    = $artifact_creator;
        $this->execution_creator   = $execution_creator;
    }

    /**
     * @return ArtifactReference
     */
    public function createCampaignAndExecutions(PFUser $user, $project_id, $label, $environments) {
        try {
            $execution_ids = $this->createTestExecutionsForDefinitions($project_id, $user, $environments);

            return $this->createCampaign($project_id, $label, $execution_ids, $user);
        } catch (Tracker_FormElement_InvalidFieldException $exception) {
            throw new RestException(400, $exception->getMessage());
        } catch (Tracker_FormElement_InvalidFieldValueException $exception) {
            throw new RestException(400, $exception->getMessage());
        } catch (Tracker_Artifact_Attachment_FileNotFoundException $exception) {
            throw new RestException(400, $exception->getMessage());
        } catch (Tracker_Artifact_Attachment_AlreadyLinkedToAnotherArtifactException $exception) {
            throw new RestException(400, $exception->getMessage());
        }
    }

    private function createCampaign($project_id, $label, $execution_ids, PFUser $user) {
        $tracker = $this->getCampaignTrackerReferenceForProject($project_id);
        $values  = $this->getFieldValuesForCampaignArtifactCreation($tracker, $user, $label, $execution_ids);

        return $this->artifact_creator->create($user, $tracker, $values);
    }

    /** @return int[] the ids of created executions */
    private function createTestExecutionsForDefinitions(
        $project_id,
        PFUser $user,
        $environments
    ) {
        $execution_ids = array();
        foreach ($environments as $environment_id => $definition_ids) {
            $execution_ids = array_merge(
                $execution_ids,
                $this->createTestExecutionsInEnvironmentForDefinitions(
                    $project_id,
                    $user,
                    $environment_id,
                    $definition_ids
                )
            );
        }

        return $execution_ids;
    }

    /** @return int[] the ids of created executions */
    private function createTestExecutionsInEnvironmentForDefinitions(
        $project_id,
        PFUser $user,
        $environment_id,
        array $definition_ids
    ) {
        $execution_ids = array();
        foreach ($definition_ids as $definition_id) {
            $execution = $this->execution_creator->createTestExecution(
                $project_id,
                $user,
                $environment_id,
                $definition_id
            );
            $execution_ids[] = array('id' => $execution->id);
        }

        return $execution_ids;
    }

    private function getCampaignTrackerReferenceForProject($project_id) {
        $project = $this->project_manager->getProject($project_id);
        if ($project->isError()) {
            throw new RestException(404, 'Project not found');
        }

        $campaign_tracker_id = $this->config->getCampaignTrackerId($project);
        $campaign_tracker    = $this->tracker_factory->getTrackerById($campaign_tracker_id);
        if (! $campaign_tracker) {
            throw new RestException(400, 'The project does not contain a campaign tracker');
        }

        $tracker_reference = new TrackerReference();
        $tracker_reference->build($campaign_tracker);

        return $tracker_reference;
    }
    private function getFieldValuesForCampaignArtifactCreation(
        TrackerReference $tracker_reference,
        PFUser $user,
        $label,
        $execution_ids
    ) {
        $label_field  = $this->getField($tracker_reference, $user, CampaignRepresentation::FIELD_NAME);
        $status_field = $this->getField($tracker_reference, $user, CampaignRepresentation::FIELD_STATUS);
        $link_field   = $this->getField($tracker_reference, $user, CampaignRepresentation::FIELD_ARTIFACT_LINKS);

        $label_value           = new ArtifactPOSTValues();
        $label_value->field_id = (int)$label_field->getId();
        $label_value->value    = $label;

        $status_value                 = new ArtifactPOSTValues();
        $status_value->field_id       = (int)$status_field->getId();
        $status_value->bind_value_ids = array((int)$status_field->getDefaultValue());

        $link_value           = new ArtifactPOSTValues();
        $link_value->field_id = (int)$link_field->getId();
        $link_value->links    = $execution_ids;

        return array($label_value, $status_value, $link_value);
    }

    private function getField(
        TrackerReference $tracker_reference,
        PFUser $user,
        $field_name
    ) {
        $field = $this->formelement_factory->getUsedFieldByNameForUser(
            $tracker_reference->id,
            $field_name,
            $user
        );
        if (! $field) {
            throw new RestException(400, "No $field_name field. Execution tracker misconfigured");
        }

        return $field;
    }
}