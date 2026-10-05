<?php

use App\Enums\CustomerStatus;
use App\Enums\PracticeStatus;
use App\Livewire\Admin\Practice\PracticeIndex;
use App\Livewire\Admin\Practice\PracticeShow;
use App\Livewire\Admin\Practice\PracticeUpdate;
use App\Livewire\Admin\Trash\TrashIndex;
use App\Livewire\Forms\PracticeForm;
use App\Models\Customer;
use App\Models\Practice;
use App\Models\ProductType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    // The application uses MySQL CONCAT; provide its semantics on test SQLite.
    if (DB::getDriverName() === 'sqlite') {
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT',
            fn (...$values) => in_array(null, $values, true) ? null : implode('', $values));
    }
});

function permissionsUser(string $role): User
{
    return User::factory()->create()->assignRole($role);
}

function permissionsPractice(User $owner, PracticeStatus $status = PracticeStatus::UNDER_REVIEW): Practice
{
    return Practice::withoutEvents(function () use ($owner, $status) {
        $customer = Customer::factory()->create([
            'user_id' => $owner->id,
            'customer_status' => CustomerStatus::CUSTOMER,
            'lead_status' => null,
        ]);

        return Practice::factory()->create([
            'user_id' => $owner->id,
            'customer_id' => $customer->id,
            'practice_status' => $status,
        ]);
    });
}

test('restricted roles retain own visibility and active permissions with legacy status permission', function ($role, $status) {
    $user = permissionsUser($role);
    $user->givePermissionTo('update practice status');
    $own = permissionsPractice($user, $status);
    $other = permissionsPractice(permissionsUser($role), $status);
    $this->actingAs($user);

    expect(Gate::allows('view', $own))->toBeTrue()
        ->and(Gate::allows('update', $own))->toBe($status !== PracticeStatus::DISBURSED)
        ->and(Gate::allows('delete', $own))->toBe($status !== PracticeStatus::DISBURSED)
        ->and(Gate::allows('updateStatus', $own))->toBeFalse();
    foreach (['view', 'update', 'delete', 'updateStatus'] as $ability) {
        expect(Gate::allows($ability, $other))->toBeFalse();
    }
    expect(Practice::filteredForDepartment()->isExpired($status === PracticeStatus::DISBURSED)->pluck('id')->all())
        ->toBe([$own->id]);
})->with(['consultant', 'external'])->with([PracticeStatus::UNDER_REVIEW, PracticeStatus::DISBURSED]);

test('back office and superadmin retain practice permissions', function ($role, $status) {
    $user = permissionsUser($role);
    $practice = permissionsPractice(permissionsUser('consultant'), $status);
    $this->actingAs($user);
    foreach (['view', 'update', 'delete'] as $ability) {
        expect(Gate::allows($ability, $practice))->toBeTrue();
    }
    expect(Gate::allows('updateStatus', $practice))
        ->toBe($role === 'superadmin' || $status !== PracticeStatus::DISBURSED);
})->with(['back_office', 'superadmin'])->with([PracticeStatus::UNDER_REVIEW, PracticeStatus::DISBURSED]);

test('restricted users cannot invoke status actions from index or detail', function ($role) {
    $user = permissionsUser($role);
    $user->givePermissionTo('update practice status');
    $practice = permissionsPractice($user);

    Livewire::actingAs($user)->test(PracticeIndex::class)
        ->assertDontSeeHtml('title="Cambia stato pratica"')
        ->call('selectPracticeForStatus', $practice->id)->assertForbidden();
    Livewire::test(PracticeIndex::class)
        ->set('selectedPractice', $practice)
        ->set('selectedPracticeStatus', PracticeStatus::APPROVED->value)
        ->call('updatePracticeStatus')->assertForbidden();
    Livewire::test(PracticeShow::class, ['id' => $practice->id])
        ->assertDontSeeHtml('wire:click="openUpdatePracticeStatusModal"')
        ->call('openUpdatePracticeStatusModal')->assertForbidden();
    Livewire::test(PracticeShow::class, ['id' => $practice->id])
        ->set('selectedPracticeStatus', PracticeStatus::APPROVED->value)
        ->call('updatePracticeStatus')->assertForbidden();
    expect($practice->fresh()->practice_status)->toBe(PracticeStatus::UNDER_REVIEW);
})->with(['consultant', 'external']);

