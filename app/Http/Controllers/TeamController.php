<?php
namespace Sponsor\Http\Controllers;

use Illuminate\Http\Request;
use Sponsor\Services\TeamService;
use Validator;
use Sponsor\Entities\Team;
use Illuminate\Contracts\Auth\Guard;

class TeamController extends Controller
{
    /**
     * get list of teams
     */
    public function getTeams(Request $request, TeamService $teamService)
    {
        $this->validate($request, [
            'name' => 'max:32',
            'page' => 'integer',
            'size' => 'integer',
        ], [
            'name.max'      => '社团名称错误',
            'page.integer'  => '分页page错误',
            'size.integer'  => '分页size错误',
        ]);
        
        try {
            list($page, $size) = $this->sanePageAndSize($request);
            list($pages, $teams) = $teamService->getTeams(
                                                 $page, $size,
                                                 [
                                                     'name'      => $request->input('name'),
                                                     'forbidden' => false,
                                                 ]);
            
            return $this->json([
                                'pages' => $pages,
                                'teams' => $this->getTeamWithRelateAttributes($teams),
                              ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }
    
    private function getTeamWithRelateAttributes($teams)
    {
        if (0 == count($teams)) {
            return [];
        }

        // init array of team ids and array of team attributes
        $teamWithAttributes = [];
        /* @var $team \Sponsor\Entities\Team */
        foreach ($teams as $team) {
            array_push($teamWithAttributes, [
                'id'            => is_array($team) ? $team['id'] : $team->getId(),
                'creator_id'    => is_array($team) ? $team['creator_id'] : $team->getCreator()->getId(),
                'name'          => is_array($team) ? $team['name'] : $team->getName(),
                'introduction'  => is_array($team) ? $team['introduction'] : $team->getIntroduction(),
            ]);
        }

        return array_values($teamWithAttributes);
    }

    /**
     * 
     * @param Team $team     \Jihe\Entities\Team
     * @return array
     */
    private function morphToTeamArray(Team $team)
    {
        if (empty($team)) {
            return null;
        }

        return [
                'id'                    => $team->getId(),
                'name'                  => $team->getName(),
                'introduction'          => $team->getIntroduction(),
        ];
    }
    
    /**
     * get info of team
     * 
     * @param Request $request
     * @param TeamService $teamService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeam(Request $request, TeamService $teamService, Guard $auth)
    {
        // validate request
        $validator = Validator::make($request->only('team'), [
            'team' => 'required|integer',
        ], [
            'team.required' => '社团未填写',
            'team.integer'  => '社团错误',
        ]);
    
        /* @var $validator \Illuminate\Validation\Validator  */
        if ($validator->fails()) {
            return $this->jsonException($validator->errors()->first());
        }
    
        try {
            /* @var $authUser \Sponsor\Entities\User  */
            $team = $teamService->getTeam($request->input('team'));
            $teamAttributes = $this->morphToTeamArray($team);

            return $this->json($teamAttributes);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }
}