<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, h, ref, toRefs } from 'vue';
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
import {
    store as storeAdmin,
    update as updateAdmin,
    destroy as destroyAdmin,
} from '@/routes/admin';
import {
    store as storeOwner,
    update as updateOwner,
    destroy as destroyOwner,
} from '@/routes/owner';
import { index } from '@/routes/users';

import type {
    AdminType,
    OutletType,
    PaginatedResponse,
    RoleType,
} from '@/types/data-types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengguna',
                href: index(),
            },
        ],
    },
});

// 1. Catch Props & Reactivity di bagian paling atas
const props = defineProps<{
    owners: PaginatedResponse<AdminType>;
    admins: PaginatedResponse<AdminType>;
    outlets: OutletType[];
    roles: RoleType[];
}>();

const { admins, outlets, owners } = toRefs(props);

// 2. Modals State Ref
const isAdminModalOpen = ref(false);
const isAdminShowModalOpen = ref(false);
const isAdminEditModalOpen = ref(false);
const isAdminDeleteModalOpen = ref(false);
const isDeletingAdmin = ref(false);

const isOwnerModalOpen = ref(false);
const isOwnerShowModalOpen = ref(false);
const isOwnerEditModalOpen = ref(false);
const isOwnerDeleteModalOpen = ref(false);
const isDeletingOwner = ref(false);

const selectedAdmin = ref<AdminType | null>(null);
const selectedOwner = ref<AdminType | null>(null);

// 3. Handlers Action Admin
function handleViewAdmin(id: number) {
    const admin = props.admins.data.find((item) => item.id === id);
    if (admin) {
        selectedAdmin.value = admin;
        isAdminShowModalOpen.value = true;
    }
}

function handleEditAdmin(id: number) {
    const admin = props.admins.data.find((item) => item.id === id);
    if (admin) {
        selectedAdmin.value = admin;
        isAdminEditModalOpen.value = true;
    }
}

function handleEditAdminSuccess() {
    toast.success('Data Admin Berhasil Diperbarui!');
    isAdminEditModalOpen.value = false;
    selectedAdmin.value = null;
}

function handleDeleteAdmin(id: number) {
    const admin = props.admins.data.find((item) => item.id === id);
    if (admin) {
        selectedAdmin.value = admin;
        isAdminDeleteModalOpen.value = true;
    }
}

function confirmDeleteAdmin() {
    if (!selectedAdmin.value) return;

    isDeletingAdmin.value = true;

    router.delete(destroyAdmin({ id: selectedAdmin.value.id }), {
        onSuccess: () => {
            toast.success('Admin berhasil dihapus!');
            isAdminDeleteModalOpen.value = false;
            selectedAdmin.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus data admin.');
        },
        onFinish: () => {
            isDeletingAdmin.value = false;
        },
    });
}

// 4. Handlers Action Owner
function handleViewOwner(id: number) {
    const owner = props.owners.data.find((item) => item.id === id);
    if (owner) {
        selectedOwner.value = owner;
        isOwnerShowModalOpen.value = true;
    }
}

function handleEditOwner(id: number) {
    const owner = props.owners.data.find((item) => item.id === id);
    if (owner) {
        selectedOwner.value = owner;
        isOwnerEditModalOpen.value = true;
    }
}

function handleEditOwnerSuccess() {
    toast.success('Data Owner Berhasil Diperbarui!');
    isOwnerEditModalOpen.value = false;
    selectedOwner.value = null;
}

function handleDeleteOwner(id: number) {
    const owner = props.owners.data.find((item) => item.id === id);
    if (owner) {
        selectedOwner.value = owner;
        isOwnerDeleteModalOpen.value = true;
    }
}

function confirmDeleteOwner() {
    if (!selectedOwner.value) return;

    isDeletingOwner.value = true;

    router.delete(destroyOwner({ id: selectedOwner.value.id }), {
        onSuccess: () => {
            toast.success('Owner berhasil dihapus!');
            isOwnerDeleteModalOpen.value = false;
            selectedOwner.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus data owner.');
        },
        onFinish: () => {
            isDeletingOwner.value = false;
        },
    });
}

// 5. Shared Success Callback
const handleSuccess = () => {
    toast.success('Data Berhasil Disimpan!');
    if (isOwnerModalOpen.value) isOwnerModalOpen.value = false;
    if (isAdminModalOpen.value) isAdminModalOpen.value = false;
};

