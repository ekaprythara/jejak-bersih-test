<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Multiselect from 'vue-multiselect';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Label from '@/components/ui/label/Label.vue';
import cashier from '@/routes/cashier';
import type { CustomerType } from '@/types/data-types';
import { Trash2 } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kasir / POS',
                href: cashier.index(),
            },
        ],
    },
});

type ServiceType = {
    id: number;
    name: string;
    price: number;
    description: string | null;
    estimated_days: number;
};

const data = defineProps<{
    serviceData: ServiceType[];
    customers: CustomerType[];
    outlet_data: {
        name: string;
        address: string;
        phone_number: string;
    };
    user_data: {
        name: string;
    };
}>();

console.log(data.outlet_data);

// State untuk Customer (Multiselect butuh object utuh)
const customerInput = ref<CustomerType | null>(null);

// Fungsi untuk menghasilkan 10 digit angka bulat acak tanpa koma
const generateTempId = () => {
    return Math.floor(1000000000 + Math.random() * 9000000000);
};

// Inisialisasi Form Inertia dengan shoe_condition
const form = useForm({
    customer_id: null as number | null,
    payment_status: 'unpaid',
    payment_method: 'cash',
    notes: '',
    shoes: [
        {
            temp_id: generateTempId(),
            shoe_brand: '',
            shoe_color: '',
            shoe_size: '',
            shoe_condition: '', // Menggunakan shoe_condition
            services: [] as ServiceType[],
        },
    ],
});

// Fungsi untuk menghitung subtotal per sepatu
const getShoeSubtotal = (services: ServiceType[]) => {
    return services.reduce((sum, service) => sum + Number(service.price), 0);
};

// Fungsi Tambah Sepatu
const addShoe = () => {
    form.shoes.push({
        temp_id: generateTempId(),
        shoe_brand: '',
        shoe_color: '',
        shoe_size: '',
        shoe_condition: '', // Menggunakan shoe_condition
        services: [],
    });
};

// Fungsi Hapus Sepatu
const removeShoe = (index: number) => {
    if (form.shoes.length > 1) {
        form.shoes.splice(index, 1);
    }
};

// Format ke Rupiah Indonesia
const formatToIDR = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const customerInputLabel = (option: CustomerType) => {
    if (!option) {
        return '';
    }

    return `${option.name} (${option.phone_number})`;
};

const serviceInputLabel = (option: ServiceType) => {
    if (!option) {
        return '';
    }

    return `${option.name} - ${formatToIDR(option.price)}`;
};

// Reaktivitas Total Harga
const totalPrice = computed(() => {
    return form.shoes.reduce((total, shoe) => {
        const shoeTotal = shoe.services.reduce((sum, service) => {
            return sum + Number(service.price);
        }, 0);

        return total + shoeTotal;
    }, 0);
});

// Fungsi Submit
const submitTransaction = () => {
    form.customer_id = customerInput.value ? customerInput.value.id : null;

    form.post(cashier.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            // 👇 Reset form Inertia ke state awal (membersihkan input sepatu, catatan, dll)
            form.reset();

            toast.success('Data Transaksi Berhasil Dibuat!');

            // 👇 Reset juga pilihan multiselect pelanggan agar kembali kosong
            customerInput.value = null;
        },
        onError: (errors) => {
            console.log('Error Validasi:', errors);
        },
    });
};

const date = new Date().toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
</script>