test('disbursed practices deny direct edit delete and attachment mutation', function ($role) {
    $user = permissionsUser($role);
    $practice = permissionsPractice($user, PracticeStatus::DISBURSED);
    $attachment = $practice->attachments()->create([
        'file_name' => 'readonly.pdf', 'file_path' => 'readonly.pdf',
        'mime_type' => 'application/pdf', 'file_size' => 10,
    ]);

    Livewire::actingAs($user)->test(PracticeUpdate::class, ['id' => $practice->id])->assertForbidden();
    Livewire::withQueryParams(['expired' => 1])->test(PracticeIndex::class)
        ->assertDontSeeHtml('selectPracticeForDelete('.$practice->id.')')
        ->assertDontSeeHtml(route('practice.edit', ['id' => $practice->id]))
        ->call('selectPracticeForDelete', $practice->id)->assertForbidden();
    Livewire::test(PracticeIndex::class)->set('selectedPractice', $practice)
        ->call('deletePractice')->assertForbidden();
    Livewire::test(PracticeShow::class, ['id' => $practice->id])
        ->assertDontSeeHtml('selectAttachmentForDelete('.$attachment->id.')')
        ->call('selectAttachmentForDelete', $attachment->id)->assertForbidden();
    Livewire::test(PracticeShow::class, ['id' => $practice->id])
        ->set('selectedAttachment', $attachment)->call('deleteAttachment')->assertForbidden();
    expect($practice->fresh())->not->toBeNull()->and($attachment->fresh())->not->toBeNull();
})->with(['consultant', 'external']);

test('archive and direct detail never expose another users practice', function ($role) {
    $user = permissionsUser($role);
    $own = permissionsPractice($user, PracticeStatus::DISBURSED);
    $other = permissionsPractice(permissionsUser($role), PracticeStatus::DISBURSED);
    $component = Livewire::actingAs($user)->withQueryParams(['expired' => 1])->test(PracticeIndex::class);
    expect($component->instance()->rows->pluck('id')->all())->toBe([$own->id]);
    $component->set('search', (string) $other->id);
    expect($component->instance()->rows->pluck('id')->all())->not->toContain($other->id);
    Livewire::test(PracticeShow::class, ['id' => $other->id])->assertForbidden();
})->with(['consultant', 'external']);

test('normal form update rejects indirect status changes before any writes', function ($role) {
    $user = permissionsUser($role);
    $user->givePermissionTo('update practice status');
    $practice = permissionsPractice($user);
    $this->actingAs($user);
    $form = new PracticeForm(new PracticeUpdate, 'practiceForm');
    $form->setPractice($practice);
    $form->practiceStatus = PracticeStatus::APPROVED->value;
    $form->notes = 'unauthorized change';
    $oldNotes = $practice->opportunity->notes;

    expect(fn () => $form->update())->toThrow(AuthorizationException::class);
    expect($practice->fresh()->practice_status)->toBe(PracticeStatus::UNDER_REVIEW)
        ->and($practice->opportunity->fresh()->notes)->toBe($oldNotes);
})->with(['consultant', 'external']);

test('a stale form cannot mutate a practice disbursed after opening', function ($role) {
    $user = permissionsUser($role);
    $practice = permissionsPractice($user);
    $this->actingAs($user);
    $form = new PracticeForm(new PracticeUpdate, 'practiceForm');
    $form->setPractice($practice);
    Practice::whereKey($practice->id)->update(['practice_status' => PracticeStatus::DISBURSED->value]);
    $form->userId = permissionsUser($role)->id;
    expect(fn () => $form->update())->toThrow(AuthorizationException::class);
    expect($practice->fresh()->user_id)->toBe($user->id)
        ->and($practice->fresh()->practice_status)->toBe(PracticeStatus::DISBURSED);
})->with(['consultant', 'external']);

