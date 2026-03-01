<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Superadmin\Jabatan\JabatanService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JabatanController extends Controller
{
    protected $jabatanService;

    public function __construct(JabatanService $jabatanService)
    {
        $this->jabatanService = $jabatanService;
    }

    public function index()
    {
        return Inertia::render('SuperAdmin/Jabatan/Index', [
            'jabatan' => $this->jabatanService->getAllJabatan()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'level' => 'required|integer|min:1'
        ]);

        $this->jabatanService->createJabatan($request->all());

        return redirect()->back()->with('success', 'Jabatan berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'level' => 'required|integer|min:1'
        ]);

        $this->jabatanService->updateJabatan($id, $request->all());

        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->jabatanService->deleteJabatan($id);

        return redirect()->back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
