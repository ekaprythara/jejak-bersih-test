<script setup lang="ts">
import { Head, Form, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Eye,
    Edit,
    Trash2,
    MoveUpRight,
    FileUp,
    CalendarIcon,
    FunnelX,
} from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import dayjs from 'dayjs';
import pickBy from 'lodash/pickBy';
import { h, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

import { Calendar } from '@/components/ui/calendar';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, index, store, update, print } from '@/routes/expenses'; // Tambahkan route update di sini
import type {
    ExpenseCategoryType,
    ExpenseType,
    OutletType,
    PaginatedResponse,
} from '@/types/data-types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengeluaran',
                href: index(),
            },
        ],
    },
});

const page = usePage();
const isAdmin = page.props.auth.isAdmin;

const previewImage = ref<string | null>(null);
const editPreviewImage = ref<string | null>(null);
const selectedExpense = ref<ExpenseType | null>(null);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isModalOpen = ref(false);

function handleView(id: number) {
    const expense = expenses.data.find((item) => item.id === id);

    if (expense) {
        selectedExpense.value = expense;
        isViewModalOpen.value = true;
    }
}

function handleEdit(id: number) {
    const expense = expenses.data.find((item) => item.id === id);

    if (expense) {
        selectedExpense.value = expense;
        // Inisialisasi preview gambar dengan gambar dari Cloudinary jika ada
        editPreviewImage.value = expense.image_url || null;
        isEditModalOpen.value = true;
    }
}

function handleDelete(id: number) {
    const expense = expenses.data.find((item) => item.id === id);

    if (expense) {
        selectedExpense.value = expense;
        isDeleteModalOpen.value = true;
    }
}

const isDeleting = ref(false);

