<script setup lang="ts">
import { Form, Head, router, usePage } from '@inertiajs/vue3';
import { Eye, Edit, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

import 'dayjs/locale/id';
import { h, ref } from 'vue';
import CountUp from 'vue-countup-v3';
import { toast } from 'vue-sonner';
import DataTable from '@/components/DataTable.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import CardTitle from '@/components/ui/card/CardTitle.vue';
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

import { index, store, update, destroy } from '@/routes/customers';
import type { CustomerType, PaginatedResponse } from '@/types/data-types';
import NavUser from '@/components/NavUser.vue';
import UserInfo from '@/components/UserInfo.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pelanggan',
                href: index(),
            },
        ],
    },
});

dayjs.extend(relativeTime);
dayjs.locale('id');

const { customers, totalCustomers, newCustomers } = defineProps<{
    customers: PaginatedResponse<CustomerType>;
    totalCustomers: number;
    newCustomers: number;
}>();

const selectedCustomer = ref<CustomerType | null>(null);

// State Modals
const isModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

// Handlers Modal Aksi
function handleView(id: number) {
    const customer = customers.data.find((item) => item.id === id);
    if (customer) {
        selectedCustomer.value = customer;
        isViewModalOpen.value = true;
    }
}

function handleEdit(id: number) {
    const customer = customers.data.find((item) => item.id === id);
    if (customer) {
        selectedCustomer.value = customer;
        isEditModalOpen.value = true;
    }
}

function handleDelete(id: number) {
    const customer = customers.data.find((item) => item.id === id);
    if (customer) {
        selectedCustomer.value = customer;
        isDeleteModalOpen.value = true;
    }
}

