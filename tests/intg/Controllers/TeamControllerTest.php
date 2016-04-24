<?php
namespace intg\Sponsor\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;

use intg\Sponsor\TestCase;
use intg\Sponsor\RequestSignCheck;

class TeamControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RequestSignCheck;

    //=========================================
    //                teams
    //=========================================
    public function testSuccessfulTeams()
    {
        $user = factory(\Sponsor\Models\User::class)->create();
        factory(\Sponsor\Models\Team::class)->create([
            'id'      => 1,
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
             ->get('/api/team/list');
        $this->seeJsonContains([ 'code' => 0 ]);
        $result = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('pages', $result);
        $this->assertObjectHasAttribute('teams', $result);
        $team = $result->teams[0];
        $this->assertObjectHasAttribute('id', $team);
        $this->assertObjectHasAttribute('name', $team);
        $this->assertObjectHasAttribute('introduction', $team);
    }
    
    public function testSuccessfulTeams_Multi()
    {
        $user = factory(\Sponsor\Models\User::class)->create();
        factory(\Sponsor\Models\Team::class)->create([
            'id'      => 1,
            'name'    => 'team1',
        ]);
        factory(\Sponsor\Models\Team::class)->create([
            'id'      => 2,
            'name'    => 'team2',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
             ->get('/api/team/list');
        $this->seeJsonContains([ 'code' => 0 ]);
        $result = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('pages', $result);
        $this->assertObjectHasAttribute('teams', $result);
        $team = $result->teams[0];
        $this->assertObjectHasAttribute('id', $team);
        $this->assertObjectHasAttribute('name', $team);
        $this->assertObjectHasAttribute('introduction', $team);
    }


    public function testSuccessfulTeams_selectName()
    {
        $user = factory(\Sponsor\Models\User::class)->create();
        factory(\Sponsor\Models\Team::class)->create([
            'id'      => 1,
            'name'    => 'team1',
        ]);
        factory(\Sponsor\Models\Team::class)->create([
            'id'      => 2,
            'name'    => 'team2',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
            ->get('/api/team/list?name=team1');
        $this->seeJsonContains([ 'code' => 0 ]);
        $result = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('pages', $result);
        $this->assertObjectHasAttribute('teams', $result);
        $team = $result->teams[0];
        $this->assertObjectHasAttribute('id', $team);
        $this->assertObjectHasAttribute('name', $team);
        $this->assertObjectHasAttribute('introduction', $team);
        $this->assertEquals('team1', $team->name);
    }

    //=========================================
    //                team
    //=========================================
    public function testSuccessfulTeam()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile' => '13800001111',
        ]);
        factory(\Sponsor\Models\Team::class)->create([
            'id'         => 1,
            'creator_id' => 1,
        ]);
        factory(\Sponsor\Models\User::class)->create([
            'id' => 1,
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
             ->get('/api/team/detail?team=1');
        $this->seeJsonContains([ 'code' => 0 ]);
        $team = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('id', $team);
        $this->assertObjectHasAttribute('name', $team);
        $this->assertObjectHasAttribute('introduction', $team);
    }

}
