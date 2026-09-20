<?php

namespace App\Domain\Shared;

class VideoEmbed
{
    public static function id(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', trim($url), $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public static function src(string $url): ?string
    {
        $id = self::id($url);

        return $id === null ? null : 'https://www.youtube.com/embed/'.$id;
    }
}