// 6. Computed Columns Definition (Mencegah Kehilangan Listener Events pada Table Cell)
const columns = computed<ColumnDef<AdminType>[]>(() => [
    {
        accessorKey: 'name',
        header: 'Nama',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorKey: 'username',
        header: 'Nama Pengguna',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorKey: 'phone_number',
        header: 'No. Telepon',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorFn: (row) => row.outlet?.name ?? '-',
        id: 'outlet_name',
        header: 'Cabang',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorFn: (row) => row.is_active,
        id: 'is_active',
        header: 'Status',
        cell: ({ row }) => {
            const is_active = row.original.is_active;

            return h(
                'span',
                {
                    class: is_active
                        ? 'inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 whitespace-nowrap'
                        : 'inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 whitespace-nowrap',
                },
                [
                    h('span', {
                        class: is_active
                            ? 'h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse'
                            : 'h-1.5 w-1.5 rounded-full bg-red-500',
                    }),
                    is_active ? 'Aktif' : 'Non-aktif',
                ],
            );
        },
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const admin = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer',
                        onClick: () => handleViewAdmin(admin.id),
                    },
                    () => h(Eye, { size: 16 }),
                ),
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer',
                        onClick: () => handleEditAdmin(admin.id),
                    },
                    () => h(Edit, { size: 16 }),
                ),
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer',
                        onClick: () => handleDeleteAdmin(admin.id),
                    },
                    () => h(Trash2, { size: 16 }),
                ),
            ]);
        },
    },
]);

const ownerColumns = computed<ColumnDef<AdminType>[]>(() => [
    {
        accessorKey: 'name',
        header: 'Nama',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorKey: 'username',
        header: 'Nama Pengguna',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorKey: 'phone_number',
        header: 'No. Telepon',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorFn: (row) => row.is_active,
        id: 'is_active',
        header: 'Status',
        cell: ({ row }) => {
            const is_active = row.original.is_active;

            return h(
                'span',
                {
                    class: is_active
                        ? 'inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 whitespace-nowrap'
                        : 'inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 whitespace-nowrap',
                },
                [
                    h('span', {
                        class: is_active
                            ? 'h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse'
                            : 'h-1.5 w-1.5 rounded-full bg-red-500',
                    }),
                    is_active ? 'Aktif' : 'Non-aktif',
                ],
            );
        },
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const owner = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors cursor-pointer',
                        onClick: () => handleViewOwner(owner.id),
                    },
                    () => h(Eye, { size: 16 }),
                ),
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition-colors cursor-pointer',
                        onClick: () => handleEditOwner(owner.id),
                    },
                    () => h(Edit, { size: 16 }),
                ),
                h(
                    Button,
                    {
                        type: 'button',
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer',
                        onClick: () => handleDeleteOwner(owner.id),
                    },
                    () => h(Trash2, { size: 16 }),
                ),
            ]);
        },
    },
]);
</script>

