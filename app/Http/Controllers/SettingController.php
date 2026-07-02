<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        if (! auth()->user()->hasRole('Superadmin')) {
            abort(403);
        }

        return view('settings.index');
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
            'app_favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico', 'max:1024'],
            'paraf_prod' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'paraf_eng' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'paraf_qc' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        // Save text settings
        Setting::updateOrCreate(['key' => 'app_name'], ['value' => $request->app_name]);
        Setting::updateOrCreate(['key' => 'company_name'], ['value' => $request->company_name]);

        // Process logo upload
        if ($request->hasFile('app_logo')) {
            $oldLogo = setting('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'app_logo'], ['value' => $path]);
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

        return back()->with('success', 'Konfigurasi identitas sistem berhasil diperbarui.');
    }
}
