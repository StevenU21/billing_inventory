<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Dialog;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\Settings;
use Native\Desktop\Facades\System;
use Native\Laravel\Enums\SystemThemesEnum;

class SettingController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $theme = Settings::get('theme', 'system');
        $notificationsGlobal = Settings::get('notifications_global', true);
        $notificationsInventory = Settings::get('notifications_inventory', true);
        $notificationsSystem = Settings::get('notifications_system', true);
        $openAtLogin = false;

        try {
            if (class_exists(App::class)) {
                $openAtLogin = App::openAtLogin();
            }
        } catch (\Throwable $e) {
            Log::error('NativePHP OpenAtLogin Error: ' . $e->getMessage());
        }

        // Backup Settings
        $backupFrequency = Settings::get('backup_frequency', '2hours');
        $backupRetention = Settings::get('backup_retention', 30);
        $backupPath = Settings::get('backup_path');

        // Quotation Settings
        $quotationValidityDays = Settings::get('quotation_validity_days', 7);

        return view('admin.settings.index', [
            'theme' => $theme,
            'openAtLogin' => $openAtLogin,
            'notificationsGlobal' => $notificationsGlobal,
            'notificationsInventory' => $notificationsInventory,
            'notificationsSystem' => $notificationsSystem,
            'appName' => config('app.name'),
            'appVersion' => config('nativephp.version'),
            'appTimezone' => config('app.timezone'),
            'appLocale' => config('app.locale'),

            'backupFrequency' => $backupFrequency,
            'backupRetention' => $backupRetention,
            'backupPath' => $backupPath,
            'quotationValidityDays' => $quotationValidityDays,
        ]);
    }

    public function selectBackupPath(Request $request)
    {
        try {
            $path = Dialog::new()
                ->title('Seleccionar carpeta de respaldos')
                ->folders()
                ->open();

            if ($path) {
                Settings::set('backup_path', $path);
                return response()->json(['status' => 'success', 'path' => $path]);
            }

            return response()->json(['status' => 'cancelled']);
        } catch (\Throwable $e) {
            Log::error('NativePHP Dialog Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al abrir el diálogo'], 500);
        }
    }

    public function updateBackupSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_frequency' => 'required|string|in:1minute,2hours,daily,weekly,manual,on_close',
            'backup_retention' => 'required|integer|in:2,5,10,20,30,40',
        ]);

        Settings::set('backup_frequency', $validated['backup_frequency']);
        Settings::set('backup_retention', $validated['backup_retention']);

        return response()->json(['status' => 'success']);
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark,system'
        ]);

        Settings::set('theme', $validated['theme']);

        try {
            if (class_exists(System::class) && class_exists(SystemThemesEnum::class)) {
                $nativeTheme = match ($validated['theme']) {
                    'light' => SystemThemesEnum::LIGHT,
                    'dark' => SystemThemesEnum::DARK,
                    default => SystemThemesEnum::SYSTEM
                };

                System::theme($nativeTheme);
            }
        } catch (\Throwable $e) {
            Log::error('NativePHP Theme Error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'theme' => $validated['theme']]);
    }

    public function updateOpenAtLogin(Request $request)
    {
        $validated = $request->validate([
            'open_at_login' => 'required|boolean'
        ]);

        try {
            if (class_exists(App::class)) {
                App::openAtLogin($validated['open_at_login']);
            }
        } catch (\Throwable $e) {
            Log::error('NativePHP OpenAtLogin Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar la configuración'], 500);
        }

        return response()->json(['status' => 'success', 'open_at_login' => $validated['open_at_login']]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|in:notifications_global,notifications_inventory,notifications_system',
            'value' => 'required|boolean'
        ]);

        Settings::set($validated['key'], $validated['value']);

        return response()->json(['status' => 'success', 'key' => $validated['key'], 'value' => $validated['value']]);
    }

    public function updateQuotationSettings(Request $request)
    {
        $validated = $request->validate([
            'quotation_validity_days' => 'required|integer|min:1|max:365',
        ]);

        Settings::set('quotation_validity_days', $validated['quotation_validity_days']);

        return response()->json(['status' => 'success']);
    }
}
