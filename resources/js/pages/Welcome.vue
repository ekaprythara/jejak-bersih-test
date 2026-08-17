<script setup>
import { Link } from '@inertiajs/vue3';
import { Sparkles, SquareArrowOutUpRight } from '@lucide/vue';
import { ref } from 'vue';
import { index } from '@/routes/tracking';

// State untuk FAQ Accordion
const activeFaq = ref(0); // Default pertanyaan pertama terbuka

const faqs = ref([
    {
        question: 'Berapa lama prosesnya?',
        answer: 'Waktu pengerjaan bervariasi tergantung jenis layanan. Untuk Fast Clean memakan waktu 1-2 hari kerja. Deep Clean membutuhkan 3-4 hari kerja untuk memastikan pembersihan dan pengeringan optimal.',
    },
    {
        question: 'Apakah ada layanan antar-jemput?',
        answer: 'Ya, kami menyediakan layanan antar-jemput gratis untuk area cakupan tertentu dengan minimal pemesanan 2 pasang sepatu.',
    },
    {
        question: 'Bagaimana cara pembayarannya?',
        answer: 'Kami menerima berbagai metode pembayaran termasuk QRIS, transfer bank (BCA, Mandiri, BRI), serta e-wallet (Gopay, OVO, ShopeePay).',
    },
]);

const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

