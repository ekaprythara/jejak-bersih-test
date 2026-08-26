<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Check,
    CreditCard,
    Edit,
    Eye,
    FileText,
    Footprints,
    Loader2,
    Pencil,
    ReceiptText,
    Store,
    User,
    Wrench,
} from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { h, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    index,
    updateShoeDetail,
    updateShoeStatus,
} from '@/routes/transactions';
import { PaymentStatusEnum } from '@/types/data-types';
import type {
    PaginatedResponse,
    StatusType,
    TransactionShoesType,
    TransactionType,
} from '@/types/data-types';
import 'dayjs/locale/id';

dayjs.extend(relativeTime);
dayjs.locale('id');

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Transaksi',
                href: index(),
            },
        ],
    },
});

// Tipe ekstensi untuk opsi status pengerjaan sepatu yang memiliki ID
type ShoeStatusOption = StatusType & { id: number; isFinalStep: boolean };

const isDetailOpen = ref(false);
const isEditOpen = ref(false);
const selectedTransaction = ref<TransactionType | null>(null);

// State untuk Indikator Loading per Sepatu saat Update Stepper
const updatingShoeId = ref<number | null>(null);

// State & Form untuk Modal Edit Detail Sepatu
const isEditShoeOpen = ref(false);
const editingShoe = ref<TransactionShoesType | null>(null);

const shoeForm = useForm({
    shoe_id: null as number | null,
    shoe_brand: '',
    shoe_color: '',
    shoe_size: '',
    shoe_condition: '',
});

// Menerima data dari Controller
const { transactions, shoeStatuses } = defineProps<{
    transactions: PaginatedResponse<TransactionType>;
    shoeStatuses: ShoeStatusOption[];
}>();

// Open Detail Modal (Read-Only)
const handleViewDetail = (transaction: TransactionType) => {
    selectedTransaction.value = transaction;
    isDetailOpen.value = true;
};

// Open Edit Modal (Interactive Edit Status & Detail)
const handleViewEdit = (transaction: TransactionType) => {
    selectedTransaction.value = transaction;
    isEditOpen.value = true;
};

// Open Modal Popup Edit Detail Sepatu
const handleOpenEditShoe = (shoe: TransactionShoesType) => {
    editingShoe.value = shoe;
    shoeForm.shoe_id = shoe.id;
    shoeForm.shoe_brand = shoe.shoe_brand || '';
    shoeForm.shoe_color = shoe.shoe_color || '';
    shoeForm.shoe_size = shoe.shoe_size ? String(shoe.shoe_size) : '';
    shoeForm.shoe_condition = shoe.shoe_condition || '';
    shoeForm.clearErrors();
    isEditShoeOpen.value = true;
};

// Handle Submit Edit Detail Sepatu
const submitEditShoe = () => {
    if (!selectedTransaction.value || !editingShoe.value) {
        return;
    }

    shoeForm.patch(updateShoeDetail(selectedTransaction.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditShoeOpen.value = false;

            // Sync data lokal agar UI langsung terupdate
            if (selectedTransaction.value && editingShoe.value) {
                const targetShoe =
                    selectedTransaction.value.transaction_shoes?.find(
                        (s) => s.id === editingShoe.value?.id,
                    );
                if (targetShoe) {
                    targetShoe.shoe_brand = shoeForm.shoe_brand;
                    targetShoe.shoe_color = shoeForm.shoe_color;
                    targetShoe.shoe_size = shoeForm.shoe_size;
                    targetShoe.shoe_condition = shoeForm.shoe_condition;
                }
            }
        },
    });
};

