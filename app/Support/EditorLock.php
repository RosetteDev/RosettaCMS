<?php

namespace App\Support;

use App\Models\Post;
use App\Models\PostLock;
use App\Models\User;

/**
 * Lightweight presence/locking for the editor.
 *
 * There are no websockets or realtime servers in this CMS, so collaboration is
 * approximated with a short-lived lock that is refreshed by the editor every
 * few seconds. A lock whose heartbeat is older than STALE_SECONDS is treated
 * as expired and deleted, so a crashed tab cannot hold content hostage.
 */
final class EditorLock
{
    public const STALE_SECONDS = 120;

    /**
     * Delete locks whose heartbeat is too old.
     */
    public static function pruneStale(): void
    {
        PostLock::where('heartbeat_at', '<', now()->subSeconds(self::STALE_SECONDS))->delete();
    }

    /**
     * Return the user currently holding a live lock on the given post, or null.
     */
    public static function holder(Post $post): ?User
    {
        self::pruneStale();

        $lock = PostLock::where('post_id', $post->id)
            ->orderByDesc('heartbeat_at')
            ->first();

        return $lock?->user;
    }

    /**
     * The live lock held by someone other than $user, if any.
     */
    public static function conflictingLock(Post $post, User $user): ?PostLock
    {
        self::pruneStale();

        return PostLock::where('post_id', $post->id)
            ->where('user_id', '!=', $user->id)
            ->where('heartbeat_at', '>=', now()->subSeconds(self::STALE_SECONDS))
            ->orderByDesc('heartbeat_at')
            ->first();
    }

    /**
     * Acquire (or refresh) the lock for $user. Does NOT steal the lock from
     * someone else; the caller is expected to check conflictingLock first.
     */
    public static function acquire(Post $post, User $user): PostLock
    {
        self::pruneStale();

        return PostLock::updateOrCreate(
            ['post_id' => $post->id, 'user_id' => $user->id],
            ['locked_at' => now(), 'heartbeat_at' => now()]
        );
    }

    /**
     * Release the lock held by $user.
     */
    public static function release(Post $post, User $user): void
    {
        PostLock::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
