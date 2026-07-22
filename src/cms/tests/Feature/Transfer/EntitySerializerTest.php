<?php

declare(strict_types=1);

use App\Models\Remark;
use App\Models\User;
use App\Transfer\Export\EntitySerializer;

it('omits the owner when the owner morph type is not transferable', function (): void {
    $owner = User::factory()->create();
    $author = User::factory()->create();

    // a remark whose owner is a non-transferable model (User) must serialise without an owner
    $remark = Remark::factory()->create([
        'body' => 'Notitie',
        'user_id' => $author->id,
        'remark_relatable_type' => User::class,
        'remark_relatable_id' => $owner->id,
    ]);

    $data = (new EntitySerializer())->serialize($remark);

    expect($data)->not->toHaveKey('owner')
        ->and($data['type'])->toBe('remark');
});