// Handle Update Status Pengerjaan Sepatu via Stepper
const handleUpdateShoeStatus = (shoeId: number, newStatusId: number) => {
    if (!selectedTransaction.value) {
        return;
    }

    updatingShoeId.value = shoeId;

    router.patch(
        updateShoeStatus(selectedTransaction.value.id),
        {
            shoe_id: shoeId,
            status_id: newStatusId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Sync status di state lokal modal agar badge & stepper animasi langsung berpindah
                if (selectedTransaction.value) {
                    const targetShoe =
                        selectedTransaction.value.transaction_shoes?.find(
                            (s) => s.id === shoeId,
                        );

                    if (targetShoe) {
                        targetShoe.status_id = newStatusId;
                        const matchedStatus = shoeStatuses?.find(
                            (st) => st.id === newStatusId,
                        );

                        if (matchedStatus && targetShoe.status) {
                            targetShoe.status.name = matchedStatus.name;
                        }
                    }
                }
            },
            onFinish: () => {
                updatingShoeId.value = null;
            },
        },
    );
};

// print nota
// Fungsi Print PDF menggunakan URL string langsung
function handlePrintPdf() {
    if (!selectedTransaction.value?.id) return;

    // Membangun URL secara langsung tanpa fungsi route()
    const url = `/transactions/${selectedTransaction.value.id}/print-pdf`;

    // Buka di tab baru browser
    window.open(url, '_blank');
}

// Formatter Rupiah
const formatRupiah = (val: string | number) => {
    const numericValue = typeof val === 'string' ? parseFloat(val) : val;

    if (isNaN(numericValue)) {
        return 'Rp 0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(numericValue);
};

// Definisi Kolom TanStack Table
const columns: ColumnDef<TransactionType>[] = [
    {
        accessorKey: 'invoice_number',
        header: 'No. Invoice',
        cell: (info) => info.getValue() ?? '-',
    },
    {
        id: 'customer_name',
        header: 'Pelanggan',
        cell: ({ row }) => row.original.customer?.name ?? '-',
    },
    {
        accessorFn: (row) => row.overdue_date,
        id: 'overdue_date',
        header: 'Tenggat Selesai',
        cell: ({ row }) => {
            const overdueDate = row.original.overdue_date;

            return overdueDate ? dayjs(overdueDate).fromNow() : '-';
        },
    },
    {
        id: 'status_name',
        header: 'Status Transaksi',
        cell: ({ row }) => {
            const status = row.original.status?.name;

            return status
                ? h(Badge, { variant: 'secondary' }, () => status)
                : '-';
        },
    },
    {
        accessorKey: 'payment_status',
        header: 'Status Bayar',
        cell: ({ row }) => {
            const status = row.original.payment_status;
            const isPaid = status === PaymentStatusEnum.Paid;

            return h(
                Badge,
                {
                    variant: isPaid ? 'default' : 'destructive',
                    class: 'capitalize',
                },
                () => (isPaid ? 'Lunas' : 'Belum Bayar'),
            );
        },
    },
    {
        accessorKey: 'total_price',
        header: 'Total Biaya',
        cell: (info) => formatRupiah(info.getValue() as string),
    },
    {
        id: 'actions',
        header: 'Aksi',
        cell: ({ row }) => {
            return h('div', { class: 'flex items-center gap-1.5' }, [
                // Tombol Lihat (Detail)
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors',
                        onClick: () => handleViewDetail(row.original),
                    },
                    () => h(Eye, { size: 16 }),
                ),
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-slate-500 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/30',
                        onClick: () => handleViewEdit(row.original),
                    },
                    () => h(Edit, { class: 'h-4 w-4' }),
                ),
            ]);
        },
    },
];

console.log(transactions.data[0]);
</script>

