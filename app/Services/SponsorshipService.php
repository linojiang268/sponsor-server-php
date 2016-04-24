<?php
namespace Sponsor\Services;

use Carbon\Carbon;
use Sponsor\Models\Sponsor;
use Sponsor\Models\Sponsorship;
use Sponsor\Utils\PaginationUtil;

class SponsorshipService
{
    /**
     * @param Sponsorship $sponsorship
     * @param             $postponeTo
     *
     * @throws \Exception
     */
    public function postponeApplication(Sponsorship $sponsorship, $postponeTo)
    {
        if (!$this->canPostponeApplicationTo($sponsorship, $postponeTo)) {
            throw new \Exception('不能延期该赞助');
        }

        $sponsorship->application_end_date = $postponeTo;
        $sponsorship->save();
    }

    public function listSponsorshipsFor(Sponsor $sponsor, $page, $size)
    {
        $query = Sponsorship::where('sponsor_id', $sponsor->getAuthIdentifier());
        $total = $query->count();

        $page = PaginationUtil::sanePage($total, $page, $size);
        $sponsorships = $query->orderBy('application_start_date', 'desc')
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $size)
            ->take($size)
            ->get();

        return [$sponsorships, $total];
    }

    /**
     * @param Sponsorship $sponsorship
     * @param array       $attributes   keys taken:
     *                                  - name
     *                                  - intro
     *                                  - application_start_date
     *                                  - application_end_date
     *                                  - application_condition
     *
     * @return bool
     * @throws \Exception
     */
    public function update(Sponsorship $sponsorship, $attributes)
    {
        if (!$this->canUpdate($sponsorship)) {
            throw new \Exception('不能修改赞助');
        }

        return 1 == $sponsorship->update(array_filter([
            'name'                   => array_get($attributes, 'name'),
            'intro'                  => array_get($attributes, 'intro'),
            'application_start_date' => array_get($attributes, 'application_start_date'),
            'application_end_date'   => array_get($attributes, 'application_end_date'),
            'application_condition'  => array_get($attributes, 'application_condition'),
            'status'                 => array_get($attributes, 'status', 0),
        ]));
    }

    /**
     * @param Sponsorship $sponsorship
     *
     * @return bool
     * @throws \Exception
     */
    public function close(Sponsorship $sponsorship)
    {
        if (!$this->canClose($sponsorship)) {
            throw new \Exception('不能关闭赞助');
        }

        $sponsorship->status = Sponsorship::STATUS_CLOSED;

        return $sponsorship->save();
    }

    /**
     * @param Sponsorship $sponsorship
     *
     * @return bool
     * @throws \Exception
     */
    public function destroy(Sponsorship $sponsorship)
    {
        if (!$this->canDestroy($sponsorship)) {
            throw new \Exception('不能删除赞助');
        }

        return $sponsorship->delete();
    }

    public function canApply(Sponsorship $sponsorship)
    {
        // only published sponsorship can be applied
        if ($sponsorship->status != Sponsorship::STATUS_PUBLISHED) {
            return false;
        }

        // now time can't over application_end_date
        return Carbon::now()->format('Y-m-d') < $sponsorship->application_end_date;
    }

    public function canPostponeApplicationTo(Sponsorship $sponsorship, $postponeTo)
    {
        // only published sponsorship can have its effective application
        // deadline postponed
        if ($sponsorship->status != Sponsorship::STATUS_PUBLISHED) {
            return false;
        }

        // deadline can't be no earlier that the start date
        return $sponsorship->application_start_date <= $postponeTo;
    }

    public function canUpdate(Sponsorship $sponsorship)
    {
        // only no-published sponsorship can be updated
        return $sponsorship->status == Sponsorship::STATUS_PENDING;
    }

    public function canDestroy(Sponsorship $sponsorship)
    {
        // only no-published sponsorship can be destroyed
        return $sponsorship->status == Sponsorship::STATUS_PENDING;
    }

    public function canClose(Sponsorship $sponsorship)
    {
        // only published sponsorship can be closed
        return $sponsorship->status == Sponsorship::STATUS_PUBLISHED;
    }
}