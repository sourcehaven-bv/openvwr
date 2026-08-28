<?php

declare(strict_types=1);

namespace Tests\Feature\Scopes\Tenancy;

use App\Models\Organisation;
use App\Models\Scopes\TenantScope;
use App\Models\Tag;
use App\Models\Wpg\WpgProcessingRecord;
use Filament\Facades\Filament;
use Tests\Feature\FeatureTestCase;

class TenantScopeTest extends FeatureTestCase
{
    public function testTenantQueryScopesWithoutChangingTheModelDefault(): void
    {
        $organisation = Organisation::factory()
            ->hasUsers(1)
            ->create();
        $otherOrganisation = Organisation::factory()->create();
        $user = $organisation->users->first();

        $this->be($user);
        Filament::setTenant($organisation);

        $tag = Tag::factory()->for($organisation)->create();
        $otherTag = Tag::factory()->for($otherOrganisation)->create();

        $this->assertEquals([$tag->id], Tag::tenantQuery()->pluck('id')->all());
        $this->assertEqualsCanonicalizing(
            [$tag->id, $otherTag->id],
            Tag::query()->whereKey([$tag->id, $otherTag->id])->pluck('id')->all(),
        );
    }

    public function testItScopes(): void
    {
        $organisation = Organisation::factory()
            ->hasUsers(1)
            ->create();

        $user = $organisation->users->first();
        $this->be($user);
        Filament::setTenant($organisation);

        $organisation2 = Organisation::factory()->create();
        WpgProcessingRecord::factory()->for($organisation, 'organisation')->create();
        WpgProcessingRecord::factory()->for($organisation2, 'organisation')->create();
        WpgProcessingRecord::addGlobalScope(new TenantScope());

        $this->assertCount(1, WpgProcessingRecord::all());
        $this->assertGreaterThan(1, WpgProcessingRecord::withoutGlobalScope(TenantScope::class)->count());
    }
}