<template>
    <Head title="Pengguna" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto bg-[#F8FAFC] p-4"
    >
        <!-- Owner Section -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xs">
            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">
                        Daftar Owner
                    </h2>
                    <Button
                        @click="isOwnerModalOpen = true"
                        class="cursor-pointer bg-blue-600 font-semibold hover:bg-blue-700"
                        >+ Tambah Owner</Button
                    >
                </div>

                <DataTable
                    :columns="ownerColumns"
                    :data="owners.data"
                    searchable
                />
            </div>
        </div>

        <!-- Admin Section -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xs">
            <div class="flex flex-col gap-5">
                <div class="flex flex-row items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">
                        Daftar Admin
                    </h2>
                    <Button
                        @click="isAdminModalOpen = true"
                        class="cursor-pointer bg-blue-600 font-semibold hover:bg-blue-700"
                        >+ Tambah Admin</Button
                    >
                </div>

                <DataTable
                    :columns="columns"
                    :data="admins.data"
                    searchable
                    searchPlaceholder="Cari Admin..."
                />
            </div>
        </div>
    </div>

    <!-- Modal Tambah Owner -->
    <Dialog v-model:open="isOwnerModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Owner Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Masukkan informasi Owner di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="storeOwner.form()"
                :reset-on-success="[
                    'name',
                    'username',
                    'email',
                    'phone_number',
                    'password',
                    'password_confirmation',
                ]"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <div class="space-y-1.5">
                    <Label
                        for="owner_name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Lengkap</Label
                    >
                    <Input
                        id="owner_name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        placeholder="John Doe"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="owner_username"
                        class="text-xs font-semibold text-gray-700"
                        >Username</Label
                    >
                    <Input
                        id="owner_username"
                        type="text"
                        name="username"
                        required
                        placeholder="johndoe"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.username" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="owner_email"
                        class="text-xs font-semibold text-gray-700"
                        >Email</Label
                    >
                    <Input
                        id="owner_email"
                        type="email"
                        name="email"
                        required
                        placeholder="john@example.com"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="owner_phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon</Label
                    >
                    <Input
                        id="owner_phone_number"
                        type="text"
                        name="phone_number"
                        required
                        placeholder="081234567890"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.phone_number" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="owner_password"
                        class="text-xs font-semibold text-gray-700"
                        >Password</Label
                    >
                    <Input
                        id="owner_password"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="owner_password_confirmation"
                        class="text-xs font-semibold text-gray-700"
                        >Konfirmasi Password</Label
                    >
                    <Input
                        id="owner_password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        placeholder="••••••••"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isOwnerModalOpen = false"
                        class="cursor-pointer rounded-lg"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="cursor-pointer rounded-lg bg-blue-600 font-semibold hover:bg-blue-700"
                        >Simpan Owner</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Modal Detail Owner -->
    <Dialog v-model:open="isOwnerShowModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Detail Owner</DialogTitle
                >
                <DialogDescription
                    class="text-xs leading-relaxed text-gray-500"
                >
                    Informasi lengkap akun pemilik sistem.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedOwner" class="space-y-3 pt-2 text-xs">
                <div
                    class="space-y-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Lengkap
                        </p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedOwner.name }}
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2.5 border-t border-gray-200/60 pt-2.5"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Username
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOwner.username }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                No. Telepon
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOwner.phone_number || '-' }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-medium text-gray-400">
                                Email
                            </p>
                            <p
                                class="mt-0.5 truncate font-semibold text-gray-800"
                            >
                                {{ selectedOwner.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3"
                >
                    <span class="text-xs font-medium text-gray-500"
                        >Status Akun</span
                    >
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap',
                            selectedOwner.is_active
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700',
                        ]"
                    >
                        <span
                            :class="[
                                'h-1.5 w-1.5 rounded-full',
                                selectedOwner.is_active
                                    ? 'animate-pulse bg-green-500'
                                    : 'bg-red-500',
                            ]"
                        />
                        {{ selectedOwner.is_active ? 'Aktif' : 'Non-aktif' }}
                    </span>
                </div>
            </div>

            <DialogFooter class="pt-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isOwnerShowModalOpen = false"
                    class="cursor-pointer rounded-lg"
                    >Tutup</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Modal Edit Owner -->
    <Dialog v-model:open="isOwnerEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Edit Status Owner</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Atur status keaktifan akun pemilik sistem.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedOwner"
                v-bind="
                    updateOwner.form({
                        id: selectedOwner.id,
                        is_active: selectedOwner.is_active ?? true,
                    })
                "
                v-slot="{ errors, processing }"
                @success="handleEditOwnerSuccess"
                class="space-y-4 pt-2"
            >
                <input type="hidden" name="_method" value="PATCH" />

                <div
                    class="space-y-1 rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-xs"
                >
                    <p class="text-[11px] font-medium text-gray-400">Owner</p>
                    <p class="font-bold text-gray-800">
                        {{ selectedOwner.name }}
                    </p>
                    <p class="text-[11px] text-gray-500">
                        @{{ selectedOwner.username }} •
                        {{ selectedOwner.email }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_owner_is_active"
                        class="text-xs font-semibold text-gray-700"
                        >Status Akun</Label
                    >
                    <select
                        name="is_active"
                        id="edit_owner_is_active"
                        required
                        :value="selectedOwner.is_active ? '1' : '0'"
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                    >
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                    <InputError :message="errors.is_active" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isOwnerEditModalOpen = false"
                        class="cursor-pointer rounded-lg"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="cursor-pointer rounded-lg bg-amber-600 font-semibold text-white hover:bg-amber-700"
                    >
                        {{ processing ? 'Perbarui...' : 'Perbarui Owner' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Modal Hapus Owner -->
    <Dialog v-model:open="isOwnerDeleteModalOpen">
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
                            >Hapus Akun Owner?</DialogTitle
                        >
                        <DialogDescription class="text-xs text-gray-500">
                            Tindakan ini tidak dapat dibatalkan. Akun tidak akan
                            dapat diakses kembali.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedOwner" class="space-y-3 py-2">
                <div
                    class="space-y-2 rounded-xl border border-red-100 bg-red-50/50 p-3.5 text-xs"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Pengguna
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedOwner.name }}
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 border-t border-red-100/60 pt-2"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Username
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOwner.username }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                No. Telepon
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOwner.phone_number || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2 pt-2 sm:gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isOwnerDeleteModalOpen = false"
                    class="w-full cursor-pointer rounded-xl text-xs font-medium sm:w-auto"
                    :disabled="isDeletingOwner"
                    >Batal</Button
                >
                <Button
                    type="button"
                    @click="confirmDeleteOwner"
                    :disabled="isDeletingOwner"
                    class="w-full cursor-pointer rounded-xl bg-red-600 text-xs font-semibold text-white hover:bg-red-700 sm:w-auto"
                >
                    {{ isDeletingOwner ? 'Menghapus...' : 'Ya, Hapus Owner' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Modal Tambah Admin -->
    <Dialog v-model:open="isAdminModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Admin Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Masukkan informasi admin dan tentukan penugasan outletnya di
                    bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="storeAdmin.form()"
                :reset-on-success="[
                    'name',
                    'username',
                    'email',
                    'phone_number',
                    'password',
                    'password_confirmation',
                    'outlet_id',
                ]"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <div class="space-y-1.5">
                    <Label
                        for="admin_name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Lengkap</Label
                    >
                    <Input
                        id="admin_name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        placeholder="John Doe"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="admin_username"
                        class="text-xs font-semibold text-gray-700"
                        >Username</Label
                    >
                    <Input
                        id="admin_username"
                        type="text"
                        name="username"
                        required
                        placeholder="johndoe"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.username" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="admin_email"
                        class="text-xs font-semibold text-gray-700"
                        >Email</Label
                    >
                    <Input
                        id="admin_email"
                        type="email"
                        name="email"
                        required
                        placeholder="john@example.com"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="admin_phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon</Label
                    >
                    <Input
                        id="admin_phone_number"
                        type="text"
                        name="phone_number"
                        required
                        placeholder="081234567890"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.phone_number" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="admin_password"
                        class="text-xs font-semibold text-gray-700"
                        >Password</Label
                    >
                    <Input
                        id="admin_password"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="admin_password_confirmation"
                        class="text-xs font-semibold text-gray-700"
                        >Konfirmasi Password</Label
                    >
                    <Input
                        id="admin_password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        placeholder="••••••••"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="outlet_id"
                        class="text-xs font-semibold text-gray-700"
                        >Cabang Outlet</Label
                    >
                    <select
                        name="outlet_id"
                        id="outlet_id"
                        required
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                    >
                        <option value="" disabled selected>
                            Pilih Outlet...
                        </option>
                        <option
                            v-for="outlet in outlets"
                            :key="outlet.id"
                            :value="outlet.id"
                        >
                            {{ outlet.name }}
                        </option>
                    </select>
                    <InputError :message="errors.outlet_id" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isAdminModalOpen = false"
                        class="cursor-pointer rounded-lg"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="cursor-pointer rounded-lg bg-blue-600 font-semibold hover:bg-blue-700"
                        >Simpan Admin</Button
                    >
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Modal Detail Admin -->
    <Dialog v-model:open="isAdminShowModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Detail Admin</DialogTitle
                >
                <DialogDescription
                    class="text-xs leading-relaxed text-gray-500"
                >
                    Informasi lengkap akun staf operasional kasir dan penugasan
                    cabangnya.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedAdmin" class="space-y-3 pt-2 text-xs">
                <div
                    class="space-y-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Lengkap
                        </p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedAdmin.name }}
                        </p>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-2.5 border-t border-gray-200/60 pt-2.5"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Username
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedAdmin.username }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                No. Telepon
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedAdmin.phone_number || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Email
                            </p>
                            <p
                                class="mt-0.5 truncate font-semibold text-gray-800"
                            >
                                {{ selectedAdmin.email }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Cabang Penugasan
                            </p>
                            <p class="mt-0.5 font-semibold text-blue-600">
                                {{ selectedAdmin.outlet?.name ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/50 p-3"
                >
                    <span class="text-xs font-medium text-gray-500"
                        >Status Akun</span
                    >
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap',
                            selectedAdmin.is_active
                                ? 'bg-green-50 text-green-700'
                                : 'bg-red-50 text-red-700',
                        ]"
                    >
                        <span
                            :class="[
                                'h-1.5 w-1.5 rounded-full',
                                selectedAdmin.is_active
                                    ? 'animate-pulse bg-green-500'
                                    : 'bg-red-500',
                            ]"
                        />
                        {{ selectedAdmin.is_active ? 'Aktif' : 'Non-aktif' }}
                    </span>
                </div>
            </div>

            <DialogFooter class="pt-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isAdminShowModalOpen = false"
                    class="cursor-pointer rounded-lg"
                    >Tutup</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Modal Edit Admin -->
    <Dialog v-model:open="isAdminEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader class="space-y-1">
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Edit Akses & Cabang Admin</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Atur lokasi cabang penugasan dan status keaktifan akun
                    admin.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedAdmin"
                v-bind="
                    updateAdmin.form({
                        id: selectedAdmin.id,
                        outlet_id: selectedAdmin.outlet_id || '',
                        is_active: selectedAdmin.is_active ?? true,
                    })
                "
                v-slot="{ errors, processing }"
                @success="handleEditAdminSuccess"
                class="space-y-4 pt-2"
            >
                <input type="hidden" name="_method" value="PATCH" />

                <div
                    class="space-y-1 rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-xs"
                >
                    <p class="text-[11px] font-medium text-gray-400">Admin</p>
                    <p class="font-bold text-gray-800">
                        {{ selectedAdmin.name }}
                    </p>
                    <p class="text-[11px] text-gray-500">
                        @{{ selectedAdmin.username }} •
                        {{ selectedAdmin.email }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_admin_outlet_id"
                        class="text-xs font-semibold text-gray-700"
                        >Cabang Outlet</Label
                    >
                    <select
                        name="outlet_id"
                        id="edit_admin_outlet_id"
                        required
                        :value="selectedAdmin.outlet_id"
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                    >
                        <option value="" disabled>Pilih Outlet...</option>
                        <option
                            v-for="outlet in outlets"
                            :key="outlet.id"
                            :value="outlet.id"
                        >
                            {{ outlet.name }}
                        </option>
                    </select>
                    <InputError :message="errors.outlet_id" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_admin_is_active"
                        class="text-xs font-semibold text-gray-700"
                        >Status Akun</Label
                    >
                    <select
                        name="is_active"
                        id="edit_admin_is_active"
                        required
                        :value="selectedAdmin.is_active ? '1' : '0'"
                        class="flex h-10 w-full items-center justify-between rounded-lg border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none"
                    >
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                    <InputError :message="errors.is_active" />
                </div>

                <DialogFooter class="pt-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="isAdminEditModalOpen = false"
                        class="cursor-pointer rounded-lg"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="processing"
                        class="cursor-pointer rounded-lg bg-amber-600 font-semibold text-white hover:bg-amber-700"
                    >
                        {{ processing ? 'Perbarui...' : 'Perbarui Admin' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Modal Hapus Admin -->
    <Dialog v-model:open="isAdminDeleteModalOpen">
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
                            >Hapus Akun Admin?</DialogTitle
                        >
                        <DialogDescription class="text-xs text-gray-500">
                            Tindakan ini tidak dapat dibatalkan. Akun tidak akan
                            dapat diakses kembali.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedAdmin" class="space-y-3 py-2">
                <div
                    class="space-y-2 rounded-xl border border-red-100 bg-red-50/50 p-3.5 text-xs"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Pengguna
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedAdmin.name }}
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 border-t border-red-100/60 pt-2"
                    >
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Username
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedAdmin.username }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[11px] font-medium text-gray-400">
                                Cabang Penugasan
                            </p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedAdmin.outlet?.name ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="gap-2 pt-2 sm:gap-2">
                <Button
                    type="button"
                    variant="outline"
                    @click="isAdminDeleteModalOpen = false"
                    class="w-full cursor-pointer rounded-xl text-xs font-medium sm:w-auto"
                    :disabled="isDeletingAdmin"
                    >Batal</Button
                >
                <Button
                    type="button"
                    @click="confirmDeleteAdmin"
                    :disabled="isDeletingAdmin"
                    class="w-full cursor-pointer rounded-xl bg-red-600 text-xs font-semibold text-white hover:bg-red-700 sm:w-auto"
                >
                    {{ isDeletingAdmin ? 'Menghapus...' : 'Ya, Hapus Admin' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
