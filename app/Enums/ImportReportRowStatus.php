<?php

namespace App\Enums;

enum ImportReportRowStatus: string
{
    case IMPORTED = 'imported';
    case FAILED = 'failed';
}