// State untuk input pelacakan resi
const searchResi = ref('');
const orderStatus = ref({
    id: 'HCL-8924',
    name: 'Nike Air Force 1',
    status: 'Dalam Proses',
    step: 2, // 1: Diterima, 2: Pencucian, 3: Pengeringan, 4: Selesai
});
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-800 antialiased">
        <!-- NAVBAR -->
        <header
            class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur-md"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-12"
            >
                <!-- Logo -->
                <a
                    href="#"
                    class="text-xl font-extrabold tracking-tight text-gray-900"
                >
                    HydroClean<span class="text-[#046C56]"> Logic</span>
                </a>

                <!-- Menu Navigation -->
                <nav
                    class="hidden items-center space-x-8 text-sm font-medium text-gray-600 md:flex"
                >
                    <a href="#layanan" class="transition hover:text-[#046C56]"
                        >Layanan</a
                    >
                    <a href="#proses" class="transition hover:text-[#046C56]"
                        >Proses</a
                    >
                    <a href="#faq" class="transition hover:text-[#046C56]"
                        >FAQ</a
                    >
                </nav>

                <!-- CTA Button -->
                <a
                    href="#lacak"
                    class="flex items-center gap-2 rounded-lg bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800"
                >
                    Lacak Pesanan
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"
                        />
                    </svg>
                </a>
            </div>
        </header>

        <!-- SECTION 1: HERO (Tinggi Pas Layanan Layar Kiri Kanan) -->
        <section
            class="relative flex min-h-[calc(100vh-65px)] items-center justify-center bg-white py-12 lg:py-0"
        >
            <div class="mx-auto max-w-7xl px-6 lg:px-12">
                <div
                    class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16"
                >
                    <!-- Text Content -->
                    <div class="space-y-6">
                        <span
                            class="text-xs font-bold tracking-widest text-[#046C56] uppercase"
                        >
                            Perawatan Sepatu Premium
                        </span>
                        <h1
                            class="text-4xl leading-tight font-black text-gray-900 sm:text-5xl lg:text-6xl"
                        >
                            Kembalikan Kilau <br />Sepatu Anda
                        </h1>
                        <p
                            class="max-w-xl text-base leading-relaxed text-gray-600 sm:text-lg"
                        >
                            Perawatan sepatu premium dengan teknologi
                            pembersihan mendalam. Kami merawat setiap serat dan
                            material dengan presisi klinis.
                        </p>
                        <div class="flex flex-wrap gap-4 pt-2">
                            <a
                                href="#layanan"
                                class="flex items-center gap-2 rounded-xl bg-[#046C56] px-6 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-[#035241]"
                            >
                                Lihat Layanan
                                <Sparkles :size="18" />
                            </a>
                            <Link
                                :href="index()"
                                class="flex items-center gap-2 rounded-xl bg-[#EEF2FF] px-6 py-3.5 text-sm font-semibold text-[#3B82F6] transition hover:bg-[#e0e7ff]"
                            >
                                Cek Status Pengerjaan
                                <SquareArrowOutUpRight :size="18" />
                            </Link>
                        </div>
                    </div>

                    <!-- Hero Image & Badge -->
                    <div class="relative flex justify-center">
                        <div
                            class="relative w-full max-w-lg rounded-[2.5rem] bg-[#F8F9FA] p-8"
                        >
                            <img
                                src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=800&q=80"
                                alt="Sepatu Putih"
                                class="w-full object-contain mix-blend-multiply drop-shadow-xl"
                            />
                            <!-- Floating Warranty Badge -->
                            <div
                                class="absolute right-6 bottom-6 flex items-center gap-2 rounded-full border border-gray-100 bg-white px-4 py-2 text-xs font-bold text-gray-800 shadow-lg"
                            >
                                <span
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-[#046C56] text-white"
                                >
                                    <svg
                                        class="h-3 w-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="3"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </span>
                                100% Garansi
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: LAYANAN KAMI -->
        <section id="layanan" class="py-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-12">
                <div class="text-center">
                    <span
                        class="text-xs font-bold tracking-widest text-[#046C56] uppercase"
                        >Pilihan Perawatan</span
                    >
                    <h2
                        class="mt-1 text-3xl font-extrabold text-gray-900 sm:text-4xl"
                    >
                        Layanan Kami
                    </h2>
                </div>

                <div
                    class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <!-- Card 1: Fast Clean -->
                    <div
                        class="group overflow-hidden rounded-2xl bg-[#F8F9FA] transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="relative h-48 w-full overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80"
                                alt="Fast Clean"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900">
                                Fast Clean
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-gray-500"
                            >
                                Pembersihan instan untuk bagian luar sepatu.
                                Cocok untuk perawatan rutin mingguan.
                            </p>
                            <div class="mt-6 flex items-center justify-between">
                                <span class="text-xs text-gray-400"
                                    >Mulai dari</span
                                >
                                <span
                                    class="text-base font-extrabold text-[#046C56]"
                                    >Rp 50k</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Deep Clean -->
                    <div
                        class="group overflow-hidden rounded-2xl bg-[#F8F9FA] transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="relative h-48 w-full overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=600&q=80"
                                alt="Deep Clean"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">
                                    Deep Clean
                                </h3>
                                <span
                                    class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-600"
                                    >POPULER</span
                                >
                            </div>
                            <p
                                class="mt-2 text-xs leading-relaxed text-gray-500"
                            >
                                Pembersihan menyeluruh luar dalam, insole, tali,
                                hingga menghilangkan bau tidak sedap.
                            </p>
                            <div class="mt-6 flex items-center justify-between">
                                <span class="text-xs text-gray-400"
                                    >Mulai dari</span
                                >
                                <span
                                    class="text-base font-extrabold text-[#046C56]"
                                    >Rp 85k</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Unyellowing -->
                    <div
                        class="group overflow-hidden rounded-2xl bg-[#F8F9FA] transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="relative h-48 w-full overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?auto=format&fit=crop&w=600&q=80"
                                alt="Unyellowing"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900">
                                Unyellowing
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-gray-500"
                            >
                                Restorasi sol sepatu yang menguning kembali
                                putih bersih seperti baru menggunakan cairan
                                khusus.
                            </p>
                            <div class="mt-6 flex items-center justify-between">
                                <span class="text-xs text-gray-400"
                                    >Mulai dari</span
                                >
                                <span
                                    class="text-base font-extrabold text-[#046C56]"
                                    >Rp 120k</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Leather Care -->
                    <div
                        class="group overflow-hidden rounded-2xl bg-[#F8F9FA] transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div class="relative h-48 w-full overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=600&q=80"
                                alt="Leather Care"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900">
                                Leather Care
                            </h3>
                            <p
                                class="mt-2 text-xs leading-relaxed text-gray-500"
                            >
                                Perawatan khusus material kulit (suede, nubuck,
                                full grain) untuk menjaga tekstur dan warna.
                            </p>
                            <div class="mt-6 flex items-center justify-between">
                                <span class="text-xs text-gray-400"
                                    >Mulai dari</span
                                >
                                <span
                                    class="text-base font-extrabold text-[#046C56]"
                                    >Rp 150k</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 4: FAQ (PERTANYAAN UMUM) -->
        <section id="faq" class="bg-[#F9FAFB] py-20">
            <div class="mx-auto max-w-3xl px-6">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">
                        Pertanyaan Umum
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Informasi yang sering ditanyakan mengenai layanan kami.
                    </p>
                </div>

                <div class="mt-10 space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="overflow-hidden rounded-2xl border border-gray-100 bg-white transition"
                    >
                        <button
                            @click="toggleFaq(index)"
                            class="flex w-full items-center justify-between px-6 py-5 text-left font-semibold text-gray-900"
                        >
                            <span>{{ faq.question }}</span>
                            <svg
                                class="h-5 w-5 transform text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-180': activeFaq === index }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>
                        <div
                            v-show="activeFaq === index"
                            class="border-t border-gray-50 px-6 pt-2 pb-6 text-sm leading-relaxed text-gray-600"
                        >
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-gray-100 bg-white pt-16 pb-8">
            <div class="mx-auto max-w-7xl px-6 lg:px-12">
                <div class="grid grid-cols-1 gap-10 md:grid-cols-4">
                    <!-- Company Info -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-extrabold text-gray-900">
                            HydroClean<span class="text-[#046C56]"> Logic</span>
                        </h3>
                        <p class="text-xs leading-relaxed text-gray-500">
                            Perawatan sepatu premium dengan teknologi
                            pembersihan mendalam. Kami mengembalikan kilau
                            sepatu Anda dengan presisi klinis.
                        </p>
                        <div class="flex items-center gap-3 pt-2">
                            <a
                                href="#"
                                class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"
                                    />
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Links -->
                    <div>
                        <h4
                            class="text-xs font-bold tracking-wider text-gray-900 uppercase"
                        >
                            Tautan
                        </h4>
                        <ul class="mt-4 space-y-2 text-xs text-gray-600">
                            <li>
                                <a href="#" class="hover:text-gray-900"
                                    >Tentang Kami</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-gray-900"
                                    >Syarat & Ketentuan</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-gray-900"
                                    >Kebijakan Privasi</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-gray-900"
                                    >Hubungi Kami</a
                                >
                            </li>
                        </ul>
                    </div>

                    <!-- Location -->
                    <div>
                        <h4
                            class="text-xs font-bold tracking-wider text-gray-900 uppercase"
                        >
                            Lokasi
                        </h4>
                        <p
                            class="mt-4 flex items-start gap-2 text-xs text-gray-600"
                        >
                            <svg
                                class="h-4 w-4 shrink-0 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            Jl. Sudirman No. 123, SCBD<br />Jakarta Selatan,
                            12190
                        </p>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4
                            class="text-xs font-bold tracking-wider text-gray-900 uppercase"
                        >
                            Kontak
                        </h4>
                        <ul class="mt-4 space-y-2 text-xs text-gray-600">
                            <li class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h32a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"
                                    />
                                </svg>
                                +62 811-2233-4455
                            </li>
                            <li class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                Senin - Sabtu: 09.00 - 20.00
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div
                    class="mt-12 border-t border-gray-100 pt-6 text-center text-xs text-gray-400"
                >
                    © 2026 HydroClean Logic. Perawatan Sepatu Profesional.
                </div>
            </div>
        </footer>
    </div>
</template>
