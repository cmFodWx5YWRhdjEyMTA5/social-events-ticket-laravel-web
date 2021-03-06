<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Venue extends Model
{
    use Sluggable;
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        //we add a slug url to the name
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
    protected $fillable = ['name', 'town_id', 'latitude', 'longitude', 'contact_person_name','contact_person_phone','contact_person_email',];

    public function follow($user_id)
    {
        return $this->following()->attach($user_id,['created_at'=>now(),'updated_at'=> now()]);
    }

    public function unfollow($user_id)
    {
        return $this->following()->detach($user_id);
    }

    public function followed($user_id)
    {
        return $this->following()->where('user_venues.user_id',$user_id)->first();
    }

    public function following() //users following this venue
    {
        return $this->belongsToMany('App\User','user_venues','venue_id','user_id');
    }

    public function posts()
    {
        return $this->hasMany('App\Post','venue_id');
    }
}
