<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_number ?? $transaction->id }}</title>
    <!-- Script Tailwind v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background-color: #ffffff;
            color: #1f2937;
            padding: 4px;
            font-size: 10px; /* Ukuran dasar diperkecil khusus thermal */
        }
    </style>
</head>
<body>

    <div class="space-y-3 w-full bg-white p-3 font-mono text-gray-800">
        
        <!-- Header Nota -->
        <div class="mb-3 border-b border-dashed border-gray-400 pb-3 text-center">
            <h3 class="text-sm font-bold tracking-wider uppercase">
                BERSIH JEJAK
            </h3>
            <p class="text-[10px] text-gray-700">
                {{ $transaction->outlet->address ?? '-' }}
            </p>
            <p class="text-[10px] text-gray-700">
                {{ 'WA: ' . ($transaction->outlet->phone_number ?? '-') }}
            </p>
        </div>

        <!-- Informasi Kasir & Tanggal -->
        <div class="mb-3 space-y-1 border-b border-dashed border-gray-400 pb-2 text-[10px]">
            <div class="flex justify-between">
                <span class="text-gray-500">No. Nota:</span>
                <span class="font-bold">{{ $transaction->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-bold">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Informasi Pelanggan -->
        <div class="mb-3 space-y-1 border-b border-dashed border-gray-400 pb-2 text-[10px]">
            <div class="flex justify-between">
                <span class="text-gray-500">Pelanggan:</span>
                <span class="font-bold">
                    {{ $transaction->customer->name ?? '-' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">No. Telepon:</span>
                <span class="font-bold">
                    {{ $transaction->customer->phone_number ?? '-' }}
                </span>
            </div>
        </div>

        <!-- Daftar Item Sepatu, Kondisi & Layanan -->
        <div class="space-y-2 text-[10px]">
            @foreach($transaction->transactionShoes as $index => $shoe)
                <div class="border-b border-dashed border-gray-400 pb-2 last:border-0 last:pb-0">
                    <div class="mb-1 font-bold text-gray-900">
                        #{{ $index + 1 }} {{ $shoe->shoe_brand ?? 'Sepatu' }}
                        @if($shoe->shoe_color)
                            <span class="font-normal text-gray-600">
                                ({{ $shoe->shoe_color }} / {{ $shoe->shoe_size }})
                            </span>
                        @endif
                    </div>

                    @if($shoe->shoe_condition)
                        <div class="mb-1 pl-2 text-[9px] text-gray-600">
                            <span>Kondisi: </span>
                            <span class="font-semibold text-gray-800">{{ $shoe->shoe_condition }}</span>
                        </div>
                    @endif

                    @if($shoe->shoeServices && count($shoe->shoeServices) > 0)
                        <div class="mt-1 space-y-1 pl-2">
                            @foreach($shoe->shoeServices as $serviceItem)
                                <div class="flex justify-between text-gray-700">
                                    <span class="pr-2">↳ {{ $serviceItem->service->name ?? '-' }}</span>
                                    <span class="whitespace-nowrap">Rp {{ number_format($serviceItem->subtotal_price, 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <div class="flex justify-between pt-1 font-semibold text-gray-800 border-t border-gray-200">
                                <span>Subtotal #{{ $index + 1 }}</span>
                                <span>Rp {{ number_format($shoe->shoeServices->sum('subtotal_price'), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="mt-1 pl-2 text-gray-400 italic text-[9px]">
                            ↳ Tidak ada layanan khusus...
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="my-3 border-t border-dashed border-gray-400"></div>

        <!-- Total Keseluruhan, Pembayaran & Catatan -->
        <div class="space-y-1.5 text-[10px]">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-gray-700 uppercase">Total</span>
                <span class="text-sm font-extrabold text-gray-900">
                    Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                </span>
            </div>

            <div class="my-2 border-t border-dashed border-gray-400"></div>

            <div class="flex justify-between">
                <span class="text-gray-500">Status:</span>
                <span class="font-bold uppercase">
                    {{ $transaction->status->name ?? 'Menunggu Pengerjaan' }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-500">Metode:</span>
                <span class="font-bold uppercase">
                    {{ $transaction->payment_method === 'cash' ? 'Tunai (Cash)' : $transaction->payment_method }}
                </span>
            </div>

            <div class="flex justify-between">
                <span class="text-gray-500">Pembayaran:</span>
                <span class="{{ $transaction->payment_status === 'paid' ? 'font-bold text-green-600' : 'font-bold text-orange-600' }}">
                    {{ $transaction->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
                </span>
            </div>

            @if($transaction->notes)
                <div class="mt-2 flex flex-col gap-0.5 text-[10px]">
                    <span class="text-gray-500">Catatan:</span>
                    <span class="text-gray-800 italic">{{ $transaction->notes }}</span>
                </div>
            @endif
        </div>

        <!-- Footer & QR Code -->
        <div class="mt-3 border-t border-dashed border-gray-400 pt-3 text-center text-[9px] text-gray-600 space-y-2">
            <div class="flex items-center justify-center gap-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=55x55&data=http://bersih-jejak.test/tracking/{{ $transaction->invoice_number }}" alt="QR Code" width="55" height="55">
                <p class="text-left text-[9px] leading-tight text-gray-600">
                    Scan QR untuk cek progress cucian sepatu Anda.
                </p>
            </div>
            
            <div class="pt-2 space-y-0.5 border-t border-gray-200">
                <p class="font-semibold">Terima kasih atas kepercayaan Anda!</p>
                <p class="text-[8px] text-gray-400">Simpan struk ini sebagai bukti pengambilan.</p>
            </div>
        </div>

    </div>

</body>
</html>