<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('quicksolve:prune-usage')->daily()->withoutOverlapping();
