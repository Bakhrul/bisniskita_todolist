<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class todolistRole extends Model
{
    protected $table= 'd_todolist_roles';
    protected $primaryKey = ['tlr_users','tlr_todolist'];
    public $incrementing = false;

    protected function setKeysForSaveQuery(Builder $query)
{
    $keys = $this->getKeyName();
    if(!is_array($keys)){
        return parent::setKeysForSaveQuery($query);
    }

    foreach($keys as $keyName){
        $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
    }

    return $query;
}

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    function user()
    {
        return $this->belongsTo('App\User');
    }

    function todo()
    {
        return $this->belongsTo('App\Todo','tlr_todolist','tl_id');
    }


}
