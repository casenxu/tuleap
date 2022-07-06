<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\AsynchronousCreation;

use Psr\Log\Test\TestLogger;
use Tuleap\ProgramManagement\Adapter\Workspace\MessageLog;
use Tuleap\ProgramManagement\Tests\Builder\JustLinkedIterationCollectionBuilder;
use Tuleap\ProgramManagement\Tests\Builder\PendingIterationCreationBuilder;
use Tuleap\ProgramManagement\Tests\Builder\ProgramIncrementIdentifierBuilder;
use Tuleap\ProgramManagement\Tests\Stub\ProgramIncrementUpdateEventStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveIterationTrackerStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveLastChangesetStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;

final class IterationCreationTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const USER_ID                            = 101;
    private const PROGRAM_INCREMENT_ID               = 54;
    private const PROGRAM_INCREMENT_CHANGESET_ID     = 8769;
    private const PROGRAM_INCREMENT_OLD_CHANGESET_ID = 8768;
    private const ITERATION_TRACKER_ID               = 91;
    private const FIRST_ITERATION_ID                 = 573;
    private const SECOND_ITERATION_ID                = 268;
    private const FIRST_CHANGESET_ID                 = 4021;
    private const SECOND_CHANGESET_ID                = 4997;
    private RetrieveLastChangesetStub $changeset_retriever;
    private TestLogger $logger;
    private RetrieveIterationTrackerStub $tracker_retriever;

    protected function setUp(): void
    {
        $this->changeset_retriever = RetrieveLastChangesetStub::withLastChangesetIds(
            self::FIRST_CHANGESET_ID,
            self::SECOND_CHANGESET_ID
        );
        $this->logger              = new TestLogger();
        $this->tracker_retriever   = RetrieveIterationTrackerStub::withValidTracker(self::ITERATION_TRACKER_ID);
    }

    /**
     * @return IterationCreation[]
     */
    private function getCreationsFromJustLinkedIterations(): array
    {
        $program_increment      = ProgramIncrementIdentifierBuilder::buildWithId(self::PROGRAM_INCREMENT_ID);
        $just_linked_iterations = JustLinkedIterationCollectionBuilder::buildWithProgramIncrementAndIterationIds(
            $program_increment,
            self::FIRST_ITERATION_ID,
            self::SECOND_ITERATION_ID
        );
        return IterationCreation::buildCollectionFromJustLinkedIterations(
            $this->changeset_retriever,
            $this->tracker_retriever,
            MessageLog::buildFromLogger($this->logger),
            $just_linked_iterations,
            UserIdentifierStub::withId(self::USER_ID)
        );
    }

    public function testItRetrievesLastChangesetOfEachIterationAndBuildsCollection(): void
    {
        [$first_creation, $second_creation] = $this->getCreationsFromJustLinkedIterations();
        self::assertSame(self::FIRST_ITERATION_ID, $first_creation->getIteration()->getId());
        self::assertSame(self::FIRST_ITERATION_ID, $first_creation->getTimebox()->getId());
        self::assertSame(self::ITERATION_TRACKER_ID, $first_creation->getTracker()->getId());
        self::assertSame(self::PROGRAM_INCREMENT_ID, $first_creation->getProgramIncrement()->getId());
        self::assertSame(self::USER_ID, $first_creation->getUser()->getId());
        self::assertSame(self::FIRST_CHANGESET_ID, $first_creation->getChangeset()->getId());

        self::assertSame(self::SECOND_ITERATION_ID, $second_creation->getIteration()->getId());
        self::assertSame(self::SECOND_ITERATION_ID, $second_creation->getTimebox()->getId());
        self::assertSame(self::ITERATION_TRACKER_ID, $second_creation->getTracker()->getId());
        self::assertSame(self::PROGRAM_INCREMENT_ID, $second_creation->getProgramIncrement()->getId());
        self::assertSame(self::USER_ID, $second_creation->getUser()->getId());
        self::assertSame(self::SECOND_CHANGESET_ID, $second_creation->getChangeset()->getId());
    }

    public function testItSkipsIterationWhenItHasNoLastChangeset(): void
    {
        $this->changeset_retriever = RetrieveLastChangesetStub::withNoLastChangeset();

        self::assertEmpty($this->getCreationsFromJustLinkedIterations());
        self::assertTrue(
            $this->logger->hasErrorThatMatches('/Could not retrieve last changeset of iteration #[0-9]+, skipping it$/')
        );
    }

    /**
     * @return IterationCreation[]
     */
    private function getCreationsFromProgramIncrementUpdate(): array
    {
        $first_iteration  = PendingIterationCreationBuilder::buildWithIds(
            self::FIRST_ITERATION_ID,
            self::FIRST_CHANGESET_ID
        );
        $second_iteration = PendingIterationCreationBuilder::buildWithIds(
            self::SECOND_ITERATION_ID,
            self::SECOND_CHANGESET_ID
        );

        $update_event = ProgramIncrementUpdateEventStub::withIds(
            self::PROGRAM_INCREMENT_ID,
            self::USER_ID,
            self::PROGRAM_INCREMENT_CHANGESET_ID,
            self::PROGRAM_INCREMENT_OLD_CHANGESET_ID,
            $first_iteration,
            $second_iteration
        );
        return IterationCreation::buildCollectionFromProgramIncrementUpdateEvent(
            $this->tracker_retriever,
            $update_event
        );
    }

    public function testItBuildsCollectionFromProgramIncrementUpdateEvent(): void
    {
        [$first_creation, $second_creation] = $this->getCreationsFromProgramIncrementUpdate();
        self::assertSame(self::FIRST_ITERATION_ID, $first_creation->getIteration()->getId());
        self::assertSame(self::FIRST_ITERATION_ID, $first_creation->getTimebox()->getId());
        self::assertSame(self::ITERATION_TRACKER_ID, $first_creation->getTracker()->getId());
        self::assertSame(self::PROGRAM_INCREMENT_ID, $first_creation->getProgramIncrement()->getId());
        self::assertSame(self::USER_ID, $first_creation->getUser()->getId());
        self::assertSame(self::FIRST_CHANGESET_ID, $first_creation->getChangeset()->getId());

        self::assertSame(self::SECOND_ITERATION_ID, $second_creation->getIteration()->getId());
        self::assertSame(self::SECOND_ITERATION_ID, $second_creation->getTimebox()->getId());
        self::assertSame(self::ITERATION_TRACKER_ID, $second_creation->getTracker()->getId());
        self::assertSame(self::PROGRAM_INCREMENT_ID, $second_creation->getProgramIncrement()->getId());
        self::assertSame(self::USER_ID, $second_creation->getUser()->getId());
        self::assertSame(self::SECOND_CHANGESET_ID, $second_creation->getChangeset()->getId());
    }

    public function testItBuildsEmptyCollectionWhenEventHasNoPendingIterations(): void
    {
        $update_event = ProgramIncrementUpdateEventStub::withNoIterations(
            self::PROGRAM_INCREMENT_ID,
            self::USER_ID,
            self::PROGRAM_INCREMENT_CHANGESET_ID,
            self::PROGRAM_INCREMENT_OLD_CHANGESET_ID
        );

        self::assertEmpty(
            IterationCreation::buildCollectionFromProgramIncrementUpdateEvent($this->tracker_retriever, $update_event)
        );
    }
}
