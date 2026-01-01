<?php

namespace Tests\Feature\Admin;

use App\Models\Folder;
use App\Models\Media;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->admin()->create();

        SystemConfig::create(['content_mode' => 'cms', 'setup_completed' => true]);
        SystemConfig::clearCache();

        Storage::fake('public');
    }

    public function test_guests_cannot_access_media_index()
    {
        $response = $this->get(route('admin.media.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_media_index()
    {
        $response = $this->actingAs($this->user)->get(route('admin.media.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('admin/media/Index'));
    }

    public function test_media_index_returns_json_when_requested()
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('admin.media.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['files', 'folders', 'currentFolder']);
    }

    public function test_media_index_can_filter_by_folder()
    {
        $folder = Folder::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.media.index', ['folder_id' => $folder->id]));

        $response->assertStatus(200);
        $response->assertJson(['currentFolder' => ['id' => $folder->id]]);
    }

    public function test_users_can_upload_media()
    {
        $file = UploadedFile::fake()->image('test-image.jpg', 100, 100);

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.store'), [
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'File uploaded successfully.']);
    }

    public function test_upload_requires_file()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_users_can_view_media_details()
    {
        $media = $this->createMedia();

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.media.show', $media));

        $response->assertStatus(200);
        $response->assertJsonStructure(['file']);
    }

    public function test_users_can_update_media()
    {
        $media = $this->createMedia();

        $response = $this->actingAs($this->user)
            ->patchJson(route('admin.media.update', $media), [
                'name' => 'updated-name.jpg',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'File updated successfully.']);
    }

    public function test_users_can_move_media_to_folder()
    {
        $media = $this->createMedia();
        $folder = Folder::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('admin.media.update', $media), [
                'folder_id' => $folder->id,
            ]);

        $response->assertStatus(200);

        $media->refresh();
        $this->assertEquals($folder->id, $media->folder_id);
    }

    public function test_users_can_delete_media()
    {
        $media = $this->createMedia();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('admin.media.destroy', $media));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'File deleted successfully.']);
    }

    public function test_users_can_bulk_delete_media()
    {
        $media1 = $this->createMedia();
        $media2 = $this->createMedia();

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.bulk-destroy'), [
                'ids' => [$media1->id, $media2->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => '2 file(s) deleted successfully.']);
    }

    public function test_users_can_create_folder()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.folders.store'), [
                'name' => 'New Folder',
            ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Folder created successfully.']);

        $this->assertDatabaseHas('folders', ['name' => 'New Folder']);
    }

    public function test_users_can_create_nested_folder()
    {
        $parentFolder = Folder::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.folders.store'), [
                'name' => 'Child Folder',
                'parent_id' => $parentFolder->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('folders', [
            'name' => 'Child Folder',
            'parent_id' => $parentFolder->id,
        ]);
    }

    public function test_users_can_delete_empty_folder()
    {
        $folder = Folder::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('admin.media.folders.destroy', $folder));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Folder deleted successfully.']);

        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
    }

    public function test_cannot_delete_folder_with_files()
    {
        $folder = Folder::factory()->create();
        $this->createMedia(['folder_id' => $folder->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('admin.media.folders.destroy', $folder));

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Cannot delete folder with contents.']);

        $this->assertDatabaseHas('folders', ['id' => $folder->id]);
    }

    public function test_cannot_delete_folder_with_subfolders()
    {
        $parentFolder = Folder::factory()->create();
        Folder::factory()->create(['parent_id' => $parentFolder->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('admin.media.folders.destroy', $parentFolder));

        $response->assertStatus(422);

        $this->assertDatabaseHas('folders', ['id' => $parentFolder->id]);
    }

    public function test_folder_creation_requires_name()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('admin.media.folders.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    private function createMedia(array $attributes = []): Media
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->user->addMedia($file)
            ->usingFileName('test-'.time().'.jpg')
            ->toMediaCollection('uploads');

        if (! empty($attributes)) {
            $media->update($attributes);
            $media->refresh();
        }

        return $media;
    }
}
