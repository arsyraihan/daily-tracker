<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

// Menerima data dari Controller
const props = defineProps({
    activities: Array,
});

// Setup form Inertia
const form = useForm({
    tanggal: '',
    task: '',
    start_time: '',
    end_time: '',
    output: '',
    kategori: 'Daily Task', // Default value
});

// Fungsi submit form
const submitForm = () => {
    form.post(route('activities.store'), {
        onSuccess: () => {
            // Reset form setelah sukses, tapi biarkan tanggal tetap sama agar input berikutnya lebih cepat
            form.reset('task', 'start_time', 'end_time', 'output');
            alert('Aktivitas berhasil disimpan!');
        },
    });
};

// Fungsi hapus aktivitas
const deleteActivity = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus aktivitas ini?')) {
        router.delete(route('activities.destroy', id));
    }
};

// Fungsi format tanggal sederhana
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};
</script>

<template>
    <AppLayout title="Daily Tracker">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Input Aktivitas Harian
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Catat Kegiatan Baru</h3>
                    <form @submit.prevent="submitForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                            <input v-model="form.tanggal" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <div v-if="form.errors.tanggal" class="text-red-500 text-sm mt-1">{{ form.errors.tanggal }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori</label>
                            <select v-model="form.kategori" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="BSC / OKR">BSC / OKR</option>
                                <option value="Daily Task">Daily Task</option>
                                <option value="Improvement Goal">Improvement Goal</option>
                                <option value="Cuti">Cuti</option>
                                <option value="Day Off">Day Off</option>
                                <option value="Libur">Libur</option>
                                <option value="Ibadah">Ibadah</option>
                            </select>
                            <div v-if="form.errors.kategori" class="text-red-500 text-sm mt-1">{{ form.errors.kategori }}</div>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Task / Kegiatan</label>
                            <input v-model="form.task" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required placeholder="Contoh: Membuat rancangan database">
                            <div v-if="form.errors.task" class="text-red-500 text-sm mt-1">{{ form.errors.task }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Waktu</label>
                            <input v-model="form.start_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <div v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Waktu</label>
                            <input v-model="form.end_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <div v-if="form.errors.end_time" class="text-red-500 text-sm mt-1">{{ form.errors.end_time }}</div>
                        </div>

                        <div class="col-span-1 md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Output / Hasil Kerja (Opsional)</label>
                            <textarea v-model="form.output" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Contoh: Skema ERD berhasil dibuat"></textarea>
                            <div v-if="form.errors.output" class="text-red-500 text-sm mt-1">{{ form.errors.output }}</div>
                        </div>

                        <div class="col-span-1 md:col-span-2 lg:col-span-3 mt-2">
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Aktivitas
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-700">Riwayat Aktivitas Anda</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi (Menit)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="activity in activities" :key="activity.id">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ formatDate(activity.tanggal) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ activity.kategori }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ activity.task }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ activity.start_time.substring(0,5) }} - {{ activity.end_time.substring(0,5) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ activity.durasi_menit }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                        <button @click="deleteActivity(activity.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="activities.length === 0">
                                    <td colspan="6" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada aktivitas yang dicatat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>