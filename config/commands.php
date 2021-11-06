<?php

return [
    'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => [],
    'hidden' => [
//        Illuminate\Database\Console\Migrations\FreshCommand::class,
//        Illuminate\Database\Console\Migrations\MigrateCommand::class,
//        Illuminate\Database\Console\Migrations\ResetCommand::class,
//        Illuminate\Database\Console\Migrations\RollbackCommand::class,
//        Illuminate\Database\Console\Migrations\StatusCommand::class,
        Illuminate\Database\Console\Migrations\InstallCommand::class,
    ],
    'remove' => [
        Illuminate\Database\Console\Migrations\MigrateMakeCommand::class,
        Illuminate\Database\Console\Migrations\RefreshCommand::class,
        Illuminate\Database\Console\Seeds\SeedCommand::class,
        Illuminate\Database\Console\WipeCommand::class,
    ],
];
