<?php
namespace intg\Sponsor\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use Sponsor\Models\Team;
use Sponsor\Models\User;
use Sponsor\Entities\Team as TeamEntity;
use Sponsor\Entities\User as UserEntity;

class TeamRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //===========================================
    //       getNumberOfTeamsCreatedBy
    //===========================================
    public function testGetNumberOfTeamsCreatedBy_OnlyOneTeamCreated()
    {
        factory(Team::class)->create([
            'creator_id'   => 1,
            'status'       => TeamEntity::STATUS_NORMAL,
        ]);
        
        self::assertEquals(1, $this->getRepository()->getNumberOfTeamsCreatedBy(1));
    }
    
    public function testGetNumberOfTeamsCreatedBy_TwoTeamCreatedButOneDeleted()
    {
        factory(Team::class)->create([
            'creator_id'   => 1,
            'status'       => TeamEntity::STATUS_NORMAL
        ]);
        
        factory(Team::class)->create([
            'creator_id'   => 1,
            'status'       => TeamEntity::STATUS_NORMAL,
            'deleted_at'   => '2015-02-01 00:00:00',
        ]);
        
        self::assertEquals(1, $this->getRepository()->getNumberOfTeamsCreatedBy(1));
    }
    
    public function testGetNumberOfTeamsCreatedBy_NoTeamCreated()
    {
        self::assertEquals(0, $this->getRepository()->getNumberOfTeamsCreatedBy(1));
    }
    
    //===========================================
    //              findTeam
    //===========================================
    public function testFindTeam_TeamExists()
    {
        factory(Team::class)->create([
            'id'         => 1,
            'creator_id' => 1,
        ]);
        
        factory(User::class)->create([
            'id' => 1,
        ]);
        
        $team = $this->getRepository()->findTeam(1, ['creator']);
        self::assertTeam(1, 1, 1, $team);
    }
    
    public function testFindTeam_TeamNotExists()
    {
        self::assertNull($this->getRepository()->findTeam(1));
    }
    
    private static function assertTeam($expectedId, $expectedCreatorId, $expectedCityId, TeamEntity $team)
    {
        self::assertNotNull($team);
        self::assertEquals($expectedId, $team->getId());
        self::assertEquals($expectedCreatorId, $team->getCreator()->getId());
    }
    
    //===========================================
    //             findTeamsByCreator
    //===========================================
    public function testFindTeamsByCreator_TeamHasCreated()
    {
        factory(Team::class)->create([
            'id'         => 1,
            'creator_id' => 1,
        ]);
    
        factory(User::class)->create([
            'id' => 1,
        ]);
    
        $teams = $this->getRepository()->findTeamsCreatedBy(1, []);
        self::assertCount(1, $teams);
        self::assertTeam(1, 1, 1, $teams[0]);
    }
    
    public function testFindTeamsByCreator_NoTeamHasCreated()
    {
        self::assertEmpty($this->getRepository()->findTeamsCreatedBy(1));
    }
    
    //===========================================
    //                findTeams
    //===========================================
    public function testFindTeams_AllTeams()
    {
        for ($i = 0; $i < 5; $i++) {
            factory(Team::class)->create([
                'id'         => $i + 1,
                'creator_id' => 1,
            ]);
        }
    
        list($pages, $teams) = $this->getRepository()->findTeams(1, 10, [], []);

        self::assertEquals(1, $pages);
        self::assertCount(5, $teams);
        for ($i = 0; $i < 5; $i++) {
            self::assertTeam(5 - $i, 1, 1, $teams[$i]);
        }
    }
    
    public function testFindTeams_SecondPageTeams()
    {
        for ($i = 0; $i < 5; $i++) {
            factory(Team::class)->create([
                'id'         => $i + 1,
                'creator_id' => 1,
            ]);
        }
        
        list($pages, $teams) = $this->getRepository()->findTeams(2, 3, [], []);
        
        self::assertEquals(2, $pages);
        self::assertCount(2, $teams);
        for ($i = 0; $i < 2; $i++) {
            self::assertTeam(2 - $i, 1, 1, $teams[$i]);
        }
    }
    
    public function testFindTeams_NoTeams()
    {
        list($pages, $teams) = $this->getRepository()->findTeams(1, 3, [], []);
        self::assertEquals(0, $pages);
        self::assertEmpty($teams);
    }
    
    public function testFindTeams_SearchTeamsByName()
    {
        factory(Team::class)->create([
            'id'         => 1,
            'name'       => '社团第一个',
            'creator_id' => 1,
        ]);
        factory(Team::class)->create([
            'id'         => 2,
            'name'       => '社团第二个',
            'creator_id' => 1,
        ]);
        
        list($pages, $teams) = $this->getRepository()->findTeams(1, 3, [], ['name' => '第一']);
        
        self::assertEquals(1, $pages);
        self::assertCount(1, $teams);
        self::assertTeam(1, 1, 1, $teams[0]);
    }
    
    //===========================================
    //                exists
    //===========================================
    public function testExists_TeamExists()
    {
        factory(Team::class)->create([
            'id' => 1,
        ]);

        self::assertTrue($this->getRepository()->exists(1));
    }

    public function testExists_TeamNotExists()
    {
        self::assertFalse($this->getRepository()->exists(1));
    }
    
    //===========================================
    //                update
    //===========================================
    public function testUpdate_TeamExists()
    {
        factory(Team::class)->create([
                'id' => 1,
                'creator_id' => 1,
        ]);
        
        factory(User::class)->create([
                'id' => 1,
        ]);
        
        $team = new TeamEntity();
        $team->setId(1)
             ->setCreator((new UserEntity())->setId(1))
             ->setName('new team name');
    
        self::assertTrue($this->getRepository()->update($team));
    }
    
    public function testUpdate_TeamNotExists()
    {
        $team = new TeamEntity();
        $team->setId(1)
             ->setCreator((new UserEntity())->setId(1))
             ->setName('new team name');

        self::assertFalse($this->getRepository()->update($team));
    }
    
    //===========================================
    //              update properties
    //===========================================
    public function testUpdateProperties()
    {
        factory(Team::class)->create([
            'id' => 1,
            'creator_id' => 1,
            'status' => TeamEntity::STATUS_NORMAL,
        ]);
    
        factory(User::class)->create([
            'id' => 1,
        ]);
    
        self::assertTrue($this->getRepository()->updateProperties(1, ['status' => TeamEntity::STATUS_FORBIDDEN]));
        
        $this->seeInDatabase('teams', [
            'id' => 1,
            'creator_id' => 1,
            'status' => 1,
        ]);
    }

    //===========================================
    //              findTeamsOf
    //===========================================
    public function testFindTeamsOf()
    {
        for ($i = 0; $i < 5; $i++) {
            factory(Team::class)->create([
                'id'                    => $i + 1,
                'creator_id'            => 1,
            ]);
        }

        list($total, $teams) = $this->getRepository()->findTeamsOf([2, 3, 4]);

        self::assertEquals(3, $total);
        self::assertCount(3, $teams);
        for ($i = 0; $i < 3; $i++) {
            self::assertTeam(4 - $i, 1, 1, $teams[$i]);
        }
    }

    /**
     * @return \Sponsor\Contracts\Repositories\TeamRepository
     */
    private function getRepository()
    {
        return $this->app[\Sponsor\Contracts\Repositories\TeamRepository::class];
    }
}
