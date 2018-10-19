<?php

namespace App\Transformers;

use App\Post;
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
            'venue'             => $post->venue->name,
            'created_at'        => Carbon::parse($post->created_at)->toDateTimeString(),
            'media_type'        => $post->media_type,
            'media_url'         => $post->media_url,
            'liked'             => $this->user->likesPost($post->id),
            'shared'            => (bool) $post->shared,
            'friend_post'       => (bool) in_array($post->user_id,$this->user->following->toArray()),
            'comment'           => $post->comment,
            'status'            => $post->status == null ? 1 : $post->status,
        ];
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        $this->user = User::find($this->user_id);
    }

}
