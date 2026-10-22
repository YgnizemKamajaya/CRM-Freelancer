<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\Setting;
use App\Models\VaultFile;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceUploadRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_options_can_be_unchecked_and_archived_wallet_is_retained(): void
    {
        $wallet = Wallet::create(['name' => 'Archived', 'is_archived' => true]);
        $goal = Goal::create(['title' => 'Laptop', 'type' => 'financial', 'target_amount' => 1000, 'wallet_id' => $wallet->id, 'auto_track' => true, 'is_pinned' => true]);
        $this->unlock()->get(route('goals.edit', $goal))->assertOk()
            ->assertViewHas('wallets', fn ($wallets) => $wallets->contains('id', $wallet->id));
        $this->put(route('goals.update', $goal), ['title' => 'Laptop', 'type' => 'financial', 'target_amount' => 1000, 'wallet_id' => $wallet->id])->assertSessionHasNoErrors();
        $this->assertFalse($goal->fresh()->auto_track);
        $this->assertFalse($goal->fresh()->is_pinned);
        $this->assertSame($wallet->id, $goal->fresh()->wallet_id);
    }

    public function test_goal_banner_is_private_and_replacement_and_removal_clean_up_files(): void
    {
        Storage::fake('local');
        $data = ['title' => 'Travel', 'type' => 'personal', 'target_amount' => 100, 'banner_image' => UploadedFile::fake()->image('banner.png')];
        $this->unlock()->post(route('goals.store'), $data)->assertSessionHasNoErrors();
        $goal = Goal::sole();
        $oldPath = $goal->banner_path;
        Storage::disk('local')->assertExists($oldPath);
        $this->flushSession();
        $this->get(route('goals.banner', $goal))->assertRedirect(route('login'));
        $this->unlock()->get(route('goals.banner', $goal))->assertOk()->assertHeader('content-type', 'image/png');
        $data['banner_image'] = UploadedFile::fake()->image('replacement.png');
        $this->put(route('goals.update', $goal), $data)->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($oldPath);
        $newPath = $goal->fresh()->banner_path;
        Storage::disk('local')->assertExists($newPath);
        unset($data['banner_image']);
        $this->put(route('goals.update', $goal), $data + ['remove_banner' => 1])->assertSessionHasNoErrors();
        $this->assertNull($goal->fresh()->banner_path);
        Storage::disk('local')->assertMissing($newPath);
    }

    public function test_vault_upload_download_and_deletion_are_private(): void
    {
        Storage::fake('local');
        $this->unlock()->post(route('vault.store'), ['files' => [UploadedFile::fake()->image("Sam's photo.png")]])->assertSessionHasNoErrors();
        $file = VaultFile::sole();
        Storage::disk('local')->assertExists($file->path);
        $this->get(route('vault.index'))->assertOk();
        $this->get(route('vault.preview', $file))->assertOk()->assertHeader('content-type', 'image/png');
        $this->flushSession();
        $this->get(route('vault.download', $file))->assertRedirect(route('login'));
        $this->unlock()->get(route('vault.download', $file))->assertOk();
        $this->delete(route('vault.destroy', $file))->assertRedirect();
        Storage::disk('local')->assertMissing($file->path);
        $this->assertDatabaseCount('vault_files', 0);
    }

    public function test_goal_banner_storage_failure_preserves_existing_banner(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('goal-banners/old.png', 'previous banner');
        $goal = Goal::create(['title' => 'Travel', 'type' => 'personal', 'target_amount' => 100, 'banner_path' => 'goal-banners/old.png']);
        $upload = \Mockery::mock(UploadedFile::fake()->image('banner.png'))->makePartial();
        $upload->shouldReceive('store')->once()->andReturn(false);
        $this->unlock()->putJson(route('goals.update', $goal), [
            'title' => 'Travel', 'type' => 'personal', 'target_amount' => 100, 'banner_image' => $upload,
        ])->assertUnprocessable()->assertJsonValidationErrors('banner_image');
        $this->assertSame('goal-banners/old.png', $goal->fresh()->banner_path);
        Storage::disk('local')->assertExists('goal-banners/old.png');
    }

    public function test_goal_banner_database_failure_keeps_old_file_and_removes_new_upload(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('goal-banners/old.png', 'previous banner');
        $goal = Goal::create(['title' => 'Travel', 'type' => 'personal', 'target_amount' => 100, 'banner_path' => 'goal-banners/old.png']);
        Goal::updating(function () {
            throw new \RuntimeException('Simulated save failure');
        });
        $this->unlock()->putJson(route('goals.update', $goal), [
            'title' => 'Travel', 'type' => 'personal', 'target_amount' => 100,
            'banner_image' => UploadedFile::fake()->image('replacement.png'),
        ])->assertStatus(500);
        $this->assertSame('goal-banners/old.png', $goal->fresh()->banner_path);
        Storage::disk('local')->assertExists('goal-banners/old.png');
        $this->assertSame(['goal-banners/old.png'], Storage::disk('local')->allFiles('goal-banners'));
    }

    public function test_vault_does_not_trust_client_mime_or_serve_active_content_inline(): void
    {
        Storage::fake('local');
        $upload = UploadedFile::fake()->createWithContent('pretend.png', '<!DOCTYPE html><html><script>alert(1)</script></html>');
        $this->unlock()->post(route('vault.store'), ['files' => [$upload]])->assertSessionHasNoErrors();
        $file = VaultFile::sole();
        $this->assertFalse($file->isImage());
        $this->get(route('vault.preview', $file))->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('content-disposition', 'attachment; filename=pretend.png');
    }

    public function test_signature_replacement_keeps_old_file_when_new_storage_fails(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('signatures/old.png', 'previous signature');
        $setting = Setting::create(['signature_path' => 'signatures/old.png']);
        $upload = \Mockery::mock(UploadedFile::fake()->image('signature.png'))->makePartial();
        $upload->shouldReceive('store')->once()->andReturn(false);
        $this->unlock()->postJson(route('esignature.store'), ['signature' => $upload])->assertUnprocessable();
        $this->assertSame('signatures/old.png', $setting->fresh()->signature_path);
        Storage::disk('public')->assertExists('signatures/old.png');
    }

    public function test_signature_can_be_saved_and_removed_with_validation(): void
    {
        Storage::fake('public');
        $this->unlock()->postJson(route('esignature.store'), ['signature' => UploadedFile::fake()->image('signature.png')])->assertOk()->assertJsonPath('ok', true);
        $path = Setting::sole()->signature_path;
        Storage::disk('public')->assertExists($path);
        $this->postJson(route('esignature.store'), ['signature' => UploadedFile::fake()->create('script.html', 1, 'text/html')])->assertUnprocessable();
        Storage::disk('public')->assertExists($path);
        $this->deleteJson(route('esignature.destroy'))->assertOk();
        Storage::disk('public')->assertMissing($path);
        $this->assertNull(Setting::sole()->signature_path);
    }
}
