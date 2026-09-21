<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    public function index()
    {
        if (! auth()->user()->hasRole('Superadmin')) {
            abort(403);
        }

        $roles = Role::where('name', '!=', 'Superadmin')->orderBy('name')->get();

        return view('settings.index', compact('roles'));
    }

    public function update(Request $request)
    {
        if (! auth()->user()->hasRole('Superadmin')) {
            abort(403);
        }

        $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'company_name' => ['required', 'string', 'max:100'],
            'app_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'print_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'app_favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico', 'max:1024'],
            'paraf_prod' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'paraf_eng' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'paraf_qc' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            // Maintenance settings
            'maintenance_enabled' => ['nullable', 'in:0,1'],
            'maintenance_title' => ['nullable', 'string', 'max:200'],
            'maintenance_message' => ['nullable', 'string', 'max:1000'],
            'maintenance_end_time' => ['nullable', 'string', 'max:50'],
            'maintenance_notice_enabled' => ['nullable', 'in:0,1'],
            'maintenance_notice_message' => ['nullable', 'string', 'max:500'],
            'maintenance_allowed_roles' => ['nullable', 'array'],
        ]);

        // Save text settings
        Setting::updateOrCreate(['key' => 'app_name'], ['value' => $request->app_name]);
        Setting::updateOrCreate(['key' => 'company_name'], ['value' => $request->company_name]);

        // Save Maintenance Mode Settings
        $maintenanceEnabled = $request->has('maintenance_enabled') ? '1' : '0';
        Setting::updateOrCreate(['key' => 'maintenance_enabled'], ['value' => $maintenanceEnabled]);
        Setting::updateOrCreate(['key' => 'maintenance_title'], ['value' => $request->maintenance_title ?? 'Sistem Dalam Pemeliharaan']);
        Setting::updateOrCreate(['key' => 'maintenance_message'], ['value' => $request->maintenance_message ?? 'Saat ini kami sedang melakukan peningkatan sistem dan pemeliharaan berkala untuk kenyamanan Anda. Sistem akan segera dapat diakses kembali.']);
        Setting::updateOrCreate(['key' => 'maintenance_end_time'], ['value' => $request->maintenance_end_time]);

        // Save Pre-maintenance Notice Settings
        $noticeEnabled = $request->has('maintenance_notice_enabled') ? '1' : '0';
        Setting::updateOrCreate(['key' => 'maintenance_notice_enabled'], ['value' => $noticeEnabled]);
        Setting::updateOrCreate(['key' => 'maintenance_notice_message'], ['value' => $request->maintenance_notice_message]);

        // Save Allowed Roles for Maintenance Bypass
        $allowedRoles = $request->input('maintenance_allowed_roles', []);
        Setting::updateOrCreate(['key' => 'maintenance_allowed_roles'], ['value' => json_encode($allowedRoles)]);

        // Process logo upload
        if ($request->hasFile('app_logo')) {
            $oldLogo = setting('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'app_logo'], ['value' => $path]);
        }

        // Process print logo upload
        if ($request->hasFile('print_logo')) {
            $oldPrintLogo = setting('print_logo');
            if ($oldPrintLogo) {
                Storage::disk('public')->delete($oldPrintLogo);
            }
            $path = $request->file('print_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'print_logo'], ['value' => $path]);
        }

        // Process favicon upload
        if ($request->hasFile('app_favicon')) {
            $oldFav = setting('app_favicon');
            if ($oldFav) {
                Storage::disk('public')->delete($oldFav);
            }
            $path = $request->file('app_favicon')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'app_favicon'], ['value' => $path]);
        }

        // Process paraf uploads
        $parafTypes = ['paraf_prod', 'paraf_eng', 'paraf_qc'];
        foreach ($parafTypes as $parafType) {
            if ($request->hasFile($parafType)) {
                $oldParaf = setting($parafType);
                if ($oldParaf) {
                    Storage::disk('public')->delete($oldParaf);
                }
                $path = $request->file($parafType)->store('settings/paraf', 'public');
                Setting::updateOrCreate(['key' => $parafType], ['value' => $path]);
            }
        }

        return back()->with('success', 'Konfigurasi sistem dan mode pemeliharaan berhasil diperbarui.');
    }
}