<template>
    <Head title="Transaksi" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto bg-[#F8FAFC] p-4"
    >
        <div
            class="rounded-xl border border-sidebar-border/70 bg-white p-8 dark:border-sidebar-border dark:bg-sidebar"
        >
            <div class="flex flex-col gap-5">
                <!-- DataTable Component -->
                <DataTable
                    :columns="columns"
                    :data="transactions.data"
                    searchable
                />
            </div>
        </div>
    </div>

    <!-- Modal Detail Transaksi (Read Only) -->
    <Dialog v-model:open="isDetailOpen">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <FileText class="h-5 w-5 text-primary" />
                    Detail Transaksi
                </DialogTitle>
                <DialogDescription></DialogDescription>
            </DialogHeader>

            <div v-if="selectedTransaction" class="space-y-4 py-2">
                <!-- Ringkasan Invoice & Total -->
                <div
                    class="flex items-center justify-between rounded-lg bg-slate-100 p-4 dark:bg-slate-900"
                >
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            No. Invoice
                        </p>
                        <p
                            class="text-lg font-bold text-slate-800 dark:text-slate-100"
                        >
                            {{ selectedTransaction.invoice_number }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-muted-foreground">
                            Total Tagihan
                        </p>
                        <p class="text-lg font-bold text-primary">
                            {{ formatRupiah(selectedTransaction.total_price) }}
                        </p>
                    </div>
                </div>

                <!-- Information Grid: Pelanggan & Outlet -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border p-3">
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                        >
                            <User class="h-4 w-4" /> Pelanggan
                        </div>
                        <p
                            class="font-medium text-slate-900 dark:text-slate-100"
                        >
                            {{ selectedTransaction.customer?.name ?? '-' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{
                                selectedTransaction.customer?.phone_number ??
                                '-'
                            }}
                        </p>
                    </div>

                    <div class="rounded-lg border p-3">
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                        >
                            <Store class="h-4 w-4" /> Outlet
                        </div>
                        <p
                            class="font-medium text-slate-900 dark:text-slate-100"
                        >
                            {{ selectedTransaction.outlet?.name ?? '-' }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{
                                selectedTransaction.outlet?.phone_number ?? '-'
                            }}
                        </p>
                    </div>
                </div>

                <div class="rounded-lg border p-3">
                    <div
                        class="mb-2 flex items-center gap-1.5 text-xs font-bold text-muted-foreground"
                    >
                        <ReceiptText class="h-4 w-4" /> Invoice
                    </div>

                    <div class="flex gap-1">
                        <Button variant="secondary" @click="handlePrintPdf">
                            Print Invoice
                        </Button>
                        <Button class="bg-green-500 text-white">
                            <img
                                src="https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/whatsapp/mono.svg"
                                alt="WhatsApp"
                                class="h-4 w-4"
                            />Kirim WhatsApp</Button
                        >
                    </div>
                </div>

                <!-- Status & Pembayaran -->
                <div class="space-y-3 rounded-lg border p-3">
                    <div
                        class="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                    >
                        <CreditCard class="h-4 w-4" /> Pembayaran & Status
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Status Transaksi
                            </p>
                            <Badge variant="secondary" class="mt-1 font-medium">
                                {{ selectedTransaction.status?.name ?? '-' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Status Bayar
                            </p>
                            <Badge
                                :variant="
                                    selectedTransaction.payment_status ===
                                    PaymentStatusEnum.Paid
                                        ? 'success'
                                        : 'destructive'
                                "
                                class="mt-1 capitalize"
                            >
                                {{
                                    selectedTransaction.payment_status ===
                                    PaymentStatusEnum.Paid
                                        ? 'Lunas'
                                        : 'Belum Bayar'
                                }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Metode Bayar
                            </p>
                            <p
                                class="mt-1 font-medium text-slate-800 capitalize dark:text-slate-200"
                            >
                                {{ selectedTransaction.payment_method }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Daftar Sepatu & Progress Status -->
                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between text-xs font-semibold text-muted-foreground"
                    >
                        <span class="flex items-center gap-1.5">
                            <Footprints class="h-4 w-4" /> Progress Pengerjaan
                            Sepatu
                        </span>
                        <span
                            >{{
                                selectedTransaction.transaction_shoes?.length ||
                                0
                            }}
                            Item</span
                        >
                    </div>

                    <div
                        v-if="
                            selectedTransaction.transaction_shoes &&
                            selectedTransaction.transaction_shoes.length > 0
                        "
                        class="space-y-3"
                    >
                        <div
                            v-for="(
                                shoe, index
                            ) in selectedTransaction.transaction_shoes as TransactionShoesType[]"
                            :key="shoe.id || index"
                            class="rounded-lg border p-4 dark:border-slate-800"
                        >
                            <div
                                class="flex items-start justify-between border-b pb-3 dark:border-slate-800"
                            >
                                <div>
                                    <h4
                                        class="font-bold text-slate-900 dark:text-slate-100"
                                    >
                                        {{ shoe.shoe_brand }}
                                    </h4>
                                    <p class="text-xs text-muted-foreground">
                                        Warna: {{ shoe.shoe_color }} | Ukuran:
                                        {{ shoe.shoe_size || '-' }}
                                    </p>
                                </div>
                                <Badge
                                    v-if="shoe.status?.name"
                                    variant="outline"
                                    class="border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ shoe.status.name }}
                                </Badge>
                            </div>

                            <p
                                v-if="shoe.shoe_condition"
                                class="mt-2 text-xs text-muted-foreground"
                            >
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-300"
                                    >Kondisi Awal:</span
                                >
                                {{ shoe.shoe_condition }}
                            </p>

                            <!-- Services List -->
                            <div class="mt-3 space-y-1.5">
                                <p
                                    class="flex items-center gap-1 text-[11px] font-medium text-muted-foreground"
                                >
                                    <Wrench class="h-3 w-3" /> Layanan Dipilih:
                                </p>
                                <div
                                    v-if="
                                        shoe.shoe_services &&
                                        shoe.shoe_services.length > 0
                                    "
                                    class="divide-y rounded-md bg-slate-50 p-2.5 text-xs dark:divide-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        v-for="serviceItem in shoe.shoe_services"
                                        :key="serviceItem.id"
                                        class="flex items-center justify-between py-1 first:pt-0 last:pb-0"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    serviceItem.service?.name ??
                                                    '-'
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    serviceItem.service
                                                        ?.estimated_days
                                                "
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                Estimasi:
                                                {{
                                                    serviceItem.service
                                                        .estimated_days
                                                }}
                                                Hari
                                            </p>
                                        </div>
                                        <p
                                            class="font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                formatRupiah(
                                                    serviceItem.subtotal_price,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Tidak ada layanan terdaftar
                                </p>
                            </div>

                            <!-- Read-Only Stepper Pengerjaan Sepatu -->
                            <div
                                class="my-4 w-full rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div
                                    class="flex w-full items-start justify-between"
                                >
                                    <template
                                        v-for="(st, sIdx) in shoeStatuses"
                                        :key="st.id"
                                    >
                                        <div
                                            class="z-10 flex min-w-15 flex-col items-center gap-1.5"
                                        >
                                            <div
                                                class="relative flex items-center justify-center"
                                            >
                                                <!-- Pulse Ping: Tampil jika step pengerjaan SEPATU INI aktif & belum final -->
                                                <div
                                                    v-if="
                                                        st.step ===
                                                            shoe.status?.step &&
                                                        !st.isFinalStep
                                                    "
                                                    class="absolute inline-flex h-11 w-11 animate-ping rounded-full bg-blue-400/40 dark:bg-blue-500/30"
                                                />

                                                <!-- Lingkaran Indicator -->
                                                <div
                                                    class="relative flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all duration-300"
                                                    :class="[
                                                        st.step ===
                                                        shoe.status?.step
                                                            ? st.isFinalStep
                                                                ? 'scale-105 bg-emerald-500 text-white shadow-lg ring-4 shadow-emerald-500/30 ring-emerald-100 dark:ring-emerald-950'
                                                                : 'scale-105 bg-blue-600 text-white shadow-lg ring-4 shadow-blue-500/30 ring-blue-100 dark:ring-blue-950'
                                                            : st.step <
                                                                (shoe.status
                                                                    ?.step || 0)
                                                              ? 'bg-emerald-500 text-white shadow-xs'
                                                              : 'border border-slate-300 bg-white text-slate-400 dark:border-slate-700 dark:bg-slate-800',
                                                    ]"
                                                >
                                                    <!-- Centang jika: Step Lampau OR (Step Aktif Sepatu & isFinalStep = true) -->
                                                    <Check
                                                        v-if="
                                                            st.step <
                                                                (shoe.status
                                                                    ?.step ||
                                                                    0) ||
                                                            (st.step ===
                                                                shoe.status
                                                                    ?.step &&
                                                                st.isFinalStep)
                                                        "
                                                        class="h-4 w-4 stroke-[2.5]"
                                                    />
                                                    <!-- Angka jika belum selesai -->
                                                    <span v-else>{{
                                                        st.step
                                                    }}</span>
                                                </div>
                                            </div>

                                            <!-- Label Nama Status -->
                                            <span
                                                class="max-w-20 text-center text-[11px] leading-tight font-medium transition-colors"
                                                :class="[
                                                    st.step ===
                                                    shoe.status?.step
                                                        ? st.isFinalStep
                                                            ? 'font-bold text-emerald-600 dark:text-emerald-400'
                                                            : 'animate-pulse font-bold text-blue-600 dark:text-blue-400'
                                                        : st.step <
                                                            (shoe.status
                                                                ?.step || 0)
                                                          ? 'font-semibold text-emerald-600 dark:text-emerald-400'
                                                          : 'text-slate-500 dark:text-slate-400',
                                                ]"
                                            >
                                                {{ st.name }}
                                            </span>
                                        </div>

                                        <!-- Garis Penghubung antar Step -->
                                        <div
                                            v-if="
                                                sIdx < shoeStatuses.length - 1
                                            "
                                            class="mt-4 h-0.5 min-w-5 flex-1 transition-colors duration-300"
                                            :class="[
                                                st.step <
                                                    (shoe.status?.step || 0) ||
                                                (st.step ===
                                                    shoe.status?.step &&
                                                    st.isFinalStep)
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-200 dark:bg-slate-700',
                                            ]"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground">
                        Catatan / Keterangan
                    </p>
                    <p
                        class="rounded-md border bg-slate-50/50 p-3 text-sm text-slate-700 dark:bg-slate-900/50 dark:text-slate-300"
                    >
                        {{
                            selectedTransaction.notes ||
                            'Tidak ada catatan tambahan'
                        }}
                    </p>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="isDetailOpen = false"
                    >Tutup</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Modal Edit Transaksi (Interactive Stepper & Quick Edit Shoe Detail) -->
    <Dialog v-model:open="isEditOpen">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Edit class="h-5 w-5 text-amber-600" />
                    Edit Progress & Detail Sepatu
                </DialogTitle>
                <DialogDescription>
                    Klik lingkaran status pada stepper untuk mengubah progress
                    pengerjaan sepatu, atau klik ikon pensil untuk mengubah
                    informasi sepatu.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedTransaction" class="space-y-4 py-2">
                <!-- Ringkasan Invoice & Total -->
                <div
                    class="flex items-center justify-between rounded-lg bg-slate-100 p-4 dark:bg-slate-900"
                >
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            No. Invoice
                        </p>
                        <p
                            class="text-lg font-bold text-slate-800 dark:text-slate-100"
                        >
                            {{ selectedTransaction.invoice_number }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-muted-foreground">
                            Total Tagihan
                        </p>
                        <p class="text-lg font-bold text-primary">
                            {{ formatRupiah(selectedTransaction.total_price) }}
                        </p>
                    </div>
                </div>

                <!-- Information Grid: Pelanggan & Outlet -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border p-3">
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                        >
                            <User class="h-4 w-4" /> Pelanggan
                        </div>
                        <p
                            class="font-medium text-slate-900 dark:text-slate-100"
                        >
                            {{ selectedTransaction.customer?.name ?? '-' }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{
                                selectedTransaction.customer?.phone_number ??
                                '-'
                            }}
                        </p>
                    </div>

                    <div class="rounded-lg border p-3">
                        <div
                            class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                        >
                            <Store class="h-4 w-4" /> Outlet
                        </div>
                        <p
                            class="font-medium text-slate-900 dark:text-slate-100"
                        >
                            {{ selectedTransaction.outlet?.name ?? '-' }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{
                                selectedTransaction.outlet?.phone_number ?? '-'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Status & Pembayaran -->
                <div class="space-y-3 rounded-lg border p-3">
                    <div
                        class="flex items-center gap-1.5 text-xs font-semibold text-muted-foreground"
                    >
                        <CreditCard class="h-4 w-4" /> Pembayaran & Status
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Status Transaksi
                            </p>
                            <Badge variant="secondary" class="mt-1 font-medium">
                                {{ selectedTransaction.status?.name ?? '-' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Status Bayar
                            </p>
                            <Badge
                                :variant="
                                    selectedTransaction.payment_status ===
                                    PaymentStatusEnum.Paid
                                        ? 'success'
                                        : 'destructive'
                                "
                                class="mt-1 capitalize"
                            >
                                {{
                                    selectedTransaction.payment_status ===
                                    PaymentStatusEnum.Paid
                                        ? 'Lunas'
                                        : 'Belum Bayar'
                                }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Metode Bayar
                            </p>
                            <p
                                class="mt-1 font-medium text-slate-800 capitalize dark:text-slate-200"
                            >
                                {{ selectedTransaction.payment_method }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Daftar Sepatu & Progress Status (Interactive) -->
                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between text-xs font-semibold text-muted-foreground"
                    >
                        <span class="flex items-center gap-1.5">
                            <Footprints class="h-4 w-4" /> Progress Pengerjaan
                            Sepatu
                        </span>
                        <span
                            >{{
                                selectedTransaction.transaction_shoes?.length ||
                                0
                            }}
                            Item</span
                        >
                    </div>

                    <div
                        v-if="
                            selectedTransaction.transaction_shoes &&
                            selectedTransaction.transaction_shoes.length > 0
                        "
                        class="space-y-3"
                    >
                        <div
                            v-for="(
                                shoe, index
                            ) in selectedTransaction.transaction_shoes as TransactionShoesType[]"
                            :key="shoe.id || index"
                            class="rounded-lg border p-4 dark:border-slate-800"
                        >
                            <div
                                class="flex items-start justify-between border-b pb-3 dark:border-slate-800"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4
                                            class="font-bold text-slate-900 dark:text-slate-100"
                                        >
                                            {{ shoe.shoe_brand }}
                                        </h4>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-6 w-6 text-slate-400 hover:bg-slate-100 hover:text-amber-600 dark:hover:bg-slate-800"
                                            title="Edit Detail Sepatu"
                                            @click="handleOpenEditShoe(shoe)"
                                        >
                                            <Pencil class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Warna: {{ shoe.shoe_color }} | Ukuran:
                                        {{ shoe.shoe_size || '-' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div
                                        v-if="updatingShoeId === shoe.id"
                                        class="flex items-center gap-1 text-xs text-blue-600"
                                    >
                                        <Loader2
                                            class="h-3.5 w-3.5 animate-spin"
                                        />
                                        <span>Menyimpan...</span>
                                    </div>

                                    <Badge
                                        v-if="shoe.status?.name"
                                        variant="outline"
                                        class="border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{ shoe.status.name }}
                                    </Badge>
                                </div>
                            </div>

                            <p
                                v-if="shoe.shoe_condition"
                                class="mt-2 text-xs text-muted-foreground"
                            >
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-300"
                                    >Kondisi Awal:</span
                                >
                                {{ shoe.shoe_condition }}
                            </p>

                            <!-- Services List -->
                            <div class="mt-3 space-y-1.5">
                                <p
                                    class="flex items-center gap-1 text-[11px] font-medium text-muted-foreground"
                                >
                                    <Wrench class="h-3 w-3" /> Layanan Dipilih:
                                </p>
                                <div
                                    v-if="
                                        shoe.shoe_services &&
                                        shoe.shoe_services.length > 0
                                    "
                                    class="divide-y rounded-md bg-slate-50 p-2.5 text-xs dark:divide-slate-800 dark:bg-slate-900"
                                >
                                    <div
                                        v-for="serviceItem in shoe.shoe_services"
                                        :key="serviceItem.id"
                                        class="flex items-center justify-between py-1 first:pt-0 last:pb-0"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    serviceItem.service?.name ??
                                                    '-'
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    serviceItem.service
                                                        ?.estimated_days
                                                "
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                Estimasi:
                                                {{
                                                    serviceItem.service
                                                        .estimated_days
                                                }}
                                                Hari
                                            </p>
                                        </div>
                                        <p
                                            class="font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            {{
                                                formatRupiah(
                                                    serviceItem.subtotal_price,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="text-xs text-muted-foreground italic"
                                >
                                    Tidak ada layanan terdaftar
                                </p>
                            </div>

                            <!-- Stepper Progress Interactive -->
                            <div
                                class="my-4 w-full rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <p
                                    class="mb-3 text-[11px] font-semibold text-muted-foreground"
                                >
                                    Klik lingkaran status untuk memperbarui
                                    progress:
                                </p>

                                <div
                                    class="flex w-full items-start justify-between"
                                >
                                    <template
                                        v-for="(st, sIdx) in shoeStatuses &&
                                        shoeStatuses.length > 0
                                            ? shoeStatuses
                                            : [
                                                  { id: 1, name: 'Antrian' },
                                                  {
                                                      id: 2,
                                                      name: 'Proses Cuci',
                                                  },
                                                  {
                                                      id: 3,
                                                      name: 'Pengeringan',
                                                  },
                                                  { id: 4, name: 'Selesai' },
                                              ]"
                                        :key="st.id || sIdx"
                                    >
                                        <button
                                            type="button"
                                            :disabled="
                                                updatingShoeId === shoe.id
                                            "
                                            @click="
                                                handleUpdateShoeStatus(
                                                    shoe.id,
                                                    st.id,
                                                )
                                            "
                                            class="group z-10 flex min-w-15 cursor-pointer flex-col items-center gap-1.5 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <div
                                                class="relative flex items-center justify-center"
                                            >
                                                <div
                                                    v-if="
                                                        shoe.status_id === st.id
                                                    "
                                                    class="absolute inline-flex h-11 w-11 animate-ping rounded-full bg-blue-400/40 dark:bg-blue-500/30"
                                                />
                                                <div
                                                    class="relative flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all duration-300 group-hover:scale-110"
                                                    :class="[
                                                        shoe.status_id === st.id
                                                            ? 'scale-105 bg-blue-600 text-white shadow-lg ring-4 shadow-blue-500/30 ring-blue-100 dark:ring-blue-950'
                                                            : st.id <
                                                                shoe.status_id
                                                              ? 'bg-emerald-500 text-white shadow-xs'
                                                              : 'border border-slate-300 bg-white text-slate-400 group-hover:border-blue-400 dark:border-slate-700 dark:bg-slate-800',
                                                    ]"
                                                >
                                                    <Check
                                                        v-if="
                                                            st.id <
                                                            shoe.status_id
                                                        "
                                                        class="h-4 w-4 stroke-[2.5]"
                                                    />
                                                    <span v-else>{{
                                                        sIdx + 1
                                                    }}</span>
                                                </div>
                                            </div>

                                            <span
                                                class="max-w-20 text-center text-[11px] leading-tight font-medium transition-colors"
                                                :class="[
                                                    shoe.status_id === st.id
                                                        ? 'font-bold text-blue-600 dark:text-blue-400'
                                                        : st.id < shoe.status_id
                                                          ? 'font-semibold text-emerald-600 dark:text-emerald-400'
                                                          : 'text-slate-500 group-hover:text-slate-800 dark:text-slate-400 dark:group-hover:text-slate-200',
                                                ]"
                                            >
                                                {{ st.name }}
                                            </span>
                                        </button>

                                        <div
                                            v-if="
                                                sIdx <
                                                (shoeStatuses &&
                                                shoeStatuses.length > 0
                                                    ? shoeStatuses
                                                    : [1, 2, 3, 4]
                                                ).length -
                                                    1
                                            "
                                            class="mt-4 h-0.5 min-w-5 flex-1 transition-colors duration-300"
                                            :class="[
                                                st.id < shoe.status_id
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-200 dark:bg-slate-700',
                                            ]"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div class="space-y-1">
                    <p class="text-xs font-medium text-muted-foreground">
                        Catatan / Keterangan
                    </p>
                    <p
                        class="rounded-md border bg-slate-50/50 p-3 text-sm text-slate-700 dark:bg-slate-900/50 dark:text-slate-300"
                    >
                        {{
                            selectedTransaction.notes ||
                            'Tidak ada catatan tambahan'
                        }}
                    </p>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="isEditOpen = false"
                    >Tutup</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Modal Form Popup Edit Detail Sepatu -->
    <Dialog v-model:open="isEditShoeOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Wrench class="h-5 w-5 text-primary" />
                    Edit Detail Sepatu
                </DialogTitle>
                <DialogDescription>
                    Ubah informasi dasar sepatu jika terjadi kesalahan input.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitEditShoe" class="space-y-4 py-2">
                <!-- Input Brand / Merk -->
                <div class="space-y-1.5">
                    <label
                        class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Merek / Brand Sepatu <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="shoeForm.shoe_brand"
                        type="text"
                        required
                        class="w-full rounded-md border border-slate-300 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none dark:border-slate-700"
                        placeholder="Contoh: Nike Air Jordan"
                    />
                    <p
                        v-if="shoeForm.errors.shoe_brand"
                        class="text-xs text-red-500"
                    >
                        {{ shoeForm.errors.shoe_brand }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Input Warna -->
                    <div class="space-y-1.5">
                        <label
                            class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Warna <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="shoeForm.shoe_color"
                            type="text"
                            required
                            class="w-full rounded-md border border-slate-300 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none dark:border-slate-700"
                            placeholder="Contoh: Putih / Hitam"
                        />
                        <p
                            v-if="shoeForm.errors.shoe_color"
                            class="text-xs text-red-500"
                        >
                            {{ shoeForm.errors.shoe_color }}
                        </p>
                    </div>

                    <!-- Input Ukuran -->
                    <div class="space-y-1.5">
                        <label
                            class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Ukuran (Size)
                        </label>
                        <input
                            v-model="shoeForm.shoe_size"
                            type="text"
                            class="w-full rounded-md border border-slate-300 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none dark:border-slate-700"
                            placeholder="Contoh: 42"
                        />
                        <p
                            v-if="shoeForm.errors.shoe_size"
                            class="text-xs text-red-500"
                        >
                            {{ shoeForm.errors.shoe_size }}
                        </p>
                    </div>
                </div>

                <!-- Input Kondisi Awal -->
                <div class="space-y-1.5">
                    <label
                        class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Kondisi Awal Sepatu
                    </label>
                    <textarea
                        v-model="shoeForm.shoe_condition"
                        rows="3"
                        class="w-full rounded-md border border-slate-300 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-primary focus:outline-none dark:border-slate-700"
                        placeholder="Contoh: Noda lumpur di outsole, minus lem terkelupas..."
                    ></textarea>
                    <p
                        v-if="shoeForm.errors.shoe_condition"
                        class="text-xs text-red-500"
                    >
                        {{ shoeForm.errors.shoe_condition }}
                    </p>
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditShoeOpen = false"
                        :disabled="shoeForm.processing"
                    >
                        Batal
                    </Button>
                    <Button type="submit" :disabled="shoeForm.processing">
                        {{
                            shoeForm.processing
                                ? 'Menyimpan...'
                                : 'Simpan Perubahan'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
