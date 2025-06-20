<?php

namespace Goodcat\QueryString\Tests\Feature;

use Goodcat\QueryString\Attributes\QueryString;
use Goodcat\QueryString\Tests\TestCase;
use Goodcat\QueryString\Traits\UseQueryString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;

class UseQueryStringTest extends TestCase
{
    #[Test]
    public function it_generates_query_from_query_string(): void
    {
        $sql = (new FakeModel)->query()->queryString(['name' => 'John Doe'])->toSql();

        $this->assertStringContainsString('where "name" like ?', $sql);
    }

    #[Test]
    public function it_ignores_empty_query_string(): void
    {
        $sql = (new FakeModel)->query()->queryString(['email' => null])->toSql();

        $this->assertStringNotContainsString('where "email" like ?', $sql);
    }

    #[Test]
    public function it_handles_multiple_attributes_on_same_function(): void
    {
        $queryString = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ];

        $sql = (new FakeModel)->query()->queryString($queryString)->toSql();

        $this->assertStringContainsString('where "name" like ? and "email" like ?', $sql);
    }

    #[Test]
    public function it_uses_config_file(): void
    {
        config()->set('querystring.allows_null', true);

        $sql = (new FakeModel)->query()->queryString(['email' => null])->toSql();

        $this->assertStringContainsString('where "email" like ?', $sql);
    }
}

class FakeModel extends Model
{
    use UseQueryString;

    /**
     * @param  Builder<self>  $query
     */
    #[QueryString('name')]
    #[QueryString('email')]
    public function genericTextSearch(Builder $query, ?string $search, string $queryString): void
    {
        $query->where($queryString, 'like', "$search%");
    }
}
