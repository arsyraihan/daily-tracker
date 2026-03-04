<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { 
    Calendar, Download, 
    BarChart3, ClipboardCheck, ListTodo,
    Eye, X, Loader2, Info
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const attendanceForm = useForm({
    type: 'attendance',
    filter: 'monthly',
    date: new Date().toISOString().split('T')[0].substring(0, 7),
});

const taskForm = useForm({
    type: 'tasks',
    filter: 'monthly',
    date: new Date().toISOString().split('T')[0].substring(0, 7),
});

const productivityForm = useForm({
    type: 'productivity',
    filter: 'monthly',
    date: new Date().toISOString().split('T')[0].substring(0, 7),
});

const activeTab = ref('attendance');
const isPreviewOpen = ref(false);
const previewUrl = ref('');
const isProcessing = ref(false);

const currentForm = computed(() => {
    if (activeTab.value === 'attendance') return attendanceForm;
    if (activeTab.value === 'missions') return taskForm;
    return productivityForm;
});

const handleExport = (form) => {
    const url = route('employee.reports.export') + '?' + new URLSearchParams(form.data()).toString();
    window.location.href = url;
};

const handlePreview = (form) => {
    isProcessing.value = true;
    const params = new URLSearchParams(form.data());
    params.append('preview', 'true');
    previewUrl.value = route('employee.reports.export') + '?' + params.toString();
    
    setTimeout(() => {
        isPreviewOpen.value = true;
        isProcessing.value = false;
    }, 400);
};

const tabInfo = computed(() => {
    switch (activeTab.value) {
        case 'attendance': return { title: 'Rekap Absensi Saya', icon: ClipboardCheck, color: 'text-indigo-600', bg: 'bg-indigo-100 dark:bg-indigo-500/20' };
        case 'missions': return { title: 'Laporan Tugas Saya', icon: ListTodo, color: 'text-emerald-600', bg: 'bg-emerald-100 dark:bg-emerald-500/20' };
        case 'productivity': return { title: 'Produktivitas Saya', icon: BarChart3, color: 'text-amber-600', bg: 'bg-amber-100 dark:bg-amber-500/20' };
        default: return {};
    }
});
</script>

<template>
    <Head title="Laporan Saya" />
    
    <UserLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Laporan Saya</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Export dan analisa data operasional individu Anda.</p>
                </div>
            </div>
        </template>

        <div class="py-6 min-h-[calc(100vh-12rem)]">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar Navigation -->
                <div class="w-full lg:w-72 flex-shrink-0">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-2">
                        <button
                            @click="activeTab = 'attendance'"
                            :class="['w-full flex items-center gap-3 px-4 py-3.5 rounded-xl font-medium transition-colors', activeTab === 'attendance' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']"
                        >
                            <ClipboardCheck class="w-5 h-5 flex-shrink-0" />
                            Rekap Absensi
                        </button>
                        <button
                            @click="activeTab = 'missions'"
                            :class="['w-full flex items-center gap-3 px-4 py-3.5 rounded-xl font-medium transition-colors mt-1', activeTab === 'missions' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']"
                        >
                            <ListTodo class="w-5 h-5 flex-shrink-0" />
                            Laporan Tugas
                        </button>
                        <button
                            @click="activeTab = 'productivity'"
                            :class="['w-full flex items-center gap-3 px-4 py-3.5 rounded-xl font-medium transition-colors mt-1', activeTab === 'productivity' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800']"
                        >
                            <BarChart3 class="w-5 h-5 flex-shrink-0" />
                            Produktivitas
                        </button>
                    </div>

                    <div class="mt-6 bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/50">
                        <div class="flex gap-3 mb-2">
                            <Info class="w-5 h-5 text-indigo-600 shrink-0" />
                            <h4 class="font-semibold text-slate-800 dark:text-slate-200">Informasi Format</h4>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-8">
                            Semua dokumen akan diekspor dalam format <span class="font-medium text-slate-800 dark:text-slate-200">.xls</span> standar dan siap dicetak secara rapi.
                        </p>
                    </div>
                </div>

                <!-- Main Configuration Panel -->
                <div class="flex-grow bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-8 md:p-10 flex flex-col justify-center">
                    <div class="max-w-2xl w-full mx-auto">
                        <!-- Header -->
                        <div class="flex items-center gap-5 mb-10">
                            <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center border', tabInfo.bg, tabInfo.color.replace('text', 'border') + '/20']">
                                <component :is="tabInfo.icon" :class="['w-8 h-8', tabInfo.color]" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ tabInfo.title }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 mt-1">Konfigurasi rentang laporan yang ingin di ekspor.</p>
                            </div>
                        </div>

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                            <!-- Toggle Mode -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Rentang Waktu</label>
                                <div class="flex bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <button
                                        @click="currentForm.filter = 'daily'"
                                        :class="['flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all', currentForm.filter === 'daily' ? 'bg-white dark:bg-slate-700 text-indigo-700 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200']"
                                    >
                                        Harian
                                    </button>
                                    <button
                                        @click="currentForm.filter = 'monthly'"
                                        :class="['flex-1 py-2.5 px-4 text-sm font-semibold rounded-lg transition-all', currentForm.filter === 'monthly' ? 'bg-white dark:bg-slate-700 text-indigo-700 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200']"
                                    >
                                        Bulanan
                                    </button>
                                </div>
                            </div>

                            <!-- Date Picker -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Pilih Tanggal</label>
                                <div class="relative">
                                    <input 
                                        :type="currentForm.filter === 'daily' ? 'date' : 'month'" 
                                        v-model="currentForm.date"
                                        class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-700 dark:text-slate-300 font-medium focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-900 transition-all outline-none pl-12"
                                    />
                                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-4 border-t border-slate-100 dark:border-slate-800 pt-8">
                            <button
                                @click="handlePreview(currentForm)"
                                :disabled="isProcessing"
                                class="flex-1 px-6 py-3.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl border border-slate-200 dark:border-slate-700 transition-colors flex justify-center items-center gap-3 disabled:opacity-50"
                            >
                                <Loader2 v-if="isProcessing" class="w-5 h-5 animate-spin" />
                                <Eye v-else class="w-5 h-5" />
                                Live Preview
                            </button>
                            <button
                                @click="handleExport(currentForm)"
                                class="flex-[1.5] px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors flex justify-center items-center gap-3 shadow-md shadow-indigo-200 dark:shadow-none"
                            >
                                <Download class="w-5 h-5" />
                                Unduh File Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="isPreviewOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 lg:p-8">
            <div @click="isPreviewOpen = false" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            
            <div class="relative bg-white dark:bg-slate-900 w-full max-w-7xl h-full rounded-2xl shadow-2xl flex flex-col overflow-hidden ring-1 ring-slate-900/10">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 dark:text-white">Review Dokumen Excel</h4>
                        <p class="text-sm text-slate-500">Pratinjau struktur tabel sebelum diunduh</p>
                    </div>
                    
                    <div class="flex items-center gap-3 sm:gap-4">
                        <button 
                            @click="handleExport(currentForm)" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2"
                        >
                            <Download class="w-4 h-4" />
                            <span class="hidden sm:inline">Unduh Sekarang</span>
                            <span class="sm:hidden">Unduh</span>
                        </button>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                        <button 
                            @click="isPreviewOpen = false" 
                            class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg text-slate-600 dark:text-slate-400 transition-colors"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Modal Body (Iframe container) -->
                <div class="flex-1 bg-slate-100 dark:bg-slate-950 p-4 sm:p-6 lg:p-10 overflow-hidden flex flex-col">
                    <div class="w-full h-full bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden flex">
                        <iframe :src="previewUrl" class="w-full h-full border-none"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
/* Fix default margin on calendar icon in chrome */
input[type="date"]::-webkit-calendar-picker-indicator,
input[type="month"]::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
    z-index: 10;
}
</style>
