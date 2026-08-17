<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Expense', [
            'expenses' => Expense::latest('expense_date')->with(['expenseCategory', 'outlet', 'user'])->paginate(100),
            'expenseCategories' => ExpenseCategory::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request, CloudinaryService $cloudinary)
    {

        $user = Auth::user();

        $data = $request->validated();

        // Tambahkan user dan outlet otomatis
        $data['user_id'] = $user->id;
        $data['outlet_id'] = $user->outlet_id;


        // Upload gambar ke Cloudinary jika ada file
        if ($request->hasFile('image')) {

            $upload = $cloudinary->upload(
                $request->file('image'),
                // Upload di folder 'expenses'
                'expenses'
            );

            $data['image_url'] = $upload['secure_url'];
            $data['image_public_id'] = $upload['public_id'];
        }


        Expense::create($data);

        return back()->with('success', 'Pengeluaran baru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense, CloudinaryService $cloudinary)
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['outlet_id'] = $user->outlet_id;
        // Upload gambar baru ke Cloudinary jika ada file yang dikirim
        if ($request->hasFile('image')) {

            // 1. Hapus foto lama di Cloudinary jika public_id foto lama ada
            if ($expense->image_public_id) {
                $cloudinary->destroy($expense->image_public_id);
            }

            // 2. Upload foto baru
            $upload = $cloudinary->upload(
                $request->file('image'),
                'expenses'
            );

            $data['image_url'] = $upload['secure_url'];
            $data['image_public_id'] = $upload['public_id'];
        }

        // Update data pengeluaran di database
        $expense->update($data);

        return back()->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense, CloudinaryService $cloudinary)
    {
        // 1. Hapus gambar dari Cloudinary jika ada public_id
        if ($expense->image_public_id) {
            $cloudinary->destroy($expense->image_public_id);
        }

        // 2. Hapus data dari database
        $expense->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus!');
    }
}
