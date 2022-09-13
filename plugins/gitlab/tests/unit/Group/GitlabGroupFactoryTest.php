<?php
/**
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Gitlab\Group;

use Tuleap\Gitlab\API\Group\GitlabGroupApiDataRepresentation;
use Tuleap\Gitlab\Test\Stubs\AddNewGroupStub;
use Tuleap\Gitlab\Test\Stubs\VerifyGroupIsAlreadyLinkedStub;
use Tuleap\Gitlab\Test\Stubs\VerifyProjectIsAlreadyLinkedStub;
use Tuleap\Test\Builders\ProjectTestBuilder;

final class GitlabGroupFactoryTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const GROUP_ID            = 85;
    private const INTEGRATED_GROUP_ID = 77;
    private VerifyGroupIsAlreadyLinkedStub $group_verifier;
    private VerifyProjectIsAlreadyLinkedStub $project_verifier;
    private AddNewGroupStub $group_adder;

    protected function setUp(): void
    {
        $this->group_verifier   = VerifyGroupIsAlreadyLinkedStub::withNeverLinked();
        $this->project_verifier = VerifyProjectIsAlreadyLinkedStub::withNeverLinked();
        $this->group_adder      = AddNewGroupStub::withGroupId(self::INTEGRATED_GROUP_ID);
    }

    private function createGroup(): GitlabGroup
    {
        $factory = new GitlabGroupFactory($this->group_verifier, $this->project_verifier, $this->group_adder);

        $api_group = GitlabGroupApiDataRepresentation::buildGitlabGroupFromApi([
            'id'         => self::GROUP_ID,
            'name'       => 'puntal',
            'full_path'  => 'antithenar/puntal',
            'web_url'    => 'https://gitlab.example.com/antithenar/puntal',
            'avatar_url' => 'https://gitlab.example.com/avatar',
        ]);

        $new_group = NewGroup::fromAPIRepresentation(
            $api_group,
            ProjectTestBuilder::aProject()->withId(153)->build(),
            new \DateTimeImmutable(),
            true,
            'dev-'
        );

        return $factory->createGroup($new_group);
    }

    public function testItCreatesNewGroup(): void
    {
        $group = $this->createGroup();

        self::assertSame(self::INTEGRATED_GROUP_ID, $group->id);
        self::assertSame(self::GROUP_ID, $group->gitlab_group_id);
    }

    public function testItThrowsIfGivenGitlabGroupIsAlreadyLinked(): void
    {
        $this->group_verifier = VerifyGroupIsAlreadyLinkedStub::withAlwaysLinked();

        $this->expectException(GitlabGroupAlreadyExistsException::class);
        $this->createGroup();
    }

    public function testItThrowsIfGivenProjectIsAlreadyLinked(): void
    {
        $this->project_verifier = VerifyProjectIsAlreadyLinkedStub::withAlwaysLinked();

        $this->expectException(ProjectAlreadyLinkedToGitlabGroupException::class);
        $this->createGroup();
    }
}