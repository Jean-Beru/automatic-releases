<?php

declare(strict_types=1);

namespace Laminas\AutomaticReleases\Test\Unit\Github\Api\GraphQL\Query\GetMilestoneChangelog\Response;

use Laminas\AutomaticReleases\Github\Api\GraphQL\Query\GetMilestoneChangelog\Response\Author;
use PHPUnit\Framework\TestCase;

final class AuthorTest extends TestCase
{
    public function test(): void
    {
        $author = Author::fromPayload([
            'login' => 'Magoo',
            'url'   => 'http://example.com/',
        ]);

        self::assertSame('Magoo', $author->name());
        self::assertSame('http://example.com/', $author->url()->__toString());
    }
}