<template>
    <Head title="Kasir" />

    <div class="min-h-screen">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_450px]">
            <!-- Kiri -->
            <div class="space-y-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-8 dark:border-sidebar-border"
                >
                    <h2 class="mb-5 text-2xl font-bold">Kasir / POS</h2>

                    <form
                        @submit.prevent="submitTransaction"
                        class="flex flex-col gap-6"
                    >
                        <!-- 1. INPUT PELANGGAN -->
                        <div class="grid gap-2">
                            <Label> Pelanggan </Label>
                            <Multiselect
                                :options="data.customers"
                                v-model="customerInput"
                                placeholder="Pilih Pelanggan..."
                                label="name"
                                track-by="id"
                                :allow-empty="false"
                                :required="true"
                                :custom-label="customerInputLabel"
                                select-label="Tekan 'enter' untuk memilih"
                                selected-label="Terpilih"
                                deselect-label="Tekan 'enter' untuk membatalkan"
                            />
                            <InputError :message="form.errors.customer_id" />
                        </div>

                        <hr class="border-gray-200" />

                        <!-- 2. LOOPING INPUT SEPATU -->
                        <div
                            v-for="(shoe, index) in form.shoes"
                            :key="shoe.temp_id"
                            class="relative space-y-4 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/50 p-5"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-700">
                                    Data Sepatu #{{ index + 1 }}
                                </h3>
                                <button
                                    v-if="form.shoes.length > 1"
                                    type="button"
                                    @click="removeShoe(index)"
                                    class="text-red-500 hover:cursor-pointer hover:text-red-700"
                                >
                                    <Trash2 :size="20" :stroke-width="1.5" />
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-5">
                                <div class="grid gap-2">
                                    <Label> Brand Sepatu </Label>
                                    <Input
                                        v-model="shoe.shoe_brand"
                                        type="text"
                                        required
                                        placeholder="Nike Air Jordan"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `shoes.${index}.shoe_brand`
                                            ]
                                        "
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label> Warna Sepatu </Label>
                                    <Input
                                        v-model="shoe.shoe_color"
                                        type="text"
                                        required
                                        placeholder="Hitam"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `shoes.${index}.shoe_color`
                                            ]
                                        "
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-5">
                                <div class="grid gap-2">
                                    <Label> Ukuran Sepatu (EU) </Label>
                                    <Input
                                        v-model="shoe.shoe_size"
                                        type="number"
                                        required
                                        placeholder="42"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `shoes.${index}.shoe_size`
                                            ]
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label> Kondisi Sepatu </Label>
                                    <Input
                                        v-model="shoe.shoe_condition"
                                        type="text"
                                        required
                                        placeholder="Bekas ringan"
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `shoes.${index}.shoe_condition`
                                            ]
                                        "
                                    />
                                </div>
                            </div>
                            <!-- LAYANAN -->
                            <div class="mt-4 grid gap-2">
                                <Label> Pilih Layanan: </Label>

                                <Multiselect
                                    v-model="shoe.services"
                                    :options="data.serviceData"
                                    label="name"
                                    track-by="id"
                                    :multiple="true"
                                    placeholder="Pilih Layanan..."
                                    :close-on-select="false"
                                    :clear-on-select="false"
                                    :preserve-search="true"
                                    :custom-label="serviceInputLabel"
                                    select-label="Tekan 'enter' untuk memilih"
                                    selected-label="Terpilih"
                                    deselect-label="Tekan 'enter' untuk membatalkan"
                                >
                                    <!-- Menampilkan deskripsi di dalam list dropdown pilihan -->
                                    <template #option="{ option }">
                                        <div class="text-sm">
                                            {{ option.name }} -
                                            {{ formatToIDR(option.price) }}
                                            <div class="text-xs">
                                                {{
                                                    option.description &&
                                                    option.description
                                                }}
                                            </div>
                                        </div>
                                    </template>

                                    <template #selection="{ values, isOpen }">
                                        <span
                                            class="multiselect__single"
                                            v-if="values.length"
                                            v-show="!isOpen"
                                        >
                                            {{ values.length }} layanan dipilih
                                        </span>
                                    </template>
                                </Multiselect>

                                <!-- Menampilkan deskripsi ringkas untuk layanan yang SEDANG DIPILIH -->
                                <div
                                    v-if="shoe.services.length > 0"
                                    class="mt-1 rounded bg-gray-100 p-2 text-xs"
                                >
                                    <div
                                        class="mb-1 font-semibold text-gray-600"
                                    >
                                        Layanan yang dipilih:
                                    </div>
                                    <div
                                        v-for="service in shoe.services"
                                        :key="service.id"
                                        class="text-gray-500"
                                    >
                                        <span class="font-medium text-gray-700"
                                            >• {{ service.name }}:</span
                                        >
                                        {{
                                            service.description ||
                                            'Tidak ada deskripsi.'
                                        }}
                                    </div>
                                </div>

                                <InputError
                                    :message="
                                        form.errors[`shoes.${index}.services`]
                                    "
                                />
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="addShoe"
                            class="rounded-xl bg-gray-200 px-3 py-4 font-bold text-gray-800 transition-colors hover:bg-gray-300"
                        >
                            + Tambah Sepatu Lain
                        </button>

                        <hr class="my-2 border-gray-200" />

                        <!-- 3. PEMBAYARAN & CATATAN -->
                        <div
                            class="space-y-4 rounded-xl border border-gray-200 bg-white p-5"
                        >
                            <h3 class="mb-2 text-lg font-bold text-gray-700">
                                Pembayaran & Catatan
                            </h3>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="payment_method">
                                        Metode Pembayaran
                                    </Label>
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                    >
                                        <option value="cash">
                                            Tunai (Cash)
                                        </option>
                                        <option value="transfer">
                                            Transfer (Bank / QRIS)
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.payment_method"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="payment_status">
                                        Status Pembayaran
                                    </Label>
                                    <select
                                        id="payment_status"
                                        v-model="form.payment_status"
                                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                    >
                                        <option value="unpaid">
                                            Belum Lunas (Unpaid)
                                        </option>
                                        <option value="paid">
                                            Lunas (Paid)
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.payment_status"
                                    />
                                </div>
                            </div>

                            <div class="mt-2 grid gap-2">
                                <Label for="notes">
                                    Catatan Transaksi (Opsional)
                                </Label>
                                <Input
                                    id="notes"
                                    type="text"
                                    v-model="form.notes"
                                    placeholder="Misal: Diambil oleh ojek online..."
                                />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-500 px-3 py-4 font-bold text-white transition-colors hover:bg-blue-600 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan Transaksi...'
                                    : 'Simpan Transaksi'
                            }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Harga -->
            <!-- KOLOM KANAN: STRUK NOTA (STICKY) -->
            <div class="space-y-4">
                <div
                    class="sticky top-5 rounded-lg border border-gray-300 bg-white p-6 font-mono text-gray-800 shadow-md"
                >
                    <!-- Header Nota -->
                    <div
                        class="mb-4 border-b-2 border-dashed border-gray-300 pb-4 text-center"
                    >
                        <h3 class="text-lg font-bold tracking-wider uppercase">
                            JEJAK BERSIH
                        </h3>
                        <p class="text-xs text-gray-800">
                            {{ `${outlet_data.address}` }}
                        </p>
                        <p class="text-xs text-gray-800">
                            {{ `WA: ${outlet_data.phone_number}` }}
                        </p>
                    </div>

                    <!-- Informasi Kasir & Tanggal -->
                    <div
                        class="mb-4 space-y-1 border-b-2 border-dashed border-gray-300 pb-3 text-xs"
                    >
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal:</span>
                            <span class="max-w-37.5 truncate font-bold">{{
                                date
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kasir:</span>
                            <span class="max-w-37.5 truncate font-bold">
                                {{ `${user_data.name}` }}
                            </span>
                        </div>
                    </div>

                    <!-- Informasi Pelanggan -->
                    <div
                        class="mb-4 space-y-1 border-b-2 border-dashed border-gray-300 pb-3 text-xs"
                    >
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pelanggan:</span>
                            <span class="max-w-37.5 truncate font-bold">
                                {{ customerInput ? customerInput.name : '-' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. Telepon:</span>
                            <span class="max-w-37.5 truncate font-bold">
                                {{
                                    customerInput
                                        ? customerInput.phone_number
                                        : '-'
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Daftar Item Sepatu, Ukuran, Kondisi & Layanan ala Nota -->
                    <div
                        class="max-h-87.5 space-y-3 overflow-y-auto pr-1 text-xs"
                    >
                        <div
                            v-for="(shoe, index) in form.shoes"
                            :key="shoe.temp_id"
                            class="border-b-2 border-dashed border-gray-300 pb-3 last:border-0 last:pb-0"
                        >
                            <!-- Nama Brand & Warna -->
                            <div class="mb-1 font-bold text-gray-900">
                                #{{ index + 1 }}
                                {{ shoe.shoe_brand || 'Sepatu Tanpa Brand' }}
                                <span
                                    v-if="shoe.shoe_color"
                                    class="font-normal text-gray-500"
                                >
                                    ({{
                                        `${shoe.shoe_color} / ${shoe.shoe_size}`
                                    }})
                                </span>
                            </div>

                            <!-- Detail Tambahan: Ukuran & Kondisi -->
                            <div
                                class="mb-1 space-y-0.5 pl-2 text-[11px] text-gray-500"
                            >
                                <div v-if="shoe.shoe_condition">
                                    <span>Kondisi: </span>
                                    <span class="font-semibold text-gray-700">{{
                                        shoe.shoe_condition
                                    }}</span>
                                </div>
                            </div>

                            <!-- Daftar Layanan -->
                            <div
                                v-if="shoe.services.length > 0"
                                class="mt-2 space-y-1 pl-2"
                            >
                                <div
                                    v-for="service in shoe.services"
                                    :key="service.id"
                                    class="flex justify-between text-gray-600"
                                >
                                    <span class="pr-2"
                                        >↳ {{ service.name }}</span
                                    >
                                    <span class="whitespace-nowrap">{{
                                        formatToIDR(service.price)
                                    }}</span>
                                </div>

                                <div
                                    class="flex justify-between pt-1 font-semibold text-gray-700"
                                >
                                    <span>Subtotal #{{ index + 1 }}</span>
                                    <span>{{
                                        formatToIDR(
                                            getShoeSubtotal(shoe.services),
                                        )
                                    }}</span>
                                </div>
                            </div>

                            <div v-else class="mt-1 pl-2 text-gray-400 italic">
                                ↳ Belum ada layanan...
                            </div>
                        </div>
                    </div>

                    <!-- Garis Pemisah Total -->
                    <div
                        class="my-4 border-t-2 border-dashed border-gray-300"
                    ></div>

                    <!-- Total Keseluruhan, Pembayaran & Catatan -->
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-bold text-gray-600 uppercase"
                                >Total</span
                            >
                            <span class="text-xl font-extrabold text-gray-900">
                                {{ formatToIDR(totalPrice) }}
                            </span>
                        </div>

                        <div
                            class="my-3 border-t-2 border-dashed border-gray-300"
                        ></div>

                        <div class="flex justify-between pt-1">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-bold uppercase">
                                Menunggu Pengerjaan
                            </span>
                        </div>

                        <div class="flex justify-between pt-1">
                            <span class="text-gray-500">Metode:</span>
                            <span class="font-bold uppercase">
                                {{
                                    form.payment_method === 'cash'
                                        ? 'Tunai (Cash)'
                                        : 'Transfer (Bank / QRIS)'
                                }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">Pembayaran:</span>
                            <span
                                :class="
                                    form.payment_status === 'paid'
                                        ? 'font-bold text-green-600'
                                        : 'font-bold text-orange-600'
                                "
                            >
                                {{
                                    form.payment_status === 'paid'
                                        ? 'LUNAS'
                                        : 'BELUM LUNAS'
                                }}
                            </span>
                        </div>

                        <div
                            v-if="form.notes"
                            class="mt-2 flex flex-col gap-1 text-xs"
                        >
                            <span class="block text-gray-500">Catatan:</span>
                            <span class="block text-gray-700 italic">{{
                                form.notes
                            }}</span>
                        </div>
                    </div>

                    <!-- Footer Nota Kecil -->
                    <div
                        class="mt-4 border-t-2 border-dashed border-gray-300 pt-3 text-center text-[10px] text-gray-400"
                    >
                        <p>Terima kasih atas kepercayaan Anda!</p>
                        <p>Simpan struk ini sebagai bukti transaksi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
