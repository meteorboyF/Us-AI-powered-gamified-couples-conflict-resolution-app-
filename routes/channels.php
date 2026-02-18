<?php

use App\Broadcasting\CoupleChannelAuthorizer;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('couple.{coupleId}', CoupleChannelAuthorizer::class);
