<?php

namespace App\Enums;

enum AtcType: string
{
    case PRIVATE = 'Privata';
    case SEMIPUBLIC = 'Parapubblica';
    case PUBLIC = 'Pubblica';
    case MINISTERIAL = 'Ministeriale';
}
