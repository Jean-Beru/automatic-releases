<?php

declare(strict_types=1);

namespace Laminas\AutomaticReleases\Git\Value;

use Webmozart\Assert\Assert;

use function Safe\preg_match;

/** @psalm-immutable */
final class SemVerVersion
{
    private int $major;
    private int $minor;
    private int $patch;
    private string $prerelease;
    private string $buildmetadata;

    private function __construct(int $major, int $minor, int $patch, string $prerelease = '', string $buildmetadata = '')
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->prerelease = $prerelease;
        $this->buildmetadata = $buildmetadata;
    }

    /**
     * @psalm-pure
     * @psalm-suppress ImpureFunctionCall the {@see \Safe\preg_match()} API is pure by design
     */
    public static function fromMilestoneName(string $name): self
    {
        $regex = '(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?';

        Assert::notEmpty($name);
        Assert::regex($name, "/^(v)?$regex$/");

        preg_match("/$regex/", $name, $matches);

        Assert::isArray($matches);

        return new self(
            (int) $matches['major'],
            (int) $matches['minor'],
            (int) $matches['patch'],
            isset($matches['prerelease']) ? (string) $matches['prerelease'] : '',
            isset($matches['buildmetadata']) ? (string) $matches['buildmetadata'] : ''
        );
    }

    /** @psalm-return non-empty-string */
    public function fullReleaseName(): string
    {
        $name = $this->major . '.' . $this->minor . '.' . $this->patch;
        if ($this->prerelease) {
            $name .= '-' . $this->prerelease;
        }
        if ($this->buildmetadata) {
            $name .= '+' . $this->buildmetadata;
        }

        return $name;
    }

    public function major(): int
    {
        return $this->major;
    }

    public function minor(): int
    {
        return $this->minor;
    }

    public function nextPatch(): self
    {
        return new self($this->major, $this->minor, $this->patch + 1);
    }

    public function nextMinor(): self
    {
        return new self($this->major, $this->minor + 1, 0);
    }

    public function nextMajor(): self
    {
        return new self($this->major + 1, 0, 0);
    }

    public function targetReleaseBranchName(): BranchName
    {
        return BranchName::fromName($this->major . '.' . $this->minor . '.x');
    }

    public function isNewMinorRelease(): bool
    {
        return $this->patch === 0 && empty($this->prerelease) && empty($this->buildmetadata);
    }

    public function isNewMajorRelease(): bool
    {
        return $this->minor === 0 && $this->patch === 0 && empty($this->prerelease) && empty($this->buildmetadata);
    }

    public function lessThanEqual(self $other): bool
    {
        return $this->fullReleaseName() <= $other->fullReleaseName();
    }
}
