<?php

use App\Jobs\SendPdfEmailJob;
use App\Livewire\Pdf\PdfActions;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function makePdfViewer(bool $mayEmail = false): User
{
    $role = Role::firstOrCreate(['name' => 'PdfViewer', 'guard_name' => 'web']);

    foreach ($mayEmail ? ['users.index', 'users.edit'] : ['users.index'] as $name) {
        $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
    }

    $viewer = User::factory()->create();
    $viewer->assignRole($role);

    return $viewer;
}

test('las rutas PDF exigen autorización', function () {
    User::factory()->create();
    $viewer = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($viewer)
        ->get(route('pdf.show', ['type' => 'user.profile', 'record' => $target->id]))
        ->assertForbidden();
});

test('no se puede descargar el usuario reservado ni un tipo no registrado', function () {
    $protected = User::factory()->create();
    $viewer = makePdfViewer();

    $this->actingAs($viewer)
        ->get(route('pdf.download', ['type' => 'user.profile', 'record' => $protected->id]))
        ->assertForbidden();

    $this->actingAs($viewer)
        ->get(route('pdf.show', ['type' => 'desconocido', 'record' => $protected->id]))
        ->assertNotFound();
});

test('un perfil autorizado responde como PDF sin ejecutar Chromium en el test', function () {
    User::factory()->create();
    $viewer = makePdfViewer();
    $target = User::factory()->create();

    Pdf::fake();

    $response = $this->actingAs($viewer)
        ->get(route('pdf.download', ['type' => 'user.profile', 'record' => $target->id]));

    $response->assertOk();
    Pdf::assertRespondedWithPdf(fn (PdfBuilder $pdf) =>
        $pdf->viewName === 'pdf.users.profile' && $pdf->downloadName === 'usuario-'.$target->id.'.pdf'
    );
});

test('un perfil suspendido sigue disponible con permiso de consulta', function () {
    User::factory()->create();
    $viewer = makePdfViewer();
    $target = User::factory()->create();
    $target->delete();

    Pdf::fake();

    $this->actingAs($viewer)
        ->get(route('pdf.show', ['type' => 'user.profile', 'record' => $target->id]))
        ->assertOk();

    Pdf::assertRespondedWithPdf(fn (PdfBuilder $pdf) =>
        $pdf->viewName === 'pdf.users.profile'
        && $pdf->viewData['user']->trashed()
    );
});

test('no se puede descargar la ficha de otro SuperAdmin sin ese rol', function () {
    User::factory()->create();
    $viewer = makePdfViewer();
    $target = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
    $target->assignRole($role);

    $this->actingAs($viewer)
        ->get(route('pdf.download', ['type' => 'user.profile', 'record' => $target->id]))
        ->assertForbidden();
});

test('el envío exige permiso de edición', function () {
    User::factory()->create();
    $viewer = makePdfViewer();
    $target = User::factory()->create();
    Bus::fake();

    Livewire::actingAs($viewer)
        ->test(PdfActions::class, ['type' => 'user.profile', 'recordId' => $target->id])
        ->call('openEmail')
        ->assertForbidden();

    Bus::assertNotDispatched(SendPdfEmailJob::class);
});

test('el correo se solicita sin generar ni serializar el PDF en la petición', function () {
    User::factory()->create();
    $viewer = makePdfViewer(true);
    $target = User::factory()->create();
    Bus::fake();

    Livewire::actingAs($viewer)
        ->test(PdfActions::class, ['type' => 'user.profile', 'recordId' => $target->id])
        ->call('openEmail')
        ->set('newRecipient', true)
        ->assertSet('recipient', '')
        ->set('recipient', 'destinatario@example.org')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSee('Solicitud de envío registrada');

    Bus::assertDispatched(SendPdfEmailJob::class, fn (SendPdfEmailJob $job) =>
        $job->type === 'user.profile' && $job->recordId === $target->id && $job->actorId === $viewer->id
    );
});

test('modal comienza con correo del usuario y casilla desmarcada', function () {
    User::factory()->create();
    $viewer = makePdfViewer(true);
    $target = User::factory()->create(['email' => 'perfil@example.org']);

    Livewire::actingAs($viewer)
        ->test(PdfActions::class, ['type' => 'user.profile', 'recordId' => $target->id])
        ->call('openEmail')
        ->assertSet('newRecipient', false)
        ->assertSet('recipient', 'perfil@example.org');
});

test('casilla activa limpia el campo y al desmarcar restaura correo', function () {
    User::factory()->create();
    $viewer = makePdfViewer(true);
    $target = User::factory()->create(['email' => 'perfil@example.org']);

    Livewire::actingAs($viewer)
        ->test(PdfActions::class, ['type' => 'user.profile', 'recordId' => $target->id])
        ->call('openEmail')
        ->set('newRecipient', true)
        ->assertSet('recipient', '')
        ->set('recipient', 'otro@example.org')
        ->set('newRecipient', false)
        ->assertSet('recipient', 'perfil@example.org');
});

test('un correo manipulado sin activar la casilla no cambia destinatario', function () {
    User::factory()->create();
    $viewer = makePdfViewer(true);
    $target = User::factory()->create(['email' => 'perfil@example.org']);
    Bus::fake();

    Livewire::actingAs($viewer)
        ->test(PdfActions::class, ['type' => 'user.profile', 'recordId' => $target->id])
        ->call('openEmail')
        ->set('recipient', 'tercero@example.org')
        ->call('send')
        ->assertHasNoErrors();

    Bus::assertDispatched(SendPdfEmailJob::class, fn (SendPdfEmailJob $job) =>
        $job->recipient === 'perfil@example.org'
    );
});
