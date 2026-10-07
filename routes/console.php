<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:purge-trash', function () {
    $count = \App\Services\TaskTrashService::purgeExpiredTasks(30);
    $this->info("Se han eliminado definitivamente {$count} tareas que superaron los 30 días en la papelera.");
})->purpose('Elimina definitivamente las tareas con más de 30 días en la papelera');

\Illuminate\Support\Facades\Schedule::command('tasks:purge-trash')->daily();
