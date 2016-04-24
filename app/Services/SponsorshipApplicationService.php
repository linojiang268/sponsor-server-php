<?php
namespace Sponsor\Services;

use Sponsor\Models\Sponsor;
use Sponsor\Models\Sponsorship;
use Sponsor\Models\SponsorshipApplication;
use Sponsor\Utils\PaginationUtil;

class SponsorshipApplicationService
{
    public function store(SponsorshipApplication $application)
    {
        if (!$this->canStore($application)) {
            throw new \Exception('不能创建申请');
        }

        return $application->save();
    }

    public function approve(SponsorshipApplication $application, $memo)
    {
        if (!$this->canApprove($application)) {
            throw new \Exception('不能通过赞助');
        }

        $application->status = SponsorshipApplication::STATUS_APPROVED;
        $application->memo   = $memo;
        return $application->save();
    }

    public function reject(SponsorshipApplication $application, $memo)
    {
        if (!$this->canReject($application)) {
            throw new \Exception('不能拒绝赞助');
        }

        $application->status = SponsorshipApplication::STATUS_REJECTED;
        $application->memo   = $memo;
        return $application->save();
    }

    public function canStore(SponsorshipApplication $application)
    {
        // the sponsorship ever not be applied by given team
        return null == SponsorshipApplication::where('team_id', $application->team_id)
            ->where('sponsorship_id', $application->sponsorship_id)
            ->first();
    }

    public function canApprove(SponsorshipApplication $application)
    {
        // sponsorship application is not approved
        return $application->status != SponsorshipApplication::STATUS_APPROVED;
    }

    public function canReject(SponsorshipApplication $application)
    {
        // sponsorship application is not rejected
        return $application->status == SponsorshipApplication::STATUS_PENDING;
    }
}