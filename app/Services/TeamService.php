<?php
namespace Sponsor\Services;

use Sponsor\Contracts\Repositories\TeamRepository;
use Sponsor\Entities\Team;

class TeamService
{
    /**
     * a team leader is restricted to create at most one team.
     */
    const MAX_ALLOWED_CREATED_TEAMS = 1;

    /**
     * detail of a team can only be updated once
     */
    const MAX_ALLOWED_UPDATED_TIMES = 10;
    
    /**
     * the size of team qrcode generaled
     */
    const TEAM_QRCODE_SIZE = 500;
    
    /**
     * the qrcode logo scale size of generaled
     */
    const TEAM_QRCODE_LOGO_SCALE_SIZE = 100;
    
    /**
     * @var \Sponsor\Contracts\Repositories\TeamRepository
     */
    private $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * update some info of team
     *
     * @param array $updateTeam  detail for team update, keys taken:
     *                              - team          mandatory (int) id of the team to be updated
     *                              - contact_phone (string) contact number
     *                              - contact       (string) name of the contact
     *                              - contact_hidden (boolean) whether hide contact of team
     * @return boolean
     * @throws \Exception
     */
    public function update(array $updateTeam)
    {
        $team = $this->getUpdatableTeam(array_get($updateTeam, 'team'));
        if (is_null($team)) {
            throw new \Exception('社团不存在');
        }
        $team->setContactPhone(
            array_get($updateTeam, 'contact_phone', $team->getContactPhone()));
        $team->setContact(
            array_get($updateTeam, 'contact', $team->getContact()));
        $team->setContactHidden(
            array_get($updateTeam, 'contact_hidden', $team->getContactHidden()));

        return $this->teamRepository->update($team);
    }

    /**
     * get teams by given id of team creator
     * 
     * @param int $creator  id of team creator
     * @return array        array of \Jihe\Entities\Team
     */
    public function getTeamsByCreator($creator)
    {
        return $this->teamRepository->findTeamsCreatedBy($creator, []);
    }
    
    /**
     * get teams
     *
     * @param int $page            the offset page of teams
     * @param int $size            the limit of teams size
     * @param array $criteria|[]   criteria, keys:
     *                               - city|null  (int)city of teams
     *                               - name|null  keywords of team's name used by search
     *                               - tagged     boolean, null(default)
     *                               - freeze     boolean, null(default)
     *                               - forbidden  boolean, null(default)
     * @return array array  of \Sponsor\Entities\Team
     */
    public function getTeams($page, $size, array $criteria = [])
    {
        return $this->teamRepository->findTeams($page, $size, [], $criteria);
    }
    
    /**
     * get team info by given id of team
     * 
     * @param int $team  id of team
     * @return \Sponsor\Entities\Team
     */
    public function getTeam($team)
    {
        return $this->teamRepository->findTeam($team, ['creator']);
    }

    /**
     * check whether given team exists
     *
     * @param int $team   id of team
     * @return bool       true if team exists. false otherwise
     */
    public function exists($team)
    {
        return $this->teamRepository->exists($team);
    }

    /**
     *
     * @param int $team             id of team
     * @throws \Exception
     * @return \Sponsor\Entities\Team
     */
    private function getUpdatableTeam($team)
    {
        $team = $this->teamRepository->findTeam($team);
    
        // rule#1. illegal if team is not exists
        if (null == $team) {
            throw new \Exception('社团不存在');
        }
    
        // rule#2. illegal if team is not in NORMAL state
        if (Team::STATUS_NORMAL != $team->getStatus()) {
            throw new \Exception('社团资料不可更新');
        }
    
        return $team;
    }

    /**
     * check whether the team is owned by given creator
     * @param $user
     * @param $team
     *
     * @return bool   true if user can manipulate the team, false otherwise.
     */
    public function canManipulate($user, $team)
    {
        if (null === $team = $this->teamRepository->findTeam($team)) {
            return false; // you cannot manipulate something that does not exist
        }

        // only team's creator can manipulate his/her team
        return $team->getCreator()->getId() == $user;
    }
    
    /**
     * update team's properties
     * 
     * @param int $team
     */
    public function updateProperties($team, array $properities)
    {
        $result = $this->teamRepository->updateProperties($team, $properities);
        
        return $result;
    }

    /**
     *
     * @param int $team           id of team
     * @param array $notices      array of $notices, values taken:
     *                             - activities
     *                             - members
     *                             - news
     *                             - albums
     *                             - notices
     * @return boolean
     */
    public function notify($team, array $notices = [])
    {
        return $this->teamRepository->updateNotifiedAt($team, $notices);
    }

    /**
     * get teams of given team ids
     *
     * @param array $teams
     *
     * @return array
     */
    public function getTeamsOf(array $teams = [])
    {
        return $this->teamRepository->findTeamsOf($teams);
    }
}
