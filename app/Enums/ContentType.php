<?php

namespace App\Enums;

enum ContentType: string
{
    case COURSE = 'course';
    case LIVE_LEARNING = 'live learning';
    case RESOURCE = 'resource';
    case VIDEO = 'video';
    case PROGRAM = 'program';
    case PAGE = 'page';
    case PARTNERED_CONTENT = 'partnered content';

    /**
     * Get all content type values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
} 