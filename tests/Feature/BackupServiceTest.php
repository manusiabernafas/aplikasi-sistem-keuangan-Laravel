<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_list_and_delete_backup(): void
    {
        $service = app(BackupService::class);

        $filename = $service->create();

        $this->assertMatchesRegularExpression('/^backup_\d{8}_\d{6}\.sql$/', $filename);
        $this->assertTrue(Storage::disk('local')->exists('backups/'.$filename));

        $list = $service->list();
        $this->assertContains($filename, array_column($list, 'name'));

        $service->delete($filename);
        $this->assertFalse(Storage::disk('local')->exists('backups/'.$filename));
    }
}
