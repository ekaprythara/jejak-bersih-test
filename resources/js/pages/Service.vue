<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { Eye, Edit, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h, ref } from 'vue';
import 'dayjs/locale/id';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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
import { index, store, update, destroy } from '@/routes/services';
import type { PaginatedResponse, ServiceType } from '@/types/data-types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Layanan',
                href: index(),
            },
        ],
    },
});

// State Props & Reaktivitas
const { services } = defineProps<{
    services: PaginatedResponse<ServiceType>;
}>();

const selectedService = ref<ServiceType | null>(null);

// State Modal
const isModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

// Handler Actions
function handleView(id: number) {
    const service = services.data.find((item) => item.id === id);
    if (service) {
        selectedService.value = service;
        isViewModalOpen.value = true;
    }
}

function handleEdit(id: number) {
    const service = services.data.find((item) => item.id === id);
    if (service) {
        selectedService.value = service;
        isEditModalOpen.value = true;
    }
}

function handleDelete(id: number) {
    const service = services.data.find((item) => item.id === id);
    if (service) {
        selectedService.value = service;
        isDeleteModalOpen.value = true;
    }
}

function confirmDelete() {
    if (!selectedService.value) return;

    isDeleting.value = true;

    router.delete(destroy({ id: selectedService.value.id }), {
        onSuccess: () => {
            toast.success('Layanan berhasil dihapus!');
            isDeleteModalOpen.value = false;
            selectedService.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus layanan.');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}

function handleSuccess() {
    toast.success('Data Layanan Berhasil Disimpan!');
    isModalOpen.value = false;
}

function handleEditSuccess() {
    toast.success('Data Layanan Berhasil Diperbarui!');
    isEditModalOpen.value = false;
    selectedService.value = null;
}

// Tanstack Table Columns
const columns: ColumnDef<ServiceType>[] = [
    {
        accessorKey: 'name',
        header: 'Nama',
        cell: (info) =>
            h(
                'span',
                { class: 'font-semibold text-gray-800' },
                `${info.getValue()}`,
            ),
    },
    {
        accessorKey: 'description',
        header: 'Deskripsi',
        cell: (info) => `${info.getValue() || '-'}`,
    },
    {
        accessorKey: 'price',
        header: 'Harga',
        cell: ({ row }) => {
            const price = row.original.price;
            const formattedPrice = new Intl.NumberFormat('id-ID').format(price);

            return h('div', { class: 'flex flex-col' }, [
                h(
                    'span',
                    { class: 'font-semibold text-gray-900' },
                    formattedPrice,
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
        accessorKey: 'estimated_days',
        header: 'Waktu Pengerjaan',
        cell: (info) => `${info.getValue()} hari`,
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const service = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                // Tombol Lihat (Detail)
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors',
                        onClick: () => handleView(service.id),
                    },
                    () => h(Eye, { size: 16 }),
                ),

                // Tombol Edit
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors',
                        onClick: () => handleEdit(service.id),
                    },
                    () => h(Edit, { size: 16 }),
                ),

                // Tombol Delete
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors',
                        onClick: () => handleDelete(service.id),
                    },
                    () => h(Trash2, { size: 16 }),
                ),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Layanan" />

    <div class="flex h-full flex-1 flex-col gap-4 bg-[#F8FAFC] p-4">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-6">
                <!-- Header Title & Button -->
                <div class="flex flex-row items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            Daftar Layanan
                        </h2>
                        <p class="text-xs text-gray-500">
                            Kelola opsi jenis perawatan sepatu yang ditawarkan.
                        </p>
                    </div>
                    <Button
                        @click="isModalOpen = true"
                        class="bg-blue-600 font-semibold hover:bg-blue-700"
                    >
                        + Tambah Layanan
                    </Button>
                </div>

                <!-- DataTable -->
                <DataTable
                    :columns="columns"
                    :data="services.data"
                    searchable
                    searchPlaceholder="Cari Layanan..."
                />
            </div>
        </div>
    </div>

    <!-- Dialog / Modal Tambah Layanan -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Layanan Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Masukkan rincian jenis perawatan sepatu baru di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="store.form()"
                :reset-on-success="[
                    'name',
                    'price',
                    'estimated_days',
                    'description',
                ]"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <div class="space-y-1.5">
                    <Label
                        for="name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Layanan</Label
                    >
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        placeholder="Contoh: Deep Cleaning"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="price"
                        class="text-xs font-semibold text-gray-700"
                        >Harga (Rp)</Label
                    >
                    <Input
                        id="price"
                        type="number"
                        name="price"
                        required
                        placeholder="50000"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.price" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="estimated_days"
                        class="text-xs font-semibold text-gray-700"
                        >Estimasi Pengerjaan (Hari)</Label
                    >
                    <Input
                        id="estimated_days"
                        type="number"
                        name="estimated_days"
                        required
                        placeholder="3"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.estimated_days" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="description"
                        class="text-xs font-semibold text-gray-700"
                        >Deskripsi (Opsional)</Label
                    >
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Pembersihan menyeluruh bagian upper, midsole, dan outsole..."
                        class="flex w-full rounded-lg border border-input bg-background p-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    ></textarea>
                    <InputError :message="errors.description" />
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
                        {{ processing ? 'Menyimpan...' : 'Simpan Layanan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog View Layanan -->
    <Dialog v-model:open="isViewModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Detail Layanan</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Informasi lengkap mengenai opsi perawatan sepatu.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedService" class="space-y-4 pt-2">
                <div
                    class="rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-center"
                >
                    <span
                        class="text-xs font-semibold tracking-wider text-gray-400 uppercase"
                        >Harga Layanan</span
                    >
                    <p class="mt-1 text-2xl font-bold text-blue-600">
                        Rp
                        {{
                            new Intl.NumberFormat('id-ID').format(
                                selectedService.price,
                            )
                        }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div
                        class="rounded-xl border border-gray-100 bg-gray-50/70 p-3"
                    >
                        <p class="font-medium text-gray-400">Nama Layanan</p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedService.name }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-gray-100 bg-gray-50/70 p-3"
                    >
                        <p class="font-medium text-gray-400">
                            Estimasi Pengerjaan
                        </p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedService.estimated_days }} Hari
                        </p>
                    </div>
                </div>

                <div class="space-y-1">
                    <p class="text-xs font-medium text-gray-500">
                        Deskripsi / Keterangan
                    </p>
                    <div
                        class="rounded-xl border border-gray-100 bg-gray-50/50 p-3 text-xs leading-relaxed text-gray-700"
                    >
                        {{
                            selectedService.description ||
                            'Tidak ada deskripsi tambahan.'
                        }}
                    </div>
                </div>
            </div>

            <DialogFooter class="pt-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isViewModalOpen = false"
                    class="rounded-lg"
                >
                    Tutup
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Dialog Edit Layanan -->
    <Dialog v-model:open="isEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Edit Layanan</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Ubah rincian informasi layanan di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedService"
                v-bind="
                    update.form({
                        id: selectedService.id,
                        name: selectedService.name,
                        price: selectedService.price,
                        estimated_days: selectedService.estimated_days,
                        description: selectedService.description || '',
                    })
                "
                v-slot="{ errors, processing }"
                @success="handleEditSuccess"
                class="space-y-4 pt-2"
            >
                <input type="hidden" name="_method" value="PUT" />

                <div class="space-y-1.5">
                    <Label
                        for="edit_name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Layanan</Label
                    >
                    <Input
                        id="edit_name"
                        type="text"
                        name="name"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedService.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_price"
                        class="text-xs font-semibold text-gray-700"
                        >Harga (Rp)</Label
                    >
                    <Input
                        id="edit_price"
                        type="number"
                        name="price"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedService.price"
                    />
                    <InputError :message="errors.price" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_estimated_days"
                        class="text-xs font-semibold text-gray-700"
                        >Estimasi Pengerjaan (Hari)</Label
                    >
                    <Input
                        id="edit_estimated_days"
                        type="number"
                        name="estimated_days"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedService.estimated_days"
                    />
                    <InputError :message="errors.estimated_days" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_description"
                        class="text-xs font-semibold text-gray-700"
                        >Deskripsi (Opsional)</Label
                    >
                    <textarea
                        id="edit_description"
                        name="description"
                        rows="3"
                        :value="selectedService.description || ''"
                        class="flex w-full rounded-lg border border-input bg-background p-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    ></textarea>
                    <InputError :message="errors.description" />
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
                        {{ processing ? 'Perbarui...' : 'Perbarui Layanan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog Konfirmasi Hapus Layanan -->
    <Dialog v-model:open="isDeleteModalOpen">
        <DialogContent class="rounded-2xl p-6 sm:max-w-md">
            <DialogHeader class="space-y-1.5">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600"
                    >
                        <Trash2 :size="20" :stroke-width="2" />
                    </div>
                    <div>
                        <DialogTitle class="text-base font-bold text-gray-900"
                            >Hapus Layanan?</DialogTitle
                        >
                        <DialogDescription class="text-xs text-gray-500">
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedService" class="space-y-3 py-2">
                <div
                    class="space-y-2.5 rounded-xl border border-red-100 bg-red-50/50 p-3.5"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500"
                            >Harga Layanan</span
                        >
                        <span class="text-base font-bold text-red-600">
                            Rp
                            {{
                                new Intl.NumberFormat('id-ID').format(
                                    selectedService.price,
                                )
                            }}
                        </span>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2 border-t border-red-100/60 pt-2 text-xs"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Nama Layanan
                            </p>
                            <p class="font-semibold text-gray-800">
                                {{ selectedService.name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Estimasi
                            </p>
                            <p class="font-semibold text-gray-800">
                                {{ selectedService.estimated_days }} Hari
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2 pt-2 sm:gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isDeleteModalOpen = false"
                    class="w-full rounded-xl text-xs font-medium sm:w-auto"
                    :disabled="isDeleting"
                >
                    Batal
                </Button>
                <Button
                    type="button"
                    @click="confirmDelete"
                    :disabled="isDeleting"
                    class="w-full rounded-xl bg-red-600 text-xs font-semibold text-white shadow-xs hover:bg-red-700 sm:w-auto"
                >
                    {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Layanan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
