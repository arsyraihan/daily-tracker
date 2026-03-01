<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Superadmin\Divisi\DivisiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DivisiController extends Controller
{
    protected $divisiService;

    public function __construct(DivisiService $divisiService)
    {
        $this->divisiService = $divisiService;
    }

    public function index()
    {
        return Inertia::render('SuperAdmin/Divisi/Index', [
            'divisi' => $this->divisiService->getAllDivisi()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string'
        ]);

        $this->divisiService->createDivisi($request->all());

        return redirect()->back()->with('success', 'Divisi berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string'
        ]);

        $this->divisiService->updateDivisi($id, $request->all());

        return redirect()->back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->divisiService->deleteDivisi($id);

        return redirect()->back()->with('success', 'Divisi berhasil dihapus.');
    }
}
