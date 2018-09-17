<?php

namespace App\Api\V1\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

trait HandlesUsersOnlineStatus
{
    /**
    * time in minutes it takes for user online status cache to expire
    *
    * @var int $expiresAt
    */
    public $minutes;

    /**
    * get all online users
    *
    * @return object
     */
    public function allOnline()
    {
        return $this->all()->filter->isOnline();
    }
    /**
    * check if a user is online (by checking the cache for the user's key)
    *
    * @return boolean
     */
    public function isOnline()
    {
         return Cache::has($this->getCacheKey());
    }
    /**
    * get the time a key was written to cache
    *
    * @return string
     */
    public function getCachedAt()
    {
        if (empty($cache = Cache::get($this->getCacheKey()))) {
            return 0;
        }
        return $cache['cachedAt'];
    }
    /**
    * set cache
    *
    * @param int $minutes
    *
    * @return boolean
     */
    public function setCache($minutes = 5)
    {
        $this->minutes = $minutes;
        $expiresAt = Carbon::now()->addMinutes($this->minutes);
        return Cache::put($this->getCacheKey(), $this->getCacheContent(), $expiresAt);
    }
    /**
    * get content of cache
    *
    * @return array
     */
    public function getCacheContent()
    {
        if (!empty($cache = Cache::get($this->getCacheKey()))) {
            return $cache;
        }
        $cachedAt = Carbon::now();
        return [
            'cachedAt' => $cachedAt,
            'user' => $this,
        ];
    }
    /**
    * delete cache by key
    *
    * @return void
     */
    public function pullCache()
    {
        Cache::pull($this->getCacheKey());
    }
    /**
    * get cache key (concat user-is-online- and user slug)
    *
    * @return string
     */
    public function getCacheKey()
    {
        //user-is-online-user1681987
        return sprintf('%s-%s', "user-is-online", $this->slug);
    }
}