test('internal action helpers are not callable via Livewire', function ($method) {
    $user = permissionsUser('consultant');
    $practice = permissionsPractice($user, PracticeStatus::DISBURSED);
    $component = Livewire::actingAs($user)->test(PracticeIndex::class)->set('selectedPractice', $practice);
    expect(fn () => $component->call($method))->toThrow(MethodNotFoundException::class);
    expect($practice->fresh())->not->toBeNull();
})->with(['selectEntityForAction', 'deleteSelectedEntity']);

test('an active practice cannot be used to delete a disbursed practices attachment', function ($role) {
    $user = permissionsUser($role);
    $active = permissionsPractice($user);
    $disbursed = permissionsPractice($user, PracticeStatus::DISBURSED);
    $attachment = $disbursed->attachments()->create([
        'file_name' => 'protected.pdf', 'file_path' => 'protected.pdf',
        'mime_type' => 'application/pdf', 'file_size' => 10,
    ]);
    foreach ([PracticeShow::class, PracticeUpdate::class] as $component) {
        $selection = Livewire::actingAs($user)->test($component, ['id' => $active->id]);
        expect(fn () => $selection->call('selectAttachmentForDelete', $attachment->id))
            ->toThrow(ModelNotFoundException::class);
        $deletion = Livewire::test($component, ['id' => $active->id])->set('selectedAttachment', $attachment);
        expect(fn () => $deletion->call('deleteAttachment'))->toThrow(ModelNotFoundException::class);
    }
    expect($attachment->fresh())->not->toBeNull();
})->with(['consultant', 'external']);

test('trash denies legacy force delete permission but preserves visibility and restore', function ($role, $type) {
    $user = permissionsUser($role);
    $user->givePermissionTo('force delete trash');
    $entity = $type === 'practice' ? permissionsPractice($user, PracticeStatus::DISBURSED)
        : Customer::factory()->create(['user_id' => $user->id, 'customer_status' => $type]);
    $entity->forceFill(['deleted_by' => $user->id])->saveQuietly();
    $entity->deleteQuietly();

    $component = Livewire::actingAs($user)->test(TrashIndex::class)
        ->assertSee('Ripristina')->assertDontSeeHtml('x-on:click="deleteItem(item, $wire)"');
    expect(collect($component->instance()->trashItems)->where('entity_type', $type)->pluck('id')->all())->toContain($entity->id);
    $component->call('forceDelete', $type, $entity->id)->assertForbidden();
    expect($entity->fresh()->trashed())->toBeTrue();
    Livewire::test(TrashIndex::class)->call('restore', $type, $entity->id);
    expect($entity->fresh()->trashed())->toBeFalse();
})->with(['consultant', 'external', 'back_office', 'floor_manager', 'web'])->with(['practice', 'customer', 'lead']);

test('superadmin can force delete every trash type without an explicit permission', function ($type) {
    $user = permissionsUser('superadmin');
    $owner = permissionsUser('consultant');
    $entity = $type === 'practice' ? permissionsPractice($owner)
        : Customer::factory()->create(['user_id' => $owner->id, 'customer_status' => $type]);
    $entity->forceFill(['deleted_by' => $owner->id])->saveQuietly();
    $entity->deleteQuietly();
    Livewire::actingAs($user)->test(TrashIndex::class)
        ->assertSeeHtml('x-on:click="deleteItem(item, $wire)"')
        ->call('forceDelete', $type, $entity->id);
    expect($entity->fresh())->toBeNull();
})->with(['practice', 'customer', 'lead']);

