<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sitemap:generate')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('links:check --limit=50')->dailyAt('03:15')->withoutOverlapping();
