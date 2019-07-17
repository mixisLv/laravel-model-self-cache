<?php

namespace mixisLv\SelfCache\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait SelfCache
 *
 * @implements Illuminate\Database\Eloquent\Model
 *
 * @property int $selfCacheKeyExpiration
 * @property int $selfCacheKeyId
 */
trait SelfCache
{
    /**
     * @var string  can be overridden on the base class (model::$selfCacheKeyId);
     */
    protected static $defaultSelfCacheKeyId = 'id';

    /**
     * @var int  can be overridden on the base class (model::$selfCacheKeyExpiration);
     */
    protected static $defaultSelfCacheKeyExpiration = 4000;

    /**
     * @var array
     */
    protected $selfCashedRelations = [];

    /**
     * getSelfCacheKey
     *
     * @param array|string|int $primaryId
     *
     * @return string
     */
    protected static function getSelfCacheKey($primaryId)
    {
        $primaryId = is_array($primaryId) ? implode(':', $primaryId) : $primaryId;

        return self::class . ':selfCache:' . $primaryId;
    }

    /**
     * keyIdToPrimaryId
     *
     * @param string|array $keyId
     *
     * @return string|array
     */
    private function keyIdToPrimaryId($keyId)
    {
        if (is_array($keyId)) {
            $primaryId = [];
            foreach ($keyId as $key) {
                $primaryId[$key] = $this->$key;
            }
        } else {
            $primaryId = $this->$keyId;
        }

        return $primaryId;
    }


    /**
     * getSelfCacheKeyId
     *
     * @return string
     */
    public static function getSelfCacheKeyId()
    {
        return isset(static::$selfCacheKeyId) ? static::$selfCacheKeyId :
            static::$defaultSelfCacheKeyId;
    }

    /**
     * getSelfCacheExpirationTime
     *
     * @return int
     */
    public static function getSelfCacheExpirationTime()
    {
        return isset(static::$selfCacheKeyExpiration) ? static::$selfCacheKeyExpiration :
            static::$defaultSelfCacheKeyExpiration;
    }

    /**
     * getBySelfCacheId
     *
     * @param string|int $idValue
     * @param string     $idKey
     *
     * @return \Illuminate\Database\Eloquent\Model|object|static|null
     */
    protected static function getBySelfCacheId($idValue, $idKey = 'id')
    {
        return Cache::remember(
            self::getSelfCacheKey($idValue),
            self::getSelfCacheExpirationTime(),
            function () use ($idKey, $idValue) {
                /** @var \Illuminate\Database\Eloquent\Builder $result */
                $result = self::where($idKey, $idValue);

                if ($result->getModel()->hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope')) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $result->withTrashed();
                }

                return $result->first();
            }
        );
    }

    /**
     * resetSelfCachedRelations
     *
     */
    public function resetSelfCachedRelations()
    {
        $this->selfCashedRelations = [];
    }

    /**
     * update
     *
     * @param array $attributes
     * @param array $options
     *
     * @return mixed
     */
    public function update(array $attributes = [], array $options = [])
    {
        $updated = parent::update($attributes, $options);
        if ($updated) {
            $this->resetSelfCachedRelations();
            $primaryId     = $this->keyIdToPrimaryId(self::getSelfCacheKeyId());
            $keyExpiration = self::getSelfCacheExpirationTime();
            Cache::put(self::getSelfCacheKey($primaryId), $this, $keyExpiration);
        }

        return $updated;
    }

    /**
     * save
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);
        if ($saved) {
            $this->resetSelfCachedRelations();
            $primaryId     = $this->keyIdToPrimaryId(self::getSelfCacheKeyId());
            $keyExpiration = self::getSelfCacheExpirationTime();
            Cache::put(self::getSelfCacheKey($primaryId), $this, $keyExpiration);
        }

        return $saved;
    }

    /**
     * delete
     *
     * @return bool
     * @throws \Exception
     */
    public function delete()
    {
        $deleted = parent::delete();
        if ($deleted) {
            $primaryId = $this->keyIdToPrimaryId(self::getSelfCacheKeyId());
            Cache::forget(self::getSelfCacheKey($primaryId));
        }

        return $deleted;
    }
}
