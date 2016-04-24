<?php
namespace Sponsor\Repositories;

use Sponsor\Contracts\Repositories\TeamRepository as TeamRepositoryContract;
use Sponsor\Models\Team;
use Sponsor\Entities\Team as TeamEntity;
use Sponsor\Utils\SqlUtil;

class TeamRepository implements TeamRepositoryContract
{
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::getNumberOfTeamsCreatedBy()
     */
    public function getNumberOfTeamsCreatedBy($creator)
    {
        return Team::where('creator_id', $creator)->count();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::findTeam()
     */
    public function findTeam($team, $relations = [])
    {
        return $this->convertToEntity($this->findTeamModel($team, $relations));
    }
    
    /**
     * @param int $team
     * @return \Sponsor\Models\Team
     */
    private function findTeamModel($team, $relations = [])
    {
        $query = Team::where('id', $team);
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->first();
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::exists()
     */
    public function exists($team)
    {
        return null !== Team::where('id', $team)->value('id');
    }
    
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::findTeamsCreatedBy()
     */
    public function findTeamsCreatedBy($creator, $relations = [])
    {
        $query = Team::where('creator_id', $creator);
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return array_map([ $this, 'convertToEntity' ], $query->get()->all());
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::findTeams()
     */
    public function findTeams($page, $size, array $relations = [], array $criteria = [])
    {
        $query = Team::orderBy('updated_at', 'desc')
                     ->orderBy('id', 'desc');
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        if (!empty($name = array_get($criteria, 'name'))) {
            $query->where('name', 'like', '%' . SqlUtil::escape($name) . '%');
        }
        
        if ($city = array_get($criteria, 'city')) {
            $query->where('city_id', $city);
        }
        
        if (null !== ($tagged = array_get($criteria, 'tagged'))) {
            if ($tagged) {
                $query->whereNotNull('tags');
            } else {
                $query->whereNull('tags');
            }
        }
        
        if (null !== ($freeze = array_get($criteria, 'freeze'))) {
            if ($freeze) {
                $query->where('status', TeamEntity::STATUS_FREEZE);
            } else {
                $query->where('status', '<>', TeamEntity::STATUS_FREEZE);;
            }
        }
        
        if (null !== ($forbidden = array_get($criteria, 'forbidden'))) {
            if ($forbidden) {
                $query->where('status', TeamEntity::STATUS_FORBIDDEN);
            } else {
                $query->where('status', '<>', TeamEntity::STATUS_FORBIDDEN);;
            }
        }
        
        $total = $query->getCountForPagination()->count();
        $pages = ceil($total / $size);
        if ($page > $pages) {
            $page = $pages;
        }
        
        return [$pages, array_map([ $this, 'convertToEntity' ], 
                                  $query->forPage($page, $size)->get()->all())];
    }

    /**
     *
     * @param \Sponsor\Models\Team $model
     * @return \Sponsor\Entities\Team|null
     */
    private function convertToEntity(Team $model = null)
    {
        if ($model == null) {
            return null;
        }

        return $model->toEntity();
    }
    
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::add()
     */
    public function add($team)
    {
        $team->setId(null);
        return Team::create($this->convertToModelArr($team))->id;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::update()
     */
    public function update($team)
    {
        $teamId = $team->getId();
        return 1 == Team::where('id', $teamId)
                        ->where('status', TeamEntity::STATUS_NORMAL)
                        ->update($this->convertToModelArr($team));
    }
    
    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::updateProperty()
     */
    public function updateProperties($team, $properties = [])
    {
        $attributes = [];
        
        if (!is_null($status = array_get($properties, 'status'))) {
            array_set($attributes, 'status', $status);
        }

        if (array_key_exists('tags', $properties)) {
            $tags = array_get($properties, 'tags');
            array_set($attributes, 'tags', $tags ? json_encode($tags) : null);
        }
        
        return 1 == Team::where('id', $team)
                        ->update($attributes);
    }

    /**
     *
     * @param \Sponsor\Entities\Team $team
     * @return array
     */
    private function convertToModelArr($team)
    {
        return array_filter([
                'creator_id'    => $team->getCreator()->getId(),
                'name'          => $team->getName(),
                'email'         => $team->getEmail(),
                'address'       => $team->getAddress(),
                'contact_phone' => $team->getContactPhone(),
                'contact'       => $team->getContact(),
                'introduction'  => $team->getIntroduction(),
                'status'        => $team->getStatus(),
        ], function ($value) {
            return !is_null($value);
        });
    }

    /**
     * (non-PHPdoc)
     * @see \Sponsor\Contracts\Repositories\TeamRepository::findTeamsOf()
     */
    public function findTeamsOf($teams = [], array $relations = [])
    {
        if (empty($teams)) {
            return [0, []];
        }

        $query = Team::whereIn('id', $teams)
                     ->orderBy('id', 'desc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        $total = $query->count();

        return [
            $total,
            array_map(
                [ $this, 'convertToEntity' ],
                $query->get()->all())
        ];
    }
}