function confirmDelete() {
    if (!selectedCustomer.value) return;

    isDeleting.value = true;

    router.delete(destroy({ id: selectedCustomer.value.id }), {
        onSuccess: () => {
            toast.success('Pelanggan berhasil dihapus!');
            isDeleteModalOpen.value = false;
            selectedCustomer.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus data pelanggan.');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}

function handleSuccess() {
    toast.success('Data Pelanggan Berhasil Disimpan!');
    isModalOpen.value = false;
}

function handleEditSuccess() {
    toast.success('Data Pelanggan Berhasil Diperbarui!');
    isEditModalOpen.value = false;
    selectedCustomer.value = null;
}

// Columns Definition
const columns: ColumnDef<CustomerType>[] = [
    {
        accessorKey: 'name',
        header: 'Nama',
        cell: (info) => {
            const user = info.row.original;

            return h('div', { class: 'flex flex-row items-center gap-2' }, [
                h(UserInfo, { user: user }),
            ]);
        },
    },
    {
        accessorKey: 'phone_number',
        header: 'No. Telepon',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorFn: (row) => row.created_at,
        id: 'created_at',
        header: 'Bergabung pada',
        cell: ({ getValue }) => {
            const createdAt = getValue<string>();

            return createdAt ? dayjs(createdAt).fromNow() : '-';
        },
    },
    {
        // Access the transactions array directly from the row object
        accessorFn: (row) => row.transactions?.length ?? 0,
        header: 'Jumlah Transaksi',
        // Use getValue() from the cell context parameter
        cell: (info) => `${info.getValue()}`,
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const customer = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                // Tombol Lihat (Detail)
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors',
                        onClick: () => handleView(customer.id),
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
                        onClick: () => handleEdit(customer.id),
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
                        onClick: () => handleDelete(customer.id),
                    },
                    () => h(Trash2, { size: 16 }),
                ),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Pelanggan" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto bg-[#F8FAFC] p-4"
    >
        <!-- Dashboard Stats -->
        <div
            class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-white p-8 shadow-xs dark:border-sidebar-border"
        >
            <div class="grid grid-cols-2 gap-5">
                <Card class="border border-gray-100 p-4 shadow-none">
                    <CardTitle
                        class="text-xs font-semibold tracking-wider text-gray-400 uppercase"
                        >Total Pelanggan</CardTitle
                    >
                    <div class="mt-2 text-2xl font-bold text-gray-800">
                        <CountUp :end-val="totalCustomers" />
                    </div>
                </Card>
                <Card class="border border-gray-100 p-4 shadow-none">
                    <CardTitle
                        class="text-xs font-semibold tracking-wider text-gray-400 uppercase"
                        >Pelanggan Baru</CardTitle
                    >
                    <div class="mt-2 text-2xl font-bold text-blue-600">
                        <CountUp :end-val="newCustomers" />
                    </div>
                </Card>
            </div>
        </div>

        <!-- DataTable Container -->
        <div
            class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-white p-8 shadow-xs dark:border-sidebar-border"
        >
            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            Daftar Pelanggan
                        </h2>
                        <p class="text-xs text-gray-500">
                            Kelola riwayat data pelanggan outlet di sini.
                        </p>
                    </div>
                    <Button
                        @click="isModalOpen = true"
                        class="bg-blue-600 font-semibold hover:bg-blue-700"
                    >
                        + Tambah Pelanggan
                    </Button>
                </div>

                <!-- DataTable -->
                <DataTable
                    :columns="columns"
                    :data="customers.data"
                    searchable
                    searchPlaceholder="Cari Pelanggan..."
                />
            </div>
        </div>
    </div>

    <!-- Dialog / Modal Tambah Pelanggan -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Pelanggan Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Masukkan informasi pelanggan baru di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="store.form()"
                :reset-on-success="['name', 'phone_number']"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <div class="space-y-1.5">
                    <Label
                        for="name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Pelanggan</Label
                    >
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Contoh: Santika"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon / WhatsApp</Label
                    >
                    <Input
                        id="phone_number"
                        type="text"
                        name="phone_number"
                        required
                        autocomplete="tel"
                        placeholder="085175557550"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.phone_number" />
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
                        {{ processing ? 'Menyimpan...' : 'Simpan Pelanggan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog View Pelanggan -->
    <Dialog v-model:open="isViewModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Detail Pelanggan</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Informasi profil pelanggan terdaftar.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedCustomer" class="space-y-3 pt-2 text-xs">
                <div
                    class="space-y-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Lengkap
                        </p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedCustomer.name }}
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 border-t border-gray-200/60 pt-2"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                No. Telepon
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedCustomer.phone_number }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Bergabung Pada
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{
                                    dayjs(selectedCustomer.created_at).format(
                                        'DD MMMM YYYY',
                                    )
                                }}
                            </p>
                        </div>
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

    <!-- Dialog Edit Pelanggan -->
    <Dialog v-model:open="isEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Edit Pelanggan</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Ubah rincian informasi pelanggan di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedCustomer"
                v-bind="
                    update.form({
                        id: selectedCustomer.id,
                        name: selectedCustomer.name,
                        phone_number: selectedCustomer.phone_number,
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
                        >Nama Pelanggan</Label
                    >
                    <Input
                        id="edit_name"
                        type="text"
                        name="name"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedCustomer.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon / WhatsApp</Label
                    >
                    <Input
                        id="edit_phone_number"
                        type="text"
                        name="phone_number"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedCustomer.phone_number"
                    />
                    <InputError :message="errors.phone_number" />
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
                        {{ processing ? 'Perbarui...' : 'Perbarui Pelanggan' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog Konfirmasi Hapus Pelanggan -->
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
                            >Hapus Pelanggan?</DialogTitle
                        >
                        <DialogDescription class="text-xs text-gray-500">
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedCustomer" class="space-y-3 py-2">
                <div
                    class="space-y-2 rounded-xl border border-red-100 bg-red-50/50 p-3.5 text-xs"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Pelanggan
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedCustomer.name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            No. Telepon
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedCustomer.phone_number }}
                        </p>
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
                    {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Pelanggan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
