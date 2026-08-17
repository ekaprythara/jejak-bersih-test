<script setup lang="ts">
import { Head, Form, router } from '@inertiajs/vue3';
import { Eye, Edit, Trash2 } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h, ref, toRefs } from 'vue';
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
import { index, store, update, destroy } from '@/routes/outlets';
import type { OutletType, PaginatedResponse } from '@/types/data-types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Outlet',
                href: index(),
            },
        ],
    },
});

// Props & Reactivity
const props = defineProps<{
    outlets: PaginatedResponse<OutletType>;
}>();

const { outlets } = toRefs(props);

// State Ref & Modals
const previewImage = ref<string | null>(null);
const editPreviewImage = ref<string | null>(null);
const selectedOutlet = ref<OutletType | null>(null);

const isModalOpen = ref(false);
const isViewModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);

// Handlers Action Modal
function handleView(id: number) {
    const outlet = props.outlets.data.find((item) => item.id === id);

    if (outlet) {
        selectedOutlet.value = outlet;
        isViewModalOpen.value = true;
    }
}

function handleEdit(id: number) {
    const outlet = props.outlets.data.find((item) => item.id === id);

    if (outlet) {
        selectedOutlet.value = outlet;
        editPreviewImage.value = outlet.image_url || null;
        isEditModalOpen.value = true;
    }
}

function handleDelete(id: number) {
    const outlet = props.outlets.data.find((item) => item.id === id);

    if (outlet) {
        selectedOutlet.value = outlet;
        isDeleteModalOpen.value = true;
    }
}

