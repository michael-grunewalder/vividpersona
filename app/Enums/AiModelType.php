<?php

namespace App\Enums;

/**
 * The kind of output an AI model produces. The UI picks a type based on the
 * feature context (e.g. the persona wizard only lists image models).
 */
enum AiModelType: string
{
    case Llm = 'llm';
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';

    public function label(): string
    {
        return match ($this) {
            self::Llm => 'LLM',
            self::Image => 'Image',
            self::Video => 'Video',
            self::Audio => 'Audio',
        };
    }
}
