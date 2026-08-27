<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran Operasional</title>

    <!-- Menggunakan CDN Tailwind CSS untuk Rendering PDF Browsershot -->
    <script src="https://cdn.tailwindcss.com"></script>

   <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

<style>
        /* Mengatur dasar ukuran font cetak agar pas pada kertas A4 / A5 */
        body {
            font-size: 12px;
  font-family: "Inter", sans-serif;

        }

        @page {
            margin: 10mm;
        }
    </style>
</head>
<body class="bg-white p-6 text-slate-800 antialiased">

    <!-- Header Section -->
    <div class="mb-6 flex items-start justify-between border-b-2 border-slate-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Bersih Jejak</h1>
            @if ($selectedOutlet)
                <p class="text-xs font-medium text-slate-500">
                    Laporan Rekapitulasi Pengeluaran Cabang {{ $selectedOutlet->name }}
                </p>
            @else
                <p class="text-xs font-medium text-slate-500">
                    Laporan Rekapitulasi Pengeluaran Semua Cabang
                </p>
            @endif
        </div>
        <div class="text-right">
            <span class="text-lg font-extrabold uppercase tracking-wider text-blue-600">Pengeluaran</span>
            <p class="mt-1 text-[10px] text-slate-400">
                Dicetak pada {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY HH:mm') }} oleh {{ auth()->user()->name }}
            </p>
        </div>
    </div>

    <!-- Metadata / Filter Card -->
    <div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="flex items-center gap-2">
                <span class=" text-slate-500">Periode Tanggal:</span>
                @if ($startDate === $endDate)
                    <span class="font-semibold text-slate-900">
                    {{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') }} 
                </span>
                @else
 <span class="font-semibold text-slate-900">
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') : 'Awal' }} 
                    –
                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY') : 'Hari Ini' }}
                </span>
                @endif
               
            </div>
            <div class="flex items-center gap-2">
                <span class=" text-slate-500">Total Transaksi:</span>
                <span class="font-semibold text-slate-900">{{ $expenses->count() }} Data Pengeluaran</span>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mb-6 overflow-hidden rounded-lg border border-slate-200">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 text-[10px] uppercase tracking-wider text-slate-600">
                <tr>
                    <th class="px-3 py-2.5 text-center">No.</th>
                    <th class="px-3 py-2.5 text-center">Tanggal</th>
                    <th class="px-3 py-2.5 text-center">Kategori</th>
                    <th class="px-3 py-2.5 text-center">Cabang</th>
                    <th class="px-3 py-2.5 text-left">Deskripsi</th>
                    <th class="px-3 py-2.5 text-right">Jumlah (IDR)</th>
                    <th class="px-3 py-2.5 text-left">Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse ($expenses as $index => $expense)
                    <tr class="even:bg-slate-50/50">
                        <td class="px-3 py-2 text-center font-medium text-slate-500">
                            {{ $index + 1 }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-center font-medium text-slate-700">
                            {{ \Carbon\Carbon::parse($expense->expense_date)->locale('id')->isoFormat('DD-MM-YYYY') }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-[10px] font-semibold text-blue-700">
                                {{ $expense->expenseCategory->category_name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-[10px] font-semibold text-purple-700">
                                {{ $expense->outlet->name ?? 'Non-Cabang' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-600">
                            {{ $expense->description ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-900">
                            <div class="flex items-center justify-between font-bold">
                                <span>Rp.</span>
                                <span>{{ number_format($expense->amount, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="font-bold text-slate-700">{{ $expense->user->name ?? '-' }}</div>
                            <div class="text-[10px] font-semibold uppercase text-slate-400">{{ $expense->user->role->name ?? '-' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-slate-400">
                            Tidak ada data pengeluaran ditemukan pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Total Summary Section -->
    <div class="mb-10 flex justify-end">
        <div class="w-72 rounded-xl border border-red-200 bg-red-50/70 p-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Total Pengeluaran:</span>
                <span class="text-base font-extrabold text-red-600">
                    Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

</body>
</html>