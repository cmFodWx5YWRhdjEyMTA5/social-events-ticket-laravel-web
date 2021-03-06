<?php

namespace App\Transformers;

use App\Post;
use App\Share;
use App\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    private $user_id;
    private $user;

    /**
     * A Fractal transformer.
     *
     * @param \App\Post $post
     *
     * @return array
     */
    public function transform(Post $post)
    {
        return [
            'id'                => $post->id,
            'username'          => $post->user->username,
            'creator_id'        => $post->user->id,
            'venue'             => $post->venue->name,
            'venue_id'          => $post->venue->id,
            'media_type'        => $post->media_type,
            'media_url'         => $post->media_url,
            'type'              => $post->type, //1:everyone 2:friends 3: venue
            'liked'             => $this->user->likesPost($post->id),
            'shared'            => $post->shared($this->user_id),
            'everyone_view'     => $post->original_post() && $post->type == 1 && $post->status == 1,
            'friends_view'      => $post->my_shared_post($this->user_id) || ($post->original_post() == true && $post->type == 2) || $post->friends_post($this->user_id),
            'venue_profile_view'    => ($post->original_post() && ($post->type == 3 || $post->type == 1)),
            'my_shared_post'    => $post->my_shared_post($this->user_id),
            'friend_post'       => $post->friends_post($this->user_id),
            'original_post'     => $post->original_post(),
//            'my_friends_only_post' => $post->original_post() == true && $post->type == 2,
            'comment'           => $post->comment,
            'status'            => $post->status == null ? 1 : $post->status,
            'created_at'        => Carbon::parse($post->created_at)->toDateTimeString(),
            'updated_at'        => Carbon::parse($post->updated_at)->toDateTimeString(),
        ];
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        $this->user = User::find($this->user_id);
    }

}