function confirmDelete() {
    if (!selectedOutlet.value) return;

    isDeleting.value = true;

    router.delete(destroy({ id: selectedOutlet.value.id }), {
        onSuccess: () => {
            toast.success('Data Cabang berhasil dihapus!');
            isDeleteModalOpen.value = false;
            selectedOutlet.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus data cabang.');
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
    toast.success('Data Cabang Berhasil Disimpan!');
    isModalOpen.value = false;
    previewImage.value = null;
}

function handleEditSuccess() {
    toast.success('Data Cabang Berhasil Diperbarui!');
    isEditModalOpen.value = false;
    editPreviewImage.value = null;
    selectedOutlet.value = null;
}

// Columns Definition
const columns: ColumnDef<OutletType>[] = [
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
        accessorKey: 'address',
        header: 'Alamat',
        cell: (info) => `${info.getValue()}`,
    },
    {
        accessorKey: 'phone_number',
        header: 'No. Telepon',
        cell: (info) => `${info.getValue()}`,
    },
    {
        id: 'actions',
        header: 'Aksi',
        enableSorting: false,
        cell: ({ row }) => {
            const outlet = row.original;

            return h('div', { class: 'flex items-center gap-1.5' }, [
                // Tombol Lihat (Detail)
                h(
                    Button,
                    {
                        variant: 'ghost',
                        size: 'icon',
                        class: 'h-8 w-8 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors',
                        onClick: () => handleView(outlet.id),
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
                        onClick: () => handleEdit(outlet.id),
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
                        onClick: () => handleDelete(outlet.id),
                    },
                    () => h(Trash2, { size: 16 }),
                ),
            ]);
        },
    },
];
</script>

<template>
    <Head title="Cabang" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto bg-[#F8FAFC] p-4"
    >
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xs">
            <div class="flex flex-col gap-6">
                <!-- Header Title & Button -->
                <div class="flex flex-row items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            Daftar Cabang
                        </h2>
                        <p class="text-xs text-gray-500">
                            Kelola dan pantau lokasi outlet operasional Anda.
                        </p>
                    </div>
                    <Button
                        @click="isModalOpen = true"
                        class="bg-blue-600 font-semibold hover:bg-blue-700"
                    >
                        + Tambah Cabang
                    </Button>
                </div>

                <!-- DataTable -->
                <DataTable
                    :columns="columns"
                    :data="outlets.data"
                    searchable
                    searchPlaceholder="Cari Cabang..."
                />
            </div>
        </div>
    </div>

    <!-- Dialog / Modal Tambah Outlet -->
    <Dialog v-model:open="isModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Tambah Outlet Baru</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Masukkan informasi outlet baru di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-bind="store.form()"
                enctype="multipart/form-data"
                :reset-on-success="['name', 'address', 'phone_number', 'image']"
                v-slot="{ errors, processing }"
                @success="handleSuccess"
                class="space-y-4 pt-2"
            >
                <!-- Input Nama Outlet -->
                <div class="space-y-1.5">
                    <Label
                        for="name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Outlet</Label
                    >
                    <Input
                        id="name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Contoh: Cabang Denpasar"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- Input Alamat Outlet -->
                <div class="space-y-1.5">
                    <Label
                        for="address"
                        class="text-xs font-semibold text-gray-700"
                        >Alamat</Label
                    >
                    <Input
                        id="address"
                        type="text"
                        name="address"
                        required
                        placeholder="Jl. Teuku Umar No. 12"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.address" />
                </div>

                <!-- Input No Telepon -->
                <div class="space-y-1.5">
                    <Label
                        for="phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon</Label
                    >
                    <Input
                        id="phone_number"
                        type="text"
                        name="phone_number"
                        required
                        placeholder="081234567890"
                        class="h-10 rounded-lg"
                    />
                    <InputError :message="errors.phone_number" />
                </div>

                <!-- Image Logo Outlet -->
                <div class="space-y-1.5">
                    <Label
                        for="image"
                        class="text-xs font-semibold text-gray-700"
                        >Logo / Foto Outlet (Opsional)</Label
                    >
                    <Input
                        id="image"
                        type="file"
                        name="image"
                        accept="image/*"
                        class="h-10 cursor-pointer rounded-lg pt-1.5"
                        @change="(e) => handleImagePreview(e, false)"
                    />
                    <InputError :message="errors.image" />
                    <img
                        v-if="previewImage"
                        :src="previewImage"
                        class="mt-2 max-h-32 rounded-lg border object-cover"
                    />
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
                        {{ processing ? 'Menyimpan...' : 'Simpan Outlet' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog View Outlet -->
    <Dialog v-model:open="isViewModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Detail Outlet</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Informasi lengkap mengenai cabang outlet.
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedOutlet" class="space-y-4 pt-2">
                <div
                    class="space-y-2.5 rounded-xl border border-gray-100 bg-gray-50/70 p-3.5 text-xs"
                >
                    <div>
                        <p class="font-medium text-gray-400">Nama Outlet</p>
                        <p class="mt-0.5 text-sm font-bold text-gray-800">
                            {{ selectedOutlet.name }}
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 border-t border-gray-200/60 pt-2"
                    >
                        <div>
                            <p class="font-medium text-gray-400">No. Telepon</p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOutlet.phone_number }}
                            </p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">Alamat</p>
                            <p class="mt-0.5 font-semibold text-gray-800">
                                {{ selectedOutlet.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="selectedOutlet.image_url" class="space-y-1.5">
                    <p class="text-xs font-medium text-gray-500">
                        Logo / Foto Outlet
                    </p>
                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-2"
                    >
                        <img
                            :src="selectedOutlet.image_url"
                            alt="Logo Outlet"
                            class="max-h-60 w-full rounded-lg object-contain"
                        />
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

    <!-- Dialog Edit Outlet -->
    <Dialog v-model:open="isEditModalOpen">
        <DialogContent class="rounded-2xl sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-bold text-gray-800"
                    >Edit Outlet</DialogTitle
                >
                <DialogDescription class="text-xs text-gray-500">
                    Ubah rincian informasi cabang outlet di bawah ini.
                </DialogDescription>
            </DialogHeader>

            <Form
                v-if="selectedOutlet"
                v-bind="
                    update.form({
                        id: selectedOutlet.id,
                        name: selectedOutlet.name,
                        address: selectedOutlet.address,
                        phone_number: selectedOutlet.phone_number,
                    })
                "
                enctype="multipart/form-data"
                v-slot="{ errors, processing }"
                @success="handleEditSuccess"
                class="space-y-4 pt-2"
            >
                <input type="hidden" name="_method" value="PUT" />

                <div class="space-y-1.5">
                    <Label
                        for="edit_name"
                        class="text-xs font-semibold text-gray-700"
                        >Nama Outlet</Label
                    >
                    <Input
                        id="edit_name"
                        type="text"
                        name="name"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedOutlet.name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_address"
                        class="text-xs font-semibold text-gray-700"
                        >Alamat</Label
                    >
                    <Input
                        id="edit_address"
                        type="text"
                        name="address"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedOutlet.address"
                    />
                    <InputError :message="errors.address" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_phone_number"
                        class="text-xs font-semibold text-gray-700"
                        >No. Telepon</Label
                    >
                    <Input
                        id="edit_phone_number"
                        type="text"
                        name="phone_number"
                        required
                        class="h-10 rounded-lg"
                        :default-value="selectedOutlet.phone_number"
                    />
                    <InputError :message="errors.phone_number" />
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="edit_image"
                        class="text-xs font-semibold text-gray-700"
                        >Ganti Logo / Foto (Opsional)</Label
                    >
                    <Input
                        id="edit_image"
                        type="file"
                        name="image"
                        accept="image/*"
                        class="h-10 cursor-pointer rounded-lg pt-1.5"
                        @change="(e) => handleImagePreview(e, true)"
                    />
                    <InputError :message="errors.image" />
                    <img
                        v-if="editPreviewImage"
                        :src="editPreviewImage"
                        class="mt-2 max-h-32 rounded-lg border object-cover"
                    />
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
                        {{ processing ? 'Perbarui...' : 'Perbarui Outlet' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Dialog Konfirmasi Hapus Outlet -->
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
                            >Hapus Outlet?</DialogTitle
                        >
                        <DialogDescription class="text-xs text-gray-500">
                            Tindakan ini tidak dapat dibatalkan.
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedOutlet" class="space-y-3 py-2">
                <div
                    class="space-y-2 rounded-xl border border-red-100 bg-red-50/50 p-3.5 text-xs"
                >
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Nama Outlet
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedOutlet.name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-gray-400">
                            Alamat
                        </p>
                        <p class="mt-0.5 font-semibold text-gray-800">
                            {{ selectedOutlet.address }}
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
                    {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus Outlet' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
