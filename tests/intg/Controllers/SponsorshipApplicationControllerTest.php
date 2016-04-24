<?php
namespace intg\Sponsor\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use Sponsor\Models\Sponsorship;
use intg\Sponsor\RequestSignCheck;

class SponsorshipApplicationControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RequestSignCheck;

    /**
     * @before
     */
    public function fixNow()
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2015-11-20'));
    }

    /**
     * @after
     */
    public function unfixNow()
    {
        Carbon::setTestNow(null);
    }

    //=========================================
    //       List sponsorship applications
    //=========================================
    public function testListSponsorshipApplicationsEmpty()
    {
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->ajaxGet('/web/sponsorships/1/applications');

        $this->seeJsonContains(['code' => 10000]);
    }

    public function testListSponsorshipApplicationsHasSome()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 2,
            'sponsorship_id' => 1,
            'team_id'        => 2,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:10:00',
        ]);

        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 3,
            'sponsorship_id' => 1,
            'team_id'        => 3,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:20:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $response = $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->ajaxGet('/web/sponsorships/1/applications?page=1&size=2')->response;

        $response = json_decode($response->content());
        $this->assertEquals(2, $response->totalPages);

        $applications = $response->applications;
        $this->assertCount(2, $response->applications, 'should have 2 sponsorships for the sponsorship');
        $this->assertEquals(3, $applications[0]->id);
        $this->assertEquals(2, $applications[1]->id);
    }

    //=========================================
    //       List applied sponsorships
    //=========================================
    public function testListAppliedSponsorshipsEmpty()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'id'        => 1,
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->actingAs($user, 'extended-eloquent-team')
            ->ajaxGet('/api/user/sponsorships');

        $this->seeJsonContains(['code' => 0]);
        $response = json_decode($this->response->content());
        $this->assertEquals(0, $response->total);
        $this->assertEquals([], $response->sponsorships);
    }

    public function testListAppliedSponsorshipsHasSome()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 2,
            'sponsorship_id' => 2,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_APPROVED,
            'created_at'     => '2015-11-21 00:10:00',
        ]);

        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 3,
            'sponsorship_id' => 3,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_REJECTED,
            'created_at'     => '2015-11-21 00:20:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 2,
            'sponsor_id' => 1,
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 3,
            'sponsor_id' => 1,
        ]);

        $user = factory(\Sponsor\Models\User::class)->create([
            'id'        => 1,
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $response = $this->actingAs($user, 'extended-eloquent-team')->ajaxGet('/api/user/sponsorships?page=1&size=2')->response;

        $response = json_decode($response->content());
        $this->assertEquals(3, $response->total);
        $this->assertCount(2, $response->sponsorships);
    }

    //=========================================
    //       Approve sponsorship application
    //=========================================
    public function testApproveSponsorshipApplicationSuccessfully()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/approve', [
            '_token' => csrf_token(),
            'memo'   => '有条件',
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->seeInDatabase('sponsorship_applications', [
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_APPROVED,
            'memo'           => '有条件',
        ]);
    }

    public function testApproveSponsorshipApplicationFailedApplicationNotExists()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/approve', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不存在此申请']);
    }

    public function testApproveSponsorshipApplicationFailedNoPermission()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 2,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/approve', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorship/applications?sponsorship=1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不能处理此赞助']);
    }

    public function testApproveSponsorshipApplicationFailedHasApproved()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_APPROVED,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/approve', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不能通过赞助']);
    }

    //=========================================
    //       Reject sponsorship application
    //=========================================
    public function testRejectSponsorshipApplicationSuccessfully()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/reject', [
            '_token' => csrf_token(),
            'memo'   => '条件不够',
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->seeInDatabase('sponsorship_applications', [
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_REJECTED,
            'memo'           => '条件不够',
        ]);
    }

    public function testRejectSponsorshipApplicationFailedApplicationNotExists()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/reject', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不存在此申请']);
    }

    public function testRejectSponsorshipApplicationFailedNoPermission()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 2,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/reject', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不能处理此赞助']);
    }

    public function testRejectSponsorshipApplicationFailedHasRejected()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_REJECTED,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/reject', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不能拒绝赞助']);
    }

    public function testRejectSponsorshipApplicationFailedHasApproved()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_APPROVED,
            'created_at'     => '2015-11-21 00:00:00',
        ]);

        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->post('/web/sponsorships/1/applications/1/reject', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1/applications',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1/applications');
        $this->assertSessionHasErrors(['message' => '不能拒绝赞助']);
    }

    //=========================================
    //       Store sponsorship application
    //=========================================
    public function testStoreSponsorshipApplicationSuccessfully()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-19',
            'application_end_date'   => '2015-11-30',
            'status'                 => Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->actingAs($user, 'extended-eloquent-team')
            ->post('/api/sponsorships/1/applications', [
                'team_name'          => '大学',
                'mobile'             => '13800001111',
                'contact_user'       => '朝阳',
                'application_reason' => '申请赞助申请说明',
            ]);

        $this->seeJsonContains(['code' => 0]);
        $this->seeInDatabase('sponsorship_applications', [
            'sponsorship_id'     => 1,
            'team_name'          => '大学',
            'mobile'             => '13800001111',
            'contact_user'       => '朝阳',
            'application_reason' => '申请赞助申请说明',
        ]);
    }

    public function testStoreSponsorshipApplicationFailedIfSponsorshipNotExists()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->startSession();
        $this->actingAs($user, 'extended-eloquent-team')
            ->post('/api/sponsorships/1/applications', [
                'team_name'          => '大学',
                'mobile'             => '13800001111',
                'contact_user'       => '朝阳',
                'application_reason' => '申请赞助申请说明',
            ]);
        $this->seeJsonContains(['code' => 10000, 'message' => '不存在赞助']);
    }

    public function testStoreSponsorshipApplicationFailedWithExpiredSponsorship()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-18',
            'application_end_date'   => '2015-11-19',
            'status'                 => Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->actingAs($user, 'extended-eloquent-team')->post('/api/sponsorships/1/applications', [
            'team_name'          => '大学',
            'mobile'             => '13800001111',
            'contact_user'       => '朝阳',
            'application_reason' => '申请赞助申请说明',
        ]);

        $this->seeJsonContains(['code' => 10000, 'message' => '不能申请已过期赞助']);
    }

    public function testStoreSponsorshipApplicationFailedIfApplicationHasExists()
    {
        factory(\Sponsor\Models\SponsorshipApplication::class)->create([
            'id' => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
            'status'         => \Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-19',
            'application_end_date'   => '2015-11-30',
            'status'                 => Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $user = factory(\Sponsor\Models\User::class)->create([
            'id'        => 1,
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->actingAs($user, 'extended-eloquent-team')
            ->post('/api/sponsorships/1/applications', [
                'team_name'          => '大学',
                'mobile'             => '13800001111',
                'contact_user'       => '朝阳',
                'application_reason' => '申请赞助申请说明',
            ]);

        $this->seeJsonContains(['code' => 10000, 'message' => '不能创建申请']);
    }
}
