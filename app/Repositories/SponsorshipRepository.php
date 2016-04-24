<?php
namespace Sponsor\Repositories;

use Carbon\Carbon;
use Sponsor\Contracts\Repositories\SponsorshipRepository as SponsorshipRepositoryContract;
use Sponsor\Models\Sponsorship;
use Sponsor\Utils\PaginationUtil;


class SponsorshipRepository implements SponsorshipRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function findSponsorshipFor($sponsorId, $sponsorShipId)
    {
        return Sponsorship::where('sponsor_id', $sponsorId)
            ->where('id', $sponsorShipId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function findSponsorship($sponsorshipId)
    {
        return Sponsorship::where('id', $sponsorshipId)->first();
    }

    /**
     * @inheritDoc
     */
    public function findSponsorships($page, $size, $criteria = [])
    {
        $query = Sponsorship::orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        if (!empty(array_get($criteria, 'relations', []))) {
            $query->with(array_get($criteria, 'relations'));
        }

        if (array_key_exists('sponsor', $criteria)) {
            $query->where('sponsor_id', array_get($criteria, 'sponsor'));
        }

        if (!array_get($criteria, 'with_expired', true)) {
            $query->where('application_end_date', '>', Carbon::now()->format('Y-m-d'));
        }

        if (array_get($criteria, 'only_published', false)) {
            $query->where('status', Sponsorship::STATUS_PUBLISHED);
        }

        $total = $query->count();
        $page = PaginationUtil::sanePage($total, $page, $size);

        return [
            $total,
            $query->forPage($page, $size)->get()->all(),
        ];
    }
}
