<?php

use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\PartialComposer;
use Liberu\ComposerInstaller\LiberuInstaller;

function installer(?string $rootDir = null): LiberuInstaller
{
    $composer = new PartialComposer;
    $composer->setConfig(new Config(false, $rootDir ?? sys_get_temp_dir()));

    return new LiberuInstaller(new NullIO, $composer, $rootDir);
}

function liberuPackage(string $prettyName, string $type, mixed $installerName): Package
{
    $package = new Package($prettyName, '1.0.0.0', '1.0.0');
    $package->setType($type);
    $package->setExtra($installerName === null ? [] : ['liberu' => ['name' => $installerName]]);

    return $package;
}

function scratchRoot(): string
{
    $root = sys_get_temp_dir().'/liberu-installer-'.bin2hex(random_bytes(6));
    mkdir($root.'/modules', 0777, true);

    return $root;
}

describe('path computation', function () {
    it('installs a module under modules and a theme under themes', function (string $type, string $expected) {
        $package = liberuPackage('liberusoftware/search', $type, 'search');

        expect(installer()->getInstallPath($package))->toBe($expected);
    })->with([
        ['liberu-module', 'modules/search'],
        ['liberu-theme', 'themes/search'],
    ]);

    it('derives the path from the installer name, not the package name', function () {
        $package = liberuPackage('liberusoftware/module-identity-core-filament', 'liberu-module', 'identity-filament');

        expect(installer()->getInstallPath($package))->toBe('modules/identity-filament');
    });

    it('returns the same path when asked twice for one package', function () {
        $installer = installer();
        $package = liberuPackage('liberusoftware/search', 'liberu-module', 'search');

        expect($installer->getInstallPath($package))->toBe('modules/search')
            ->and($installer->getInstallPath($package))->toBe('modules/search');
    });
});

describe('supported types', function () {
    it('supports only the two Liberu package types', function (string $type, bool $supported) {
        expect(installer()->supports($type))->toBe($supported);
    })->with([
        ['liberu-module', true],
        ['liberu-theme', true],
        ['library', false],
        ['composer-plugin', false],
        ['liberu-modules', false],
        ['', false],
    ]);
});

describe('name validation', function () {
    it('rejects a name that is not a lowercase hyphenated slug', function (mixed $name) {
        installer()->getInstallPath(liberuPackage('liberusoftware/bad', 'liberu-module', $name));
    })->with([
        'missing' => [null],
        'empty' => [''],
        'uppercase' => ['Search'],
        'underscore' => ['my_module'],
        'leading hyphen' => ['-search'],
        'trailing hyphen' => ['search-'],
        'double hyphen' => ['search--core'],
        'not a string' => [42],
        'array' => [['search']],
    ])->throws(InvalidArgumentException::class);

    it('rejects traversal and absolute paths', function (string $name) {
        installer()->getInstallPath(liberuPackage('liberusoftware/bad', 'liberu-module', $name));
    })->with([
        '../evil',
        '../../etc/passwd',
        '/etc/passwd',
        'nested/path',
        'back\\slash',
        '.',
        '..',
    ])->throws(InvalidArgumentException::class);
});

describe('collision detection', function () {
    it('rejects two different packages claiming one target in a single run', function () {
        $installer = installer();
        $installer->getInstallPath(liberuPackage('liberusoftware/search', 'liberu-module', 'search'));

        $installer->getInstallPath(liberuPackage('liberusoftware/search-legacy', 'liberu-module', 'search'));
    })->throws(InvalidArgumentException::class);

    it('rejects a target directory already holding a different package', function () {
        $root = scratchRoot();
        mkdir($root.'/modules/search');
        file_put_contents($root.'/modules/search/composer.json', json_encode(['name' => 'liberusoftware/search-legacy']));

        installer($root)->getInstallPath(liberuPackage('liberusoftware/search', 'liberu-module', 'search'));
    })->throws(InvalidArgumentException::class);

    it('accepts a target directory already holding the same package', function () {
        $root = scratchRoot();
        mkdir($root.'/modules/search');
        file_put_contents($root.'/modules/search/composer.json', json_encode(['name' => 'liberusoftware/search']));

        expect(installer($root)->getInstallPath(liberuPackage('liberusoftware/search', 'liberu-module', 'search')))
            ->toBe('modules/search');
    });

    it('accepts a target directory that claims no package', function (?string $contents) {
        $root = scratchRoot();
        mkdir($root.'/modules/search');
        if ($contents !== null) {
            file_put_contents($root.'/modules/search/composer.json', $contents);
        }

        expect(installer($root)->getInstallPath(liberuPackage('liberusoftware/search', 'liberu-module', 'search')))
            ->toBe('modules/search');
    })->with([
        'no composer.json' => [null],
        'malformed composer.json' => ['{ not json'],
        'composer.json without a name' => ['{"type":"liberu-module"}'],
    ]);
});
