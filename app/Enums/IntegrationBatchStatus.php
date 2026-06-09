<?php
namespace App\Enums;

enum IntegrationBatchStatus: string
{
    case Failed = 'failed';
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case PartialFailed = 'partial_failed';
}
