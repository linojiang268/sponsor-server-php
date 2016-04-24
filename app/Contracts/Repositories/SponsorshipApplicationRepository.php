<?php
namespace Sponsor\Contracts\Repositories;

interface SponsorshipApplicationRepository
{
    /**
     * find sponsorship application by given application id
     * @param $applicationId     id of the sponsorship application
     * @return \Sponsor\Models\SponsorshipApplication|null
     */
    public function findApplication($applicationId);

    /**
     * find sponsorship applications
     *
     * @param $sponsorship
     * @param $page
     * @param $size
     * @return mixed
     */
    public function findApplications($sponsorship, $page, $size);

    /**
     * find applications applied by given team
     *
     * @param $team
     * @param $page
     * @param $size
     * @return mixed
     */
    public function findApplicationsOf($team, $page, $size);

    /**
     * check whether given team applied for the sponsorships, return array of
     * aponsorship ids that key is sponsorship id and value is application
     * status if team applied the sponsorship, otherwise value is -1 if team not
     * applied the sponsorship
     *
     * @param array $sponsorshipIds  array of sponsorship ids
     * @param       $team            id of team
     * @return mixed
     */
    public function findApplicationStatus(array $sponsorshipIds, $team);
}
