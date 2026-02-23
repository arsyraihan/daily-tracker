<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    activities: Array,
    role: String,
});

// Fungsi memformat tanggal
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Fungsi warna badge kategori
const getCategoryColor = (kategori) => {
    const colors = {
        'BSC / OKR': 'bg-blue-100 text-blue-800',
        'Daily Task': 'bg-green-100 text-green-800',
        'Improvement Goal': 'bg-purple-100 text-purple-800',
        'Cuti': 'bg-yellow-100 text-yellow-800',
        'Day Off': 'bg-orange-100 text-orange-800',
        'Libur': 'bg-red-100 text-red-800',
        'Ibadah': 'bg-gray-100 text-gray-800',
    };
    return colors[kategori] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AppLayout title="Dashboard Performa">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ role === 'supervisor' ? 'Monitoring Aktivitas Seluruh Karyawan' : 'Riwayat Aktivitas Harian Anda' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th v-if="role === 'supervisor'" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Karyawan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task / Kegiatan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi (Menit)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Output</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="activity in activities" :key="activity.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(activity.tanggal) }}</td>
                                    
                                    <td v-if="role === 'supervisor'" class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        {{ activity.user ? activity.user.name : 'Unknown' }}
                                    </td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', getCategoryColor(activity.kategori)]">
                                            {{ activity.kategori }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">{{ activity.task }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ activity.start_time.substring(0,5) }} - {{ activity.end_time.substring(0,5) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-medium">{{ activity.durasi_menit ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ activity.output || '-' }}</td>
                                </tr>
                                
                                <tr v-if="activities.length === 0">
                                    <td :colspan="role === 'supervisor' ? 7 : 6" class="px-4 py-8 text-center text-sm text-gray-500">
                                        Belum ada rekaman aktivitas yang ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>