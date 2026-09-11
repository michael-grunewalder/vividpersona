<?php

namespace App\Enums;

enum ApiProviderType: string
{
    case Llm = 'llm';
    case Media = 'media';
}