test('seeder removes obsolete permissions without changing back office status permission', function () {
    foreach (['consultant', 'external', 'back_office', 'floor_manager', 'web'] as $role) {
        $user = permissionsUser($role);
        expect($user->hasPermissionTo('force delete trash'))->toBeFalse();
        if (in_array($role, ['consultant', 'external'])) {
            expect($user->hasPermissionTo('update practice status'))->toBeFalse();
        }
    }
    expect(permissionsUser('back_office')->hasPermissionTo('update practice status'))->toBeTrue();
});

test('ordinary form updates remain available to authorized users', function ($role, $status) {
    $user = permissionsUser($role);
    $practice = permissionsPractice($user, $status);
    $product = ProductType::create(['name' => 'Test product', 'slug' => 'test-product']);
    $practice->opportunity->update(['product_type_id' => $product->id]);
    $this->actingAs($user);
    $form = new PracticeForm(new PracticeUpdate, 'practiceForm');
    $form->setPractice($practice->fresh());
    $form->notes = 'Permitted edit';
    expect($form->update())->toBeInstanceOf(Practice::class);
    expect($practice->opportunity->fresh()->notes)->toBe('Permitted edit')
        ->and($practice->fresh()->practice_status)->toBe($status);
})->with([
    ['consultant', PracticeStatus::UNDER_REVIEW],
    ['external', PracticeStatus::UNDER_REVIEW],
    ['back_office', PracticeStatus::UNDER_REVIEW],
    ['back_office', PracticeStatus::DISBURSED],
    ['superadmin', PracticeStatus::UNDER_REVIEW],
    ['superadmin', PracticeStatus::DISBURSED],
]);

test('legacy status manipulation through savePractice leaves both records unchanged', function ($role) {
    $user = permissionsUser($role);
    $user->givePermissionTo('update practice status');
    $practice = permissionsPractice($user);
    $oldName = $practice->customer->first_name;
    Livewire::actingAs($user)->test(PracticeUpdate::class, ['id' => $practice->id])
        ->set('practiceForm.practiceStatus', PracticeStatus::APPROVED->value)
        ->set('customerForm.firstName', 'Unauthorized customer change')
        ->call('savePractice')->assertHasNoErrors();
    expect($practice->fresh()->practice_status)->toBe(PracticeStatus::UNDER_REVIEW)
        ->and($practice->customer->fresh()->first_name)->toBe($oldName);
})->with(['consultant', 'external']);

test('authorized roles can still change status through Livewire', function ($role) {
    $user = permissionsUser($role);
    $practice = permissionsPractice(permissionsUser('consultant'));
    Livewire::actingAs($user)->test(PracticeIndex::class)
        ->assertSeeHtml('title="Cambia stato pratica"')
        ->call('selectPracticeForStatus', $practice->id)
        ->set('selectedPracticeStatus', PracticeStatus::APPROVED->value)
        ->call('updatePracticeStatus')->assertHasNoErrors();
    expect($practice->fresh()->practice_status)->toBe(PracticeStatus::APPROVED);
})->with(['back_office', 'superadmin']);

test('active deletion still works through the authorized internal helper', function ($role) {
    $user = permissionsUser($role);
    $practice = permissionsPractice($user);
    Livewire::actingAs($user)->test(PracticeIndex::class)
        ->call('selectPracticeForDelete', $practice->id)->call('deletePractice');
    expect($practice->fresh()->trashed())->toBeTrue();
})->with(['consultant', 'external']);

test('an open Livewire edit cannot save or reassign a newly disbursed practice', function ($role) {
    $user = permissionsUser($role);
    $practice = permissionsPractice($user);
    $other = permissionsUser($role);
    $component = Livewire::actingAs($user)->test(PracticeUpdate::class, ['id' => $practice->id]);
    Practice::whereKey($practice->id)->update(['practice_status' => PracticeStatus::DISBURSED->value]);
    $component->set('practiceForm.userId', $other->id)
        ->set('practiceForm.notes', 'Denied edit')
        ->call('savePractice')->assertForbidden();
    expect($practice->fresh()->user_id)->toBe($user->id)
        ->and($practice->fresh()->practice_status)->toBe(PracticeStatus::DISBURSED);
})->with(['consultant', 'external']);