// Eksekusi request DELETE ke backend
function confirmDelete() {
    if (!selectedExpense.value) {
        return;
    }

    isDeleting.value = true;

    router.delete(destroy({ id: selectedExpense.value.id }), {
        onSuccess: () => {
            toast.success('Data Pengeluaran berhasil dihapus!');
            isDeleteModalOpen.value = false;
            selectedExpense.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus data pengeluaran.');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}

function handleImagePreview(event: Event, isEdit = false) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        const url = URL.createObjectURL(target.files[0]);

        if (isEdit) {
            editPreviewImage.value = url;
        } else {
            previewImage.value = url;
        }
    }
}

function handleSuccess() {
    toast.success('Data Pengeluaran Berhasil Disimpan!');
    isModalOpen.value = false;
    previewImage.value = null;
}

function handleEditSuccess() {
    toast.success('Data Pengeluaran Berhasil Diperbarui!');
    isEditModalOpen.value = false;
    editPreviewImage.value = null;
    selectedExpense.value = null;
}

const { expenses, expenseCategories, outlets, categories, filters } =
    defineProps<{
        expenses: PaginatedResponse<ExpenseType>;
        expenseCategories: ExpenseCategoryType[];
        outlets: OutletType[];
        filters: ExpenseType;
        categories: ExpenseCategoryType[];
    }>();

// 2. Set default value dengan MENCARI object yang sesuai dengan parameter URL
// 1. Set default value dari props filters (jika tidak ada / null, fallback ke 'all')
const selectedOutlet = ref<string>(
    filters.outlet_id ? String(filters.outlet_id) : 'all',
);

const selectedCategory = ref<string>(
    filters.category_id ? String(filters.category_id) : 'all',
);

const showResetSorting = ref(false);
// 2. Watch perubahan nilai selectedOutlet dan selectedCategory
watch([selectedOutlet, selectedCategory], ([newOutlet, newCategory]) => {
    const params: Record<string, string | null> = {
        outlet_id: newOutlet !== 'all' ? newOutlet : null,
        category_id: newCategory !== 'all' ? newCategory : null,
    };

    // Filter hapus key yang nilainya null/undefined agar URL tetap bersih
    const cleanParams = pickBy(
        params,
        (value) => value !== null && value !== undefined,
    );

    router.get(index().url, cleanParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    if (selectedOutlet.value === 'all' && selectedCategory.value === 'all') {
        showResetSorting.value = false;
    } else {
        showResetSorting.value = true;
    }
});

const isExportDialogOpen = ref(false);

// Ref penampung untuk Calendar Shadcn
const startDatePicker = ref();
const endDatePicker = ref();

// Form Inertia standar (Bebas penamaan, kita beri nama `form`)
const todayDate = new Date().toISOString().split('T')[0];
// Hasil: "2026-08-27"
const form = useForm({
    start_date: todayDate || '',
    end_date: todayDate || '',
    outlet_id: 'all',
    expense_category_id: 'all',
});

function submitExport() {
    // Susun query parameters dari form
    const params = new URLSearchParams({
        start_date: form.start_date || '',
        end_date: form.end_date || '',
        outlet_id: form.outlet_id || 'all',
        expense_category_id: form.expense_category_id || 'all',
    }).toString();

    // Buka PDF di tab baru browser (Native PDF Preview)
    window.open(`${print().url}?${params}`, '_blank');
    isExportDialogOpen.value = false;
}

const columns: ColumnDef<ExpenseType>[] = [
    {
        accessorKey: 'expense_date',
        header: 'Tanggal',
        cell: (info) => {
            const dateStr = info.getValue() as string;
            return dateStr ? dayjs(dateStr).format('YYYY-MM-DD') : '-';
        },
    },
    {
        accessorFn: (row) => row.expense_category.category_name,
        id: 'category_name',
        header: 'Kategori',
        cell: (info) =>
            h(
                'span',
                {
                    class: 'inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 whitespace-nowrap',
                },
                info.getValue() as string,
            ),
    },
    {
        accessorFn: (row) =>
            row.outlet?.name ? `${row.outlet.name}` : 'Non-Cabang',
        id: 'outlet_name',
        header: 'Cabang',
        cell: (info) =>
            h(
                'span',
                {
                    class: 'inline-flex items-center rounded-full bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700 whitespace-nowrap',
                },
                info.getValue() as string,
            ),
    },
    {
        accessorKey: 'description',
        header: 'Deskripsi',
        enableSorting: false,
    },
    {
        accessorKey: 'amount',
        header: 'Jumlah',
        cell: ({ row }) => {
            const amount = row.getValue('amount') as number;
            const formattedAmount = new Intl.NumberFormat('id-ID').format(
                amount,
            );

            return h('div', { class: 'flex flex-col' }, [
                h(
                    'span',
                    { class: 'font-semibold text-gray-900' },
                    formattedAmount,
                ),
                h(
                    'span',
                    {
                        class: 'text-xs font-medium text-gray-400 tracking-wider',
                    },
                    'IDR',
                ),
            ]);
        },
    },
    {
        accessorKey: 'user_id',
        header: 'Dibuat Oleh',
        cell: ({ row }) => {
            const role = row.original.user.role.name;
            const name = row.original.user.name;

            return h('div', { class: 'flex flex-col' }, [
                h('span', { class: 'font-semibold text-gray-900' }, `${name}`),
                h(
                    'span',
                    {
                        class: 'text-xs font-medium text-gray-400 tracking-wider uppercase',
                    },
                    `${role}`,
                ),
            ]);
        },
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const expense = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                // Tombol Lihat (Detail)
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-blue-600 hover:bg-blue-50 hover:text-blue-700',
                        onClick: () => handleView(expense.id),
                    },
                    () => h(Eye, { class: 'h-4 w-4' }),
                ),

                // Tombol Edit
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-amber-600 hover:bg-amber-50 hover:text-amber-700',
                        onClick: () => handleEdit(expense.id),
                    },
                    () => h(Edit, { class: 'h-4 w-4' }),
                ),

                // Tombol Delete
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-red-600 hover:bg-red-50 hover:text-red-700',
                        onClick: () => handleDelete(expense.id),
                    },
                    () => h(Trash2, { class: 'h-4 w-4' }),
                ),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Pengeluaran" />

    <div class="flex h-full flex-1 flex-col gap-4 bg-[#F8FAFC] p-4">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-6">
                <!-- Header Title & Button -->
                <div class="flex flex-row items-center justify-between">
                    <div>
                        <h2
                            class="text-xl font-bold text-gray-800 lg:text-[2.488rem]"
                        >
                            Daftar Pengeluaran
                        </h2>
                        <p class="text-sm text-gray-500 lg:text-[1rem]">
                            Kelola dan pantau pengeluaran operasional outlet
                            cuci sepatu.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:justify-between">
                    <div class="flex flex-col gap-1 lg:flex-row">
                        <!-- Filter Cabang / Outlet -->
                        <div class="flex w-full items-center gap-1 lg:w-fit">
                            <Select v-model="selectedOutlet">
                                <SelectTrigger
                                    id="outlet_id"
                                    class="w-full rounded-lg border-slate-200 lg:w-48"
                                >
                                    <SelectValue placeholder="Semua Cabang" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="all">
                                        Semua Cabang
                                    </SelectItem>
                                    <SelectItem
                                        v-for="outlet in outlets"
                                        :key="outlet.id"
                                        :value="String(outlet.id)"
                                    >
                                        {{ outlet.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Filter Kategori Pengeluaran -->
                        <div class="flex w-full items-center gap-1 lg:w-fit">
                            <Select v-model="selectedCategory">
                                <SelectTrigger
                                    id="expense_category_id"
                                    class="w-full rounded-lg border-slate-200 lg:w-48"
                                >
                                    <SelectValue placeholder="Semua Kategori" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl">
                                    <SelectItem value="all">
                                        Semua Kategori
                                    </SelectItem>
                                    <SelectItem
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="String(category.id)"
                                    >
                                        {{ category.category_name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <Button
                            variant="outline"
                            class="text-red-500 hover:bg-white hover:text-red-500/70"
                            title="Reset Filter"
                            v-if="showResetSorting"
                            @click="
                                router.get(
                                    '/expenses',
                                    {},
                                    { replace: true, preserveState: false },
                                )
                            "
                            ><FunnelX />
                            <span class="lg:hidden">Hapus Sortir</span>
                        </Button>
                    </div>

                    <div class="flex gap-1 lg:ms-auto">
                        <Button
                            @click="isExportDialogOpen = true"
                            class="w-full bg-green-600 text-white lg:w-fit"
                        >
                            Ekspor
                            <span><FileUp /></span>
                        </Button>
                        <Button v-if="isAdmin" @click="isModalOpen = true">
                            Tambah Pengeluaran
                        </Button>
                    </div>
                </div>

                <!-- DataTable Component -->
                <DataTable
                    :columns="columns"
                    :data="expenses.data"
                    searchable
                    searchPlaceholder="Cari di Pengeluaran..."
                />
            </div>
        </div>
    </div>

    <!-- Dialog / Modal Ekspor PDF -->
    <Dialog v-model:open="isExportDialogOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-xl font-bold text-slate-800">
                    Ekspor Data Pengeluaran
                </DialogTitle>
                <DialogDescription>
                    Ekspor data pengeluaran untuk keperluan analisis.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitExport" class="space-y-4 pt-2">
                <!-- Grid 2 Kolom Tanggal -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- 1. Dari Tanggal -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-slate-700">
                            Dari Tanggal
                        </Label>

                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-full justify-start rounded-lg border-slate-200 text-left font-normal"
                                    :class="
                                        !form.start_date &&
                                        'text-muted-foreground'
                                    "
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    <span>
                                        {{
                                            form.start_date
                                                ? dayjs(form.start_date).format(
                                                      'YYYY-MM-DD',
                                                  )
                                                : 'yyyy-mm-dd'
                                        }}
                                    </span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="z-60 w-auto bg-white p-0 shadow-md"
                                align="start"
                            >
                                <Calendar
                                    v-model="startDatePicker"
                                    initial-focus
                                    @update:model-value="
                                        (val) => {
                                            if (val) {
                                                form.start_date = `${val.year}-${String(val.month).padStart(2, '0')}-${String(val.day).padStart(2, '0')}`;
                                            }
                                        }
                                    "
                                />
                            </PopoverContent>
                        </Popover>

                        <InputError :message="form.errors.start_date" />
                    </div>

                    <!-- 2. Sampai Tanggal -->
                    <div class="space-y-1.5">
                        <Label class="text-sm font-medium text-slate-700">
                            Sampai Tanggal
                        </Label>

                        <Popover>
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-full justify-start rounded-lg border-slate-200 text-left font-normal"
                                    :class="
                                        !form.end_date &&
                                        'text-muted-foreground'
                                    "
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    <span>
                                        {{
                                            form.end_date
                                                ? dayjs(form.end_date).format(
                                                      'YYYY-MM-DD',
                                                  )
                                                : 'yyyy-mm-dd'
                                        }}
                                    </span>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="z-60 w-auto bg-white p-0 shadow-md"
                                align="start"
                            >
                                <Calendar
                                    v-model="endDatePicker"
                                    initial-focus
                                    :min-value="startDatePicker"
                                    @update:model-value="
                                        (val) => {
                                            if (val) {
                                                form.end_date = `${val.year}-${String(val.month).padStart(2, '0')}-${String(val.day).padStart(2, '0')}`;
                                            }
                                        }
                                    "
                                />
                            </PopoverContent>
                        </Popover>

                        <InputError :message="form.errors.end_date" />
                    </div>
                </div>

                <!-- 3. Pilih Cabang -->
                <div class="space-y-1.5">
                    <Label
                        for="outlet_id"
                        class="text-sm font-medium text-slate-700"
                    >
                        Pilih Cabang
                    </Label>

                    <Select v-model="form.outlet_id">
                        <SelectTrigger
                            id="outlet_id"
                            class="w-full rounded-lg border-slate-200"
                        >
                            <SelectValue placeholder="Semua Cabang" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl">
                            <SelectItem value="all"> Semua Cabang </SelectItem>
                            <SelectItem
                                v-for="outlet in outlets"
                                :key="outlet.id"
                                :value="String(outlet.id)"
                            >
                                {{ outlet.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="form.errors.outlet_id" />
                </div>

                <!-- 4. Pilih Kategori -->
                <div class="space-y-1.5">
                    <Label
                        for="expense_category_id"
                        class="text-sm font-medium text-slate-700"
                    >
                        Pilih Kategori
                    </Label>

                    <Select v-model="form.expense_category_id">
                        <SelectTrigger
                            id="expense_category_id"
                            class="w-full rounded-lg border-slate-200"
                        >
                            <SelectValue placeholder="Semua Kategori" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl">
                            <SelectItem value="all">
                                Semua Kategori
                            </SelectItem>
                            <SelectItem
                                v-for="category in categories"
                                :key="category.id"
                                :value="String(category.id)"
                            >
                                {{ category.category_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <InputError :message="form.errors.expense_category_id" />
                </div>

                <!-- Action Buttons -->
                <DialogFooter class="flex justify-end gap-2 pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isExportDialogOpen = false"
                        class="rounded-lg border-slate-300 px-5 py-2 text-slate-700 hover:bg-slate-50"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-emerald-600 px-5 py-2 font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Mengekspor...'
                                : 'Ekspor Sekarang'
                        }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Dialog / Modal Tambah Pengeluaran -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-120">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Pengeluaran Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Catat rincian biaya operasional outlet cuci sepatu di sini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="store.form()"
                enctype="multipart/form-data"
                :reset-on-success="[
                    'expense_date',
                    'expense_category_id',
                    'amount',
                    'description',
                    'image',
                ]"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <div class="space-y-1.5">
                    <Label
                        for="expense_date"
                        class="text-xs font-semibold text-gray-700"
                        >Tanggal Pengeluaran</Label
                    >
                    <Input
                        id="expense_date"
                        type="date"
                        name="expense_date"
                        required
                        autofocus
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.expense_date" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="expense_category_id"
                        class="text-xs font-semibold text-gray-700"
                        >Kategori Pengeluaran</Label
                    >
                    <select
                        name="expense_category_id"
                        id="expense_category_id"
                        required
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm text-gray-700 ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                    >
                        <option value="" disabled selected>
                            Pilih Kategori...
                        </option>
                        <option
                            v-for="category in expenseCategories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.category_name }}
                        </option>
                    </select>
                    <InputError :message="errors.expense_category_id" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="amount"
                        class="text-xs font-semibold text-gray-700"
                        >Jumlah (Rp)</Label
                    >
                    <Input
                        id="amount"
                        type="number"
                        name="amount"
                        required
                        placeholder="Contoh: 150000"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.amount" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="description"
                        class="text-xs font-semibold text-gray-700"
                        >Deskripsi / Keterangan</Label
                    >
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Misal: Beli sabun pembersih khusus suede dan sikat..."
                        class="flex w-full rounded-lg border border-input bg-background p-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    ></textarea>
                    <InputError :message="errors.description" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="image"
                        class="text-xs font-semibold text-gray-700"
                        >Lampiran Nota / Struk (Opsional)</Label
                    >

                    <div class="mt-2 w-full">
                        <label
                            for="image"
                            class="group relative flex min-h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-2 text-center transition-all hover:border-gray-400 hover:bg-gray-50"
                        >
                            <template v-if="!previewImage">
                                <div class="flex flex-col items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-white shadow-xs transition-colors group-hover:border-gray-400"
                                    >
                                        <svg
                                            class="h-6 w-6 text-gray-400 transition-colors group-hover:text-gray-600"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <p
                                            class="text-xs font-medium text-gray-600"
                                        >
                                            Choose image or drag and drop it
                                            here.
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            PNG, JPG, dan WEBP. Max 5 MB.
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div
                                    class="relative w-full overflow-hidden rounded-xl border border-gray-100 bg-white p-2"
                                >
                                    <img
                                        :src="previewImage"
                                        alt="Preview Bukti"
                                        class="h-44 w-full object-contain"
                                    />
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <span
                                            class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm"
                                        >
                                            Klik untuk mengganti
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <input
                                id="image"
                                type="file"
                                name="image"
                                accept="image/*"
                                class="hidden"
                                @change="(e) => handleImagePreview(e, false)"
                            />
                        </label>
                    </div>
                    <InputError :message="errors.image" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isModalOpen = false"
                        class="rounded-lg"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 font-semibold hover:bg-blue-700"
                    >
                        {{ processing ? 'Menyimpan...' : 'Simpan Pengeluaran' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog View Pengeluaran -->
    <Dialog v-model:open="isViewModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-lg">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-900">
                    Rincian Pengeluaran
                </DialogTitle>
                <DialogDescription
                    class="text-xs leading-relaxed text-gray-500"
                >
                    Menampilkan rincian lengkap transaksi pengeluaran
                    operasional, termasuk informasi kategori, nominal biaya,
                    cabang outlet, serta lampiran bukti nota.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedExpense" class="space-y-4 pt-2">
                <!-- Grid Informasi Utama dalam Card -->
                <div
                    class="grid grid-cols-2 gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 text-xs"
                >
                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Tanggal</p>
                        <p class="font-semibold text-gray-800">
                            {{
                                dayjs(selectedExpense.expense_date).format(
                                    'DD MMMM YYYY',
                                )
                            }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Kategori</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.expense_category.category_name }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Cabang</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.outlet?.name || 'Non-Cabang' }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Dibuat Oleh</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.user?.name || '-' }}
                        </p>
                    </div>
                </div>

                <!-- Keterangan / Deskripsi -->
                <div class="space-y-1">
                    <p class="text-xs font-medium text-gray-500">
                        Deskripsi / Keterangan
                    </p>
                    <div
                        class="min-h-[60px] rounded-xl border border-gray-100 bg-gray-50/50 p-3 text-xs leading-relaxed text-gray-700"
                    >
                        {{
                            selectedExpense.description ||
                            'Tidak ada keterangan tambahan.'
                        }}
                    </div>
                </div>

                <!-- Highlight Jumlah Pengeluaran -->
                <div
                    class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 p-3.5"
                >
                    <span class="text-xs font-medium text-gray-500"
                        >Total Pengeluaran</span
                    >
                    <span class="text-lg font-bold text-red-600">
                        Rp
                        {{
                            new Intl.NumberFormat('id-ID').format(
                                selectedExpense.amount,
                            )
                        }}
                    </span>
                </div>

                <!-- Lampiran Nota Pembayaran -->
                <div v-if="selectedExpense.image_url" class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-500"
                            >Lampiran Nota Pembayaran</span
                        >
                        <a
                            :href="selectedExpense.image_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 font-medium text-blue-600 transition-colors hover:text-blue-700 hover:underline"
                        >
                            <span>Lihat Gambar Secara Penuh</span>
                            <MoveUpRight :size="13" :stroke-width="1.75" />
                        </a>
                    </div>

                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-2"
                    >
                        <img
                            :src="selectedExpense.image_url"
                            alt="Bukti Pengeluaran"
                            class="max-h-64 w-full rounded-lg object-contain"
                        />
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Dialog Edit Pengeluaran -->
    <Dialog v-model:open="isEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-120">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800">
                    Edit Pengeluaran
                </DialogTitle>
                <DialogDescription class="text-xs text-gray-500">
                    Ubah rincian biaya operasional outlet di sini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedExpense"
                v-bind="
                    update.form({
                        id: selectedExpense.id,
                        expense_date: dayjs(
                            selectedExpense.expense_date,
                        ).format('YYYY-MM-DD'),
                        expense_category_id:
                            selectedExpense.expense_category_id,
                        amount: selectedExpense.amount,
                        description: selectedExpense.description || '',
                    })
                "
                enctype="multipart/form-data"
                v-slot="{ errors, processing }"
                @success="handleEditSuccess"
                class="space-y-4 pt-2"
            >
                <!-- Method Spoofing untuk upload file via HTTP PUT di Laravel/Inertia -->
                <input type="hidden" name="_method" value="PUT" />

                <!-- 1. Tanggal Pengeluaran -->
                <div class="space-y-1.5">
                    <Label
                        for="edit_expense_date"
                        class="text-xs font-semibold text-gray-700"
                        >Tanggal Pengeluaran</Label
                    >
                    <Input
                        id="edit_expense_date"
                        type="date"
                        name="expense_date"
                        required
                        class="h-10 rounded-lg"
                        :default-value="
                            dayjs(selectedExpense.expense_date).format(
                                'YYYY-MM-DD',
                            )
                        "
                    />
                    <InputError :message="errors.expense_date" />
                </div>

                <!-- 2. Kategori Pengeluaran -->
                <div class="space-y-1.5">
                    <Label
                        for="edit_expense_category_id"
                        class="text-xs font-semibold text-gray-700"
                        >Kategori Pengeluaran</Label
                    >
                    <select
                        name="expense_category_id"
                        id="edit_expense_category_id"
                        required
                        :value="selectedExpense.expense_category_id"
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm text-gray-700 ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                    >
                        <option value="" disabled>Pilih Kategori...</option>
                        <option
                            v-for="category in expenseCategories"
                            :key="category.id"
                            :value="category.id"
                        >
                            {{ category.category_name }}
                        </option>
                    </select>
                    <InputError :message="errors.expense_category_id" />
                </div>

                <!-- 3. Jumlah (Rp) -->
                <div class="space-y-1.5">
                    <Label
                        for="edit_amount"
                        class="text-xs font-semibold text-gray-700"
                        >Jumlah (Rp)</Label
                    >
                    <Input
                        id="edit_amount"
                        type="number"
                        name="amount"
                        required
                        placeholder="Contoh: 150000"
                        class="h-10 rounded-lg"
                        :default-value="selectedExpense.amount"
                    />
                    <InputError :message="errors.amount" />
                </div>

                <!-- 4. Deskripsi / Keterangan -->
                <div class="space-y-1.5">
                    <Label
                        for="edit_description"
                        class="text-xs font-semibold text-gray-700"
                        >Deskripsi / Keterangan</Label
                    >
                    <textarea
                        id="edit_description"
                        name="description"
                        rows="3"
                        placeholder="Misal: Beli sabun pembersih khusus suede dan sikat..."
                        :value="selectedExpense.description || ''"
                        class="flex w-full rounded-lg border border-input bg-background p-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    ></textarea>
                    <InputError :message="errors.description" />
                </div>

                <!-- 5. Lampiran Nota / Struk -->
                <div class="space-y-1.5">
                    <Label
                        for="edit_image"
                        class="text-xs font-semibold text-gray-700"
                        >Ganti Lampiran Nota / Struk (Opsional)</Label
                    >
                    <div class="mt-2 w-full">
                        <label
                            for="edit_image"
                            class="group relative flex min-h-40 w-full cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-2 text-center transition-all hover:border-gray-400 hover:bg-gray-50"
                        >
                            <template v-if="!editPreviewImage">
                                <div class="flex flex-col items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-white shadow-xs transition-colors group-hover:border-gray-400"
                                    >
                                        <svg
                                            class="h-6 w-6 text-gray-400 transition-colors group-hover:text-gray-600"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <p
                                            class="text-xs font-medium text-gray-600"
                                        >
                                            Choose image or drag and drop it
                                            here.
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            PNG, JPG, dan WEBP. Max 5 MB.
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div
                                    class="relative w-full overflow-hidden rounded-xl border border-gray-100 bg-white p-2"
                                >
                                    <img
                                        :src="editPreviewImage"
                                        alt="Preview Bukti"
                                        class="h-44 w-full object-contain"
                                    />
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <span
                                            class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm"
                                        >
                                            Klik untuk mengganti foto
                                        </span>
                                    </div>
                                </div>
                            </template>
                            <input
                                id="edit_image"
                                type="file"
                                name="image"
                                accept="image/*"
                                class="hidden"
                                @change="(e) => handleImagePreview(e, true)"
                            />
                        </label>
                    </div>
                    <InputError :message="errors.image" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditModalOpen = false"
                        class="rounded-lg"
                    >
                        Batal
                    </Button>
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-amber-600 font-semibold text-white hover:bg-amber-700"
                    >
                        {{
                            processing ? 'Perbarui...' : 'Perbarui Pengeluaran'
                        }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="isDeleteModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-lg">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-900">
                    Rincian Pengeluaran
                </DialogTitle>
                <DialogDescription
                    class="text-xs leading-relaxed text-gray-500"
                >
                    Menampilkan rincian lengkap transaksi pengeluaran
                    operasional, termasuk informasi kategori, nominal biaya,
                    cabang outlet, serta lampiran bukti nota.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedExpense" class="space-y-4 pt-2">
                <!-- Grid Informasi Utama dalam Card -->
                <div
                    class="grid grid-cols-2 gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 text-xs"
                >
                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Tanggal</p>
                        <p class="font-semibold text-gray-800">
                            {{
                                dayjs(selectedExpense.expense_date).format(
                                    'DD MMMM YYYY',
                                )
                            }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Kategori</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.expense_category.category_name }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Cabang</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.outlet?.name || '-' }}
                        </p>
                    </div>

                    <div class="space-y-0.5">
                        <p class="font-medium text-gray-400">Dibuat Oleh</p>
                        <p class="font-semibold text-gray-800">
                            {{ selectedExpense.user?.name || '-' }}
                        </p>
                    </div>
                </div>

                <!-- Keterangan / Deskripsi -->
                <div class="space-y-1">
                    <p class="text-xs font-medium text-gray-500">
                        Deskripsi / Keterangan
                    </p>
                    <div
                        class="min-h-[60px] rounded-xl border border-gray-100 bg-gray-50/50 p-3 text-xs leading-relaxed text-gray-700"
                    >
                        {{
                            selectedExpense.description ||
                            'Tidak ada keterangan tambahan.'
                        }}
                    </div>
                </div>

                <!-- Highlight Jumlah Pengeluaran -->
                <div
                    class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 p-3.5"
                >
                    <span class="text-xs font-medium text-gray-500"
                        >Total Pengeluaran</span
                    >
                    <span class="text-lg font-bold text-red-600">
                        Rp
                        {{
                            new Intl.NumberFormat('id-ID').format(
                                selectedExpense.amount,
                            )
                        }}
                    </span>
                </div>

                <!-- Lampiran Nota Pembayaran -->
                <div v-if="selectedExpense.image_url" class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-gray-500"
                            >Lampiran Nota Pembayaran</span
                        >
                        <a
                            :href="selectedExpense.image_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1 font-medium text-blue-600 transition-colors hover:text-blue-700 hover:underline"
                        >
                            <span>Lihat Gambar Secara Penuh</span>
                            <MoveUpRight :size="13" :stroke-width="1.75" />
                        </a>
                    </div>

                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-2"
                    >
                        <img
                            :src="selectedExpense.image_url"
                            alt="Bukti Pengeluaran"
                            class="max-h-64 w-full rounded-lg object-contain"
                        />
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2 pt-2 sm:gap-0">
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteModalOpen = false"
                    class="rounded-lg"
                    :disabled="isDeleting"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    @click="confirmDelete"
                    :disabled="isDeleting"
                    class="rounded-lg bg-red-600 font-semibold text-white hover:bg-red-700"
                >
                    {{ isDeleting ? 'Mengeksekusi...' : 'Hapus Data' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
