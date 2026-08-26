<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Outlet;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPdf\Facades\Pdf;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['expenseCategory', 'outlet', 'user.role', 'user'])
            ->latest('expense_date');

        // Cara Elegan Laravel 1: Menggunakan when() & filled()
        $query->when($request->filled('outlet_id'), function ($q) use ($request) {
            $q->where('outlet_id', $request->outlet_id);
        });

        $query->when($request->filled('category_id'), function ($q) use ($request) {
            $q->where('expense_category_id', $request->category_id);
        });

        $expenses = $query->paginate(100)->withQueryString();

        return inertia('Expense', [
            'expenses' => $expenses,
            'expenseCategories' => ExpenseCategory::all(),
            'outlets' => Outlet::all(),
            'categories' => ExpenseCategory::all(),
            'filters' => $request->only(['outlet_id', 'category_id'])
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

    public function printPdf(Request $request)
    {
        // 1. Ambil data filter dari form frontend
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $outletId = $request->input('outlet_id');
        $categoryId = $request->input('expense_category_id');

        $selectedOutlet = Outlet::find($outletId, ['id', 'name']);

        // 2. Query data pengeluaran berdasarkan filter
        $expenses = Expense::with(['expenseCategory', 'outlet', 'user'])
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('expense_date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('expense_date', '<=', $endDate);
            })
            ->when($outletId && $outletId !== 'all', function ($query) use ($outletId) {
                $query->where('outlet_id', $outletId);
            })
            ->when($categoryId && $categoryId !== 'all', function ($query) use ($categoryId) {
                $query->where('expense_category_id', $categoryId);
            })
            ->orderBy('expense_date', 'desc')
            ->get();

        // 3. Render ke PDF
        return Pdf::view('prints.expense-report', [
            'expenses'  => $expenses,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'outletId' => $outletId,
            'selectedOutlet' => $selectedOutlet
        ])
            ->landscape() // <<-- Menjadikan orientasi Landscape
            ->format('a4')
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->setNodeBinary('/Users/ekapriyanthara/Library/Application Support/Herd/config/nvm/versions/node/v22.23.1/bin/node')
                    ->setNpmBinary('/Users/ekapriyanthara/Library/Application Support/Herd/config/nvm/versions/node/v22.23.1/bin/npm');
            })
            ->inline("laporan-pengeluaran.pdf");
    }
}
