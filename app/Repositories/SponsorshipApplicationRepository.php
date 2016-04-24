<?php
namespace Sponsor\Repositories;

use Sponsor\Contracts\Repositories\SponsorshipApplicationRepository as SponsorshipApplicationRepositoryContract;
use Sponsor\Models\SponsorshipApplication;
use Sponsor\Utils\PaginationUtil;


class SponsorshipApplicationRepository implements SponsorshipApplicationRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function findApplication($applicationId)
    {
        return $sponsorship = SponsorshipApplication::where('id', $applicationId)
                                                    ->first();
    }

    /**
     * @inheritDoc
     */
    public function findApplications($sponsorshipId, $page, $size)
    {
        /** @var  $query \Illuminate\Database\Eloquent\Builder */
        $query = SponsorshipApplication::where('sponsorship_id', $sponsorshipId);
        $total = $query->count();

        $page = PaginationUtil::sanePage($total, $page, $size);
        $applications = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get()->all();

        return [$applications, $total];
    }

    /**
     * @inheritDoc
     */
    public function findApplicationsOf($team, $page, $size)
    {
        /** @var  $query \Illuminate\Database\Eloquent\Builder */
        $query = SponsorshipApplication::where('team_id', $team)
            ->with('sponsorship');
        $total = $query->count();

        $page = PaginationUtil::sanePage($total, $page, $size);
        $applications = $query->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->forPage($page, $size)
            ->get()->all();

        return [$total, $applications];
    }

    /**
     * @inheritDoc
     */
    public function findApplicationStatus(array $sponsorshipIds, $team)
    {
        $stat = array_fill_keys($sponsorshipIds, -1);

        $applications = SponsorshipApplication::where('team_id', $team)
            ->whereIn('sponsorship_id', $sponsorshipIds)
            ->get()->all();

        foreach ($applications as $application) {
            $stat[$application->sponsorship_id] = $application->status;
        }
        return $stat;
    }
}