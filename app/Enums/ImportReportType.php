<?php

namespace App\Enums;

enum ImportReportType: string
{
    case LEADS = 'leads';
    case PRACTICES = 'practices';
}