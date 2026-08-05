<?php

namespace Liberu\ComposerInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\PartialComposer;
use InvalidArgumentException;

final class LiberuInstaller extends LibraryInstaller
{
    /**
     * Anchored, and admits no `/`, `\` or `.`, so traversal and absolute paths cannot
     * reach the computed target.
     */
    private const NAME_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** @var array<string, string> target path => the package that claimed it this run */
    private array $targets = [];

    public function __construct(IOInterface $io, PartialComposer $composer, private readonly ?string $rootDir = null)
    {
        parent::__construct($io, $composer);
    }

    public function supports(string $packageType): bool
    {
        return in_array($packageType, ['liberu-module', 'liberu-theme'], true);
    }

    public function getInstallPath(PackageInterface $package): string
    {
        $name = $package->getExtra()['liberu']['name'] ?? null;

        if (! is_string($name) || ! preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException("Package [{$package->getPrettyName()}] has an invalid Liberu installer name.");
        }

        $target = ($package->getType() === 'liberu-theme' ? 'themes' : 'modules').'/'.$name;

        $this->guardAgainstCollision($target, $package->getPrettyName());

        return $target;
    }

    /**
     * Collisions are checked against both this run and the working tree, because a
     * directory left behind by a renamed or removed package outlives the process that
     * created it and would otherwise be silently installed over.
     */
    private function guardAgainstCollision(string $target, string $package): void
    {
        $claimant = $this->targets[$target] ?? $this->packageDeclaredAt($target);

        if ($claimant !== null && $claimant !== $package) {
            throw new InvalidArgumentException("Liberu installer target collision at [{$target}]: already claimed by [{$claimant}], requested by [{$package}].");
        }

        $this->targets[$target] = $package;
    }

    /**
     * The package a target directory declares itself to be, or null when it declares
     * nothing. An absent, unreadable or nameless `composer.json` is not evidence of
     * ownership, so only a different, named package blocks the install.
     */
    private function packageDeclaredAt(string $target): ?string
    {
        $manifest = ($this->rootDir ?? (string) getcwd()).'/'.$target.'/composer.json';

        if (! is_file($manifest)) {
            return null;
        }

        $declared = json_decode((string) file_get_contents($manifest), true);

        return is_array($declared) && is_string($declared['name'] ?? null) ? $declared['name'] : null;
    }
}
