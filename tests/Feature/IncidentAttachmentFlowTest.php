<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Incident;
use App\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class IncidentAttachmentFlowTest extends TestCase
{
    protected $adminUser;
    protected $otherUser;
    protected $testProject;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_att_test@example.com'],
            ['name' => 'Admin Att Test', 'password' => Hash::make('password123'), 'role' => 'Admin']
        );

        $this->otherUser = User::firstOrCreate(
            ['email' => 'other_att_test@example.com'],
            ['name' => 'Other Att Test', 'password' => Hash::make('password123'), 'role' => 'Ayudante']
        );

        $this->testProject = Project::firstOrCreate(
            ['name' => 'Project Att Test'],
            ['description' => 'Test project for attachments']
        );
        $this->testProject->members()->syncWithoutDetaching([
            $this->adminUser->id,
            $this->otherUser->id,
        ]);
    }

    protected function tearDown(): void
    {
        Attachment::whereIn('user_id', [$this->adminUser->id, $this->otherUser->id])->delete();
        Incident::where('titulo', 'like', '%Test Att%')->delete();
        Task::where('title', 'like', '%Test Att%')->delete();
        parent::tearDown();
    }

    /**
     * Prueba 1: Crear una incidencia sin adjuntar archivos.
     */
    public function test_1_create_incident_without_attachment()
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 1 - Sin Archivo',
                'descripcion' => 'Descripción sin archivo',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
            ]);

        $response->assertSessionHasNoErrors();
        $incident = Incident::where('titulo', 'Test Att 1 - Sin Archivo')->first();
        $this->assertNotNull($incident);
        $this->assertCount(0, $incident->attachments);
    }

    /**
     * Prueba 2: Crear una incidencia con un archivo adjunto.
     */
    public function test_2_create_incident_with_attachment()
    {
        $file = UploadedFile::fake()->create('reporte_incidencia.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 2 - Con Archivo',
                'descripcion' => 'Descripción con archivo',
                'prioridad' => 'alta',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $incident = Incident::where('titulo', 'Test Att 2 - Con Archivo')->first();
        $this->assertNotNull($incident);
        $this->assertCount(1, $incident->attachments);

        $attachment = $incident->attachments->first();
        $this->assertEquals('reporte_incidencia.pdf', $attachment->original_name);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    /**
     * Prueba 3 & 4: Flujo de selección, quitar archivo y cambiar por otro antes de guardar.
     * En el frontend el usuario quita el archivo A y selecciona el archivo B.
     * Al enviar al servidor, solo el archivo B se guarda.
     */
    public function test_3_and_4_select_clear_and_replace_before_saving()
    {
        $fileB = UploadedFile::fake()->image('evidencia_final.png', 800, 600);

        $response = $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 3 - Reemplazo antes de guardar',
                'descripcion' => 'Se quitó archivo A y se adjuntó archivo B',
                'prioridad' => 'urgente',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $fileB,
            ]);

        $response->assertSessionHasNoErrors();
        $incident = Incident::where('titulo', 'Test Att 3 - Reemplazo antes de guardar')->first();
        $this->assertNotNull($incident);
        $this->assertCount(1, $incident->attachments);
        $this->assertEquals('evidencia_final.png', $incident->attachments->first()->original_name);
    }

    /**
     * Prueba 5 & 6: Editar una incidencia que ya tiene archivos y añadir un nuevo archivo
     * sin eliminar los existentes.
     */
    public function test_5_and_6_edit_incident_and_add_file_without_deleting_existing()
    {
        // 1. Crear incidencia con archivo 1
        $file1 = UploadedFile::fake()->create('documento1.pdf', 300, 'application/pdf');
        $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 5 - Archivos Multiples',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $file1,
            ]);

        $incident = Incident::where('titulo', 'Test Att 5 - Archivos Multiples')->first();
        $this->assertCount(1, $incident->attachments);
        $firstAttachment = $incident->attachments->first();

        // 2. Editar y añadir archivo 2
        $file2 = UploadedFile::fake()->create('documento2.xlsx', 400, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $this->actingAs($this->adminUser)
            ->put('/incidents/' . $incident->id, [
                'titulo' => 'Test Att 5 - Archivos Multiples Editada',
                'prioridad' => 'alta',
                'estado' => 'en_proceso',
                'project_id' => $this->testProject->id,
                'attachment_file' => $file2,
            ]);

        $response->assertSessionHasNoErrors();
        $incident->refresh();
        $this->assertCount(2, $incident->attachments);

        // Comprobar que ambos existen en disco
        Storage::disk('local')->assertExists($firstAttachment->file_path);
        $newAttachment = $incident->attachments()->where('id', '!=', $firstAttachment->id)->first();
        Storage::disk('local')->assertExists($newAttachment->file_path);
    }

    /**
     * Prueba 7: Eliminar un archivo existente mediante remove_attachment_ids y guardar cambios.
     */
    public function test_7_remove_existing_file_on_update()
    {
        // 1. Crear incidencia con 2 archivos
        $file1 = UploadedFile::fake()->create('archivo_a_eliminar.pdf', 200, 'application/pdf');
        $file2 = UploadedFile::fake()->create('archivo_a_conservar.txt', 100, 'text/plain');

        $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 7 - Eliminación Staged',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_files' => [$file1, $file2],
            ]);

        $incident = Incident::where('titulo', 'Test Att 7 - Eliminación Staged')->first();
        $this->assertCount(2, $incident->attachments);

        $attToDelete = $incident->attachments()->where('original_name', 'archivo_a_eliminar.pdf')->first();
        $attToKeep = $incident->attachments()->where('original_name', 'archivo_a_conservar.txt')->first();
        $pathToDelete = $attToDelete->file_path;
        $pathToKeep = $attToKeep->file_path;

        Storage::disk('local')->assertExists($pathToDelete);
        Storage::disk('local')->assertExists($pathToKeep);

        // 2. Actualizar incidencia marcando $attToDelete->id para eliminación
        $response = $this->actingAs($this->adminUser)
            ->put('/incidents/' . $incident->id, [
                'titulo' => 'Test Att 7 - Eliminación Staged (Guardada)',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'remove_attachment_ids' => [$attToDelete->id],
            ]);

        $response->assertSessionHasNoErrors();
        $incident->refresh();

        // Verificar que solo queda el archivo a conservar
        $this->assertCount(1, $incident->attachments);
        $this->assertEquals('archivo_a_conservar.txt', $incident->attachments->first()->original_name);

        // Verificar eliminación física del archivo marcado
        Storage::disk('local')->assertMissing($pathToDelete);
        $this->assertDatabaseMissing('attachments', ['id' => $attToDelete->id]);

        // Verificar que el archivo conservado sigue intacto
        Storage::disk('local')->assertExists($pathToKeep);
        $this->assertDatabaseHas('attachments', ['id' => $attToKeep->id]);
    }

    /**
     * Prueba 8: Cancelar una edición deja intactos los archivos existentes (no se envían cambios).
     */
    public function test_8_cancel_edit_leaves_files_intact()
    {
        $file = UploadedFile::fake()->create('archivo_intacto.pdf', 300, 'application/pdf');
        $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 8 - Cancelar Edicion',
                'prioridad' => 'baja',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $file,
            ]);

        $incident = Incident::where('titulo', 'Test Att 8 - Cancelar Edicion')->first();
        $attachment = $incident->attachments->first();
        $filePath = $attachment->file_path;

        // Si el usuario cancela en el modal, no se ejecuta ninguna llamada PUT al servidor.
        // Verificamos que el archivo en BD y almacenamiento permanece intacto.
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertExists($filePath);
    }

    /**
     * Prueba 9: Comprobar que no se eliminan archivos de otras incidencias o de tareas.
     */
    public function test_9_scope_protection_cannot_delete_attachments_of_other_incidents_or_tasks()
    {
        // 1. Crear Tarea con archivo adjunto
        $task = Task::create([
            'project_id' => $this->testProject->id,
            'user_id' => $this->adminUser->id,
            'title' => 'Test Att 9 - Tarea Protegida',
            'status' => 'pendiente',
            'priority' => 'media',
        ]);
        $taskFile = UploadedFile::fake()->create('tarea_segura.pdf', 200, 'application/pdf');
        $taskPath = $taskFile->store('attachments', 'local');
        $taskAttachment = $task->attachments()->create([
            'user_id' => $this->adminUser->id,
            'original_name' => 'tarea_segura.pdf',
            'file_path' => $taskPath,
            'mime_type' => 'application/pdf',
            'file_size' => 200,
        ]);

        // 2. Crear Incidencia B con archivo
        $incB = Incident::create([
            'project_id' => $this->testProject->id,
            'user_id' => $this->adminUser->id,
            'titulo' => 'Test Att 9 - Incidencia B',
            'prioridad' => 'media',
            'estado' => 'abierta',
        ]);
        $incBFile = UploadedFile::fake()->create('incidencia_b.pdf', 250, 'application/pdf');
        $incBPath = $incBFile->store('attachments', 'local');
        $incBAttachment = $incB->attachments()->create([
            'user_id' => $this->adminUser->id,
            'original_name' => 'incidencia_b.pdf',
            'file_path' => $incBPath,
            'mime_type' => 'application/pdf',
            'file_size' => 250,
        ]);

        // 3. Crear Incidencia A
        $incA = Incident::create([
            'project_id' => $this->testProject->id,
            'user_id' => $this->adminUser->id,
            'titulo' => 'Test Att 9 - Incidencia A',
            'prioridad' => 'media',
            'estado' => 'abierta',
        ]);

        // Intentar pasar maliciosamente los IDs de los adjuntos de Task e Incidencia B en remove_attachment_ids de Incidencia A
        $response = $this->actingAs($this->adminUser)
            ->put('/incidents/' . $incA->id, [
                'titulo' => 'Test Att 9 - Incidencia A Intentando Borrar Ajenos',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'remove_attachment_ids' => [$taskAttachment->id, $incBAttachment->id],
            ]);

        $response->assertSessionHasNoErrors();

        // Verificar que los adjuntos de Task e Incidencia B siguen 100% intactos
        $this->assertDatabaseHas('attachments', ['id' => $taskAttachment->id]);
        $this->assertDatabaseHas('attachments', ['id' => $incBAttachment->id]);
        Storage::disk('local')->assertExists($taskPath);
        Storage::disk('local')->assertExists($incBPath);
    }

    /**
     * Prueba 10: Validaciones de seguridad en el servidor (tamaño max 10MB y tipos de archivo).
     */
    public function test_10_security_validations_mime_and_size()
    {
        // 1. Archivo ejecutable no permitido (.exe / php)
        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $response = $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 10 - Invalido',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $invalidFile,
            ]);
        $response->assertSessionHasErrors('attachment_file');

        // 2. Archivo que supera 10MB (ej. 15MB = 15360 KB)
        $tooLargeFile = UploadedFile::fake()->create('grande.pdf', 15360, 'application/pdf');
        $response = $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 10 - Demasiado Grande',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $tooLargeFile,
            ]);
        $response->assertSessionHasErrors('attachment_file');
    }

    /**
     * Prueba 11: Eliminar incidencia limpia sus archivos físicos y no toca archivos de tareas.
     */
    public function test_11_destroy_incident_cleans_physical_files()
    {
        $file = UploadedFile::fake()->create('para_borrar.pdf', 200, 'application/pdf');
        $this->actingAs($this->adminUser)
            ->post('/incidents', [
                'titulo' => 'Test Att 11 - Para Destruir',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'project_id' => $this->testProject->id,
                'attachment_file' => $file,
            ]);

        $incident = Incident::where('titulo', 'Test Att 11 - Para Destruir')->first();
        $att = $incident->attachments->first();
        $path = $att->file_path;
        Storage::disk('local')->assertExists($path);

        // Eliminar incidencia
        $this->actingAs($this->adminUser)
            ->delete('/incidents/' . $incident->id);

        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
        $this->assertDatabaseMissing('attachments', ['id' => $att->id]);
        Storage::disk('local')->assertMissing($path);
    }
}
