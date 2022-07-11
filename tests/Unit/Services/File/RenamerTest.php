<?php

use App\Services\File\Renamer;

foreach ([
    'Stranger Things S02E01 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things 1080p.mkv',
    'Stranger Things.mkv',
    'Stranger Things',
] as $filename) {
    test('should parse name for ' . $filename, function () use ($filename) {
        expect((new Renamer())->getName($filename))
            ->toEqual('Stranger Things');
    });
}

foreach ([
    'Stranger Things S02E12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things Saison 02 Episode 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things.Saison.02.Episode.12.[MULTI].Blu-ray 1080p.mkv',
    'Stranger Things Season 02 Ep 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things.Season.02.Ep.12.[MULTI].Blu-ray 1080p.mkv',
] as $filename) {
    test('should parse episode number for ' . $filename, function () use ($filename) {
        expect((new Renamer())->getNumber($filename))
            ->toEqual(['season' => 2, 'episode' => 12]);
    });
}

test('should parse empty number', function () {
    expect((new Renamer())->getNumber('Stranger Things [MULTI] Blu-ray 1080p.mkv'))
        ->toEqual([]);
});

test('should parse resolution', function () {
    expect((new Renamer())->getResolution('Stranger Things S02E12 [MULTI] Blu-ray 1080p.mkv'))
        ->toEqual('1080p');
});

test('should parse empty resolution', function () {
    expect((new Renamer())->getResolution('Stranger Things S02E12 [MULTI] Blu-ray.mkv'))
        ->toEqual('');
});

test('should parse extension', function () {
    expect((new Renamer())->getExtension('Stranger Things S02E12 [MULTI] Blu-ray 1080p.mkv'))
        ->toEqual('mkv');
});

test('should parse empty extension', function () {
    expect((new Renamer())->getExtension('Stranger Things S02E12 [MULTI] Blu-ray'))
        ->toEqual('');
});

foreach ([
    'Stranger Things S02E12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things Saison 02 Episode 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things.Saison.02.Episode.12.[MULTI].Blu-ray 1080p.mkv',
    'Stranger Things Season 02 Ep 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger.Things.S02E12.VF.1080p.NF.WEB-DL.DDP5.1.x264-Wawacity.co.mkv',
] as $filename) {
    test('should auto rename ' . $filename, function () use ($filename) {
        expect((new Renamer())->autoRename($filename))
            ->toEqual('Stranger.Things.S02E12.1080p.mkv');
    });
}

test('should auto rename with empty number', function () {
    expect((new Renamer())->autoRename('Stranger Things [MULTI] Blu-ray 1080p.mkv'))
        ->toEqual('Stranger.Things.[MULTI].Blu-ray.1080p.mkv');
});

test('should auto rename with empty resolution', function () {
    expect((new Renamer())->autoRename('Stranger Things S02E12 [MULTI] Blu-ray.mkv'))
        ->toEqual('Stranger.Things.S02E12.mkv');
});

test('should auto rename with empty extension', function () {
    expect((new Renamer())->autoRename('Stranger Things S02E12 [MULTI] 1080p Blu-ray'))
        ->toEqual('Stranger.Things.S02E12.1080p');
});

foreach ([
    'Stranger Things S02E12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things Saison 02 Episode 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things.Saison.02.Episode.12.[MULTI].Blu-ray 1080p.mkv',
    'Stranger Things Season 02 Ep 12 [MULTI] Blu-ray 1080p.mkv',
    'Stranger Things.Season.02.Ep.12.[MULTI].Blu-ray 1080p.mkv',
] as $filename) {
    test('should rename ' . $filename, function () use ($filename) {
        expect((new Renamer())->rename($filename, 'Sherlock Holmes'))
            ->toEqual('Sherlock.Holmes.S02E12.1080p.mkv');
    });
}

test('should rename with empty number', function () {
    expect((new Renamer())->rename('Stranger Things [MULTI] Blu-ray 1080p.mkv', 'Sherlock Holmes'))
        ->toEqual('Sherlock.Holmes.1080p.mkv');
});

test('should rename with empty resolution', function () {
    expect((new Renamer())->rename('Stranger Things S02E12 [MULTI] Blu-ray.mkv', 'Sherlock Holmes'))
        ->toEqual('Sherlock.Holmes.S02E12.mkv');
});

test('should rename with empty extension', function () {
    expect((new Renamer())->rename('Stranger Things S02E12 [MULTI] 1080p Blu-ray', 'Sherlock Holmes'))
        ->toEqual('Sherlock.Holmes.S02E12.1080p');
});
