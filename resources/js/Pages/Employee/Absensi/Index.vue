<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { 
    Clock, LogIn, LogOut, CheckCircle2, 
    Calendar, History, ShieldCheck, HelpCircle,
    Coffee, Palmtree, Thermometer, MessageSquare,
    X, Send, ChevronDown, TrendingUp,
    Timer, Fingerprint, MapPin, AlertCircle,
    UserCircle, Activity, LayoutGrid
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    openSessions: Array,
    history: Object,
    stats: Object,
});

const submitAttendance = (sessionId) => {
    useForm({
        sesi_absensi_id: sessionId,
        catatan: '',
    }).post(route('employee.absensi.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success
        }
    });
};

const showLeaveForm = ref(false);
const activeSessionIdForLeave = ref(null);
const leaveForm = useForm({
    sesi_absensi_id: '',
    status: 'izin',
    catatan: '',
});

const openLeaveForm = (sessionId) => {
    activeSessionIdForLeave.value = sessionId;
    leaveForm.sesi_absensi_id = sessionId;
    showLeaveForm.value = true;
};

const submitLeave = () => {
    leaveForm.post(route('employee.absensi.submit-request'), {
        preserveScroll: true,
        onSuccess: () => {
            showLeaveForm.value = false;
            leaveForm.reset();
            activeSessionIdForLeave.value = null;
        }
    });
};

function getStatusBadgeClass(status) {
    const classes = {
        'hadir': 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
        'terlambat': 'bg-amber-500/10 text-amber-500 border-amber-500/20',
        'sakit': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
        'izin': 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
        'cuti': 'bg-rose-500/10 text-rose-500 border-rose-500/20',
        'alpa': 'bg-slate-500/10 text-slate-400 border-slate-500/20'
    };
    return classes[status] || 'bg-slate-500/10 text-slate-400 border-slate-500/20';
}

const stats = computed(() => [
    { 
        label: 'Attendance Rate', 
        value: '98%', 
        icon: Activity, 
        color: 'text-emerald-500', 
        bg: 'bg-emerald-500/10',
        description: 'Monthly consistency'
    },
    { 
        label: 'On Time Days', 
        value: props.stats?.on_time_count || 0, 
        icon: CheckCircle2, 
        color: 'text-indigo-500', 
        bg: 'bg-indigo-500/10',
        description: 'Excluding permits'
    },
    { 
        label: 'Active Sessions', 
        value: props.openSessions.length, 
        icon: Timer, 
        color: 'text-amber-500', 
        bg: 'bg-amber-500/10',
        description: 'Waiting for action'
    },
    { 
        label: 'Total Logs', 
        value: props.stats?.total_logs || 0, 
        icon: History, 
        color: 'text-rose-500', 
        bg: 'bg-rose-500/10',
        description: 'All-time submissions'
    },
]);

// Automated Refresh Logic
const refreshInterval = ref(null);
const isSyncing = ref(false);

onMounted(() => {
    // Check for new sessions every 30 seconds
    refreshInterval.value = setInterval(() => {
        isSyncing.value = true;
        router.reload({ 
            only: ['openSessions', 'history'],
            preserveScroll: true,
            onFinish: () => {
                setTimeout(() => isSyncing.value = false, 1000);
            }
        });
    }, 30000);
});

onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
});

</script>

<template>
    <UserLayout title="Daily Attendance">
        <template #header>
            <div class="flex items-center justify-between mb-8 py-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-[2px] bg-indigo-600 rounded-full"></div>
                        <span class="text-indigo-600 dark:text-indigo-400 font-black text-[10px] uppercase tracking-[0.3em]">Operational Terminal</span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">Absensi<span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600"> Saya</span></h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ new Date().toLocaleDateString('en-US', { weekday: 'long' }) }}</p>
                        <p class="text-sm font-black text-slate-900 dark:text-white">{{ new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700">
                        <Calendar class="w-5 h-5 text-slate-400" />
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-12">
            <!-- Active Terminal & Quick Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Main Tracking Center -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-slate-900 rounded-3xl p-8 shadow-2xl relative overflow-hidden min-h-[300px]">
                         <!-- Background Visuals -->
                        <div class="absolute inset-0 opacity-20 pointer-events-none">
                            <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                            <div class="absolute bottom-0 left-0 w-64 h-64 bg-violet-600 rounded-full blur-[80px] -ml-20 -mb-20"></div>
                        </div>

                        <div class="relative z-10 flex flex-col h-full">
                            <div class="flex items-center justify-between mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20">
                                        <Fingerprint class="w-6 h-6 text-white" />
                                    </div>
                                    <div>
                                        <p class="text-white/50 text-[10px] font-black uppercase tracking-[0.2em] leading-none mb-1">Status Window</p>
                                        <h3 class="text-xl font-black text-white uppercase tracking-tight">Active Terminal</h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div :class="['flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all duration-500', isSyncing ? 'bg-indigo-500/20 border-indigo-500/30 scale-105' : 'bg-white/5 border-white/5 opacity-40']">
                                        <div :class="['w-1.5 h-1.5 rounded-full', isSyncing ? 'bg-indigo-400 animate-ping' : 'bg-white/30']"></div>
                                        <span class="text-[9px] font-black text-white uppercase tracking-widest">Auto-Sync</span>
                                    </div>
                                    <div v-if="openSessions.length > 0" class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 rounded-xl flex items-center gap-2">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                        <span class="text-emerald-500 text-[10px] font-black uppercase tracking-widest">Live</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="openSessions.length === 0" class="flex-grow flex flex-col items-center justify-center text-center py-10 opacity-50">
                                <Clock class="w-12 h-12 text-white mb-4" />
                                <p class="text-white font-bold text-sm tracking-widest uppercase">No Active Sessions Available</p>
                            </div>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="session in openSessions" :key="session.id" class="p-6 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl transition-all group/card shadow-lg active:scale-[0.98]">
                                    <div class="flex items-start gap-4 mb-6">
                                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shadow-xl border transition-transform group-hover/card:rotate-3', 
                                            session.tipe === 'masuk' ? 'bg-indigo-500 border-indigo-400 text-white' :
                                            session.tipe === 'istirahat' ? 'bg-amber-500 border-amber-400 text-white' :
                                            'bg-rose-500 border-rose-400 text-white'
                                        ]">
                                            <LogIn v-if="session.tipe === 'masuk'" class="w-5 h-5" />
                                            <Coffee v-else-if="session.tipe === 'istirahat'" class="w-5 h-5" />
                                            <LogOut v-else class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-white uppercase tracking-tight leading-none mb-1">{{ session.nama }}</h4>
                                            <p class="text-[9px] text-white/40 font-bold uppercase tracking-widest">{{ session.jam_mulai.substring(0,5) }} - {{ session.jam_selesai.substring(0,5) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="submitAttendance(session.id)" class="bg-white text-indigo-900 py-3 rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-slate-100 transition-all flex items-center justify-center gap-2">
                                            <Send class="w-3.5 h-3.5" />
                                            Record
                                        </button>
                                        <button @click="openLeaveForm(session.id)" class="bg-white/5 text-white/70 py-3 rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-white/10 transition-all border border-white/10 flex items-center justify-center gap-2">
                                            <Palmtree class="w-3.5 h-3.5" />
                                            Permit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Snapshot Stats -->
                <div class="lg:col-span-4 grid grid-cols-2 gap-4">
                    <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:shadow-lg transition-all group">
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shadow-sm mb-4 transition-transform group-hover:scale-110', stat.bg]">
                            <component :is="stat.icon" :class="['w-5 h-5', stat.color]" />
                        </div>
                        <div>
                            <h4 class="text-2xl font-black text-slate-900 dark:text-white leading-none mb-1">{{ stat.value }}</h4>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="p-10 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-slate-50/50 dark:bg-slate-800/20">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-2xl flex items-center justify-center shadow-xl border border-slate-100 dark:border-slate-700">
                            <History class="w-8 h-8 text-indigo-600" />
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Riwayat <span class="text-indigo-600"> Absensi</span></h3>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Timeline of your work logs.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center bg-white dark:bg-slate-800 p-1.5 rounded-2xl shadow-inner border border-slate-100 dark:border-slate-700">
                        <button class="px-6 py-3 bg-indigo-600 shadow-xl rounded-xl text-[10px] font-black uppercase tracking-widest text-white transition-all">Aktivitas Terkini</button>
                        <button class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-all">Arsip Riwayat</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="p-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] w-48">Timestamp</th>
                                <th class="p-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Designation</th>
                                <th class="p-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Metrics</th>
                                <th class="p-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] text-right">Validation Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="history.data.length === 0">
                                <td colspan="4" class="py-32 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20 grayscale">
                                        <History class="w-20 h-20 mb-6 text-slate-400" />
                                        <h4 class="text-xl font-black uppercase tracking-tight">No Logs Generated</h4>
                                        <p class="text-[10px] font-black uppercase tracking-[0.3em] mt-2 italic">Begin your session to populate this log.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="entry in history.data" :key="entry.id" class="group hover:bg-slate-50/50 dark:hover:bg-indigo-500/5 transition-all">
                                <td class="p-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 bg-slate-50 dark:bg-slate-800 rounded-2xl flex flex-col items-center justify-center shadow-inner group-hover:scale-110 transition-transform border border-slate-100 dark:border-slate-700">
                                            <span class="text-[9px] font-black text-slate-400 uppercase leading-none">{{ new Date(entry.sesi_absensi.tanggal).toLocaleDateString('en-US', { month: 'short' }) }}</span>
                                            <span class="text-xl font-black text-slate-900 dark:text-white leading-none mt-1">{{ new Date(entry.sesi_absensi.tanggal).getDate() }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 dark:text-white uppercase leading-none">{{ new Date(entry.sesi_absensi.tanggal).toLocaleDateString('en-US', { weekday: 'short' }) }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">{{ new Date(entry.sesi_absensi.tanggal).getFullYear() }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-indigo-600 transition-colors">{{ entry.sesi_absensi.nama }}</h4>
                                            <div class="flex items-center gap-2 mt-1">
                                                 <Clock class="w-3.5 h-3.5 text-slate-300" />
                                                 <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ entry.sesi_absensi.jam_mulai.substring(0,5) }} Window</span>
                                            </div>
                                        </div>
                                        <!-- Note Indicator -->
                                        <div v-if="entry.catatan" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-indigo-600 transition-all cursor-help group/note relative border border-transparent hover:border-indigo-100 ml-2">
                                            <MessageSquare class="w-4 h-4" />
                                            <!-- Tooltip -->
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-64 p-6 bg-slate-900 text-white rounded-2xl shadow-2xl opacity-0 group-hover/note:opacity-100 transition-all pointer-events-none z-50 transform translate-y-2 group-hover/note:translate-y-0 text-left scale-95 group-hover/note:scale-100">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <UserCircle class="w-4 h-4 text-indigo-400" />
                                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em]">Personnel Log</p>
                                                </div>
                                                <p class="text-xs font-medium text-slate-300 leading-relaxed italic border-l-2 border-indigo-500 pl-4">"{{ entry.catatan }}"</p>
                                                <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-slate-900 rotate-45"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <div class="flex flex-col gap-2">
                                        <span class="text-sm font-black text-slate-900 dark:text-white tracking-widest flex items-center gap-2">
                                            <Timer class="w-4 h-4 text-emerald-500" />
                                            {{ entry.jam_absen || '--:--' }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <span :class="['text-[8px] font-black px-2 py-1 rounded-md border text-center uppercase tracking-widest', getStatusBadgeClass(entry.status)]">
                                                {{ entry.status }}
                                            </span>
                                            <span v-if="entry.menit_keterlambatan > 0" class="text-[8px] font-black text-rose-500 uppercase tracking-widest">
                                                ({{ entry.menit_keterlambatan }} min late)
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="text-right">
                                            <p :class="['text-[10px] font-black uppercase tracking-widest leading-none', 
                                                entry.status_persetujuan === 'disetujui' ? 'text-emerald-500' : 
                                                entry.status_persetujuan === 'menunggu' ? 'text-amber-500' : 
                                                'text-rose-500'
                                            ]">{{ entry.status_persetujuan }}</p>
                                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-1">Verified Logic</p>
                                        </div>
                                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center shadow-lg', 
                                            entry.status_persetujuan === 'disetujui' ? 'bg-emerald-500 text-white' :
                                            entry.status_persetujuan === 'menunggu' ? 'bg-amber-500 text-white' :
                                            'bg-rose-500 text-white'
                                        ]">
                                            <ShieldCheck v-if="entry.status_persetujuan === 'disetujui'" class="w-5 h-5" />
                                            <HelpCircle v-else-if="entry.status_persetujuan === 'menunggu'" class="w-5 h-5 animate-pulse" />
                                            <X v-else class="w-5 h-5" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Footer -->
                <div v-if="history.links.length > 3" class="p-8 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Viewing {{ history.from || 0 }} - {{ history.to || 0 }} of {{ history.total }} work logs
                     </p>
                     <div class="flex items-center gap-1">
                        <template v-for="(link, k) in history.links" :key="k">
                            <div v-if="link.url === null" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-400 bg-white dark:bg-slate-900 opacity-50 cursor-not-allowed" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="['px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase transition-all', link.active ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50']" v-html="link.label" @click="preserveScroll = true" />
                        </template>
                     </div>
                </div>
            </div>
        </div>

        <!-- Leave Form Overlay / Modal Style -->
        <div v-if="showLeaveForm" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl p-10 shadow-2xl border border-white/20 relative overflow-hidden">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Request <span class="text-indigo-600">Permit</span></h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 italic">Submit your legitimate reason for absence.</p>
                        </div>
                        <button @click="showLeaveForm = false" class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all">
                            <X class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="space-y-8">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 mb-4 block">Select Type</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button @click="leaveForm.status = 'izin'" :class="['p-5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2 flex flex-col items-center gap-3', leaveForm.status === 'izin' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-200' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:border-slate-200']">
                                    <Palmtree class="w-6 h-6" />
                                    Izin
                                </button>
                                <button @click="leaveForm.status = 'sakit'" :class="['p-5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2 flex flex-col items-center gap-3', leaveForm.status === 'sakit' ? 'bg-blue-600 border-blue-600 text-white shadow-xl shadow-blue-200' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:border-slate-200']">
                                    <Thermometer class="w-6 h-6" />
                                    Sakit
                                </button>
                                <button @click="leaveForm.status = 'cuti'" :class="['p-5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2 flex flex-col items-center gap-3', leaveForm.status === 'cuti' ? 'bg-rose-600 border-rose-600 text-white shadow-xl shadow-rose-200' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:border-slate-200']">
                                    <Palmtree class="w-6 h-6" />
                                    Cuti
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2 mb-3 block">Description</label>
                            <textarea v-model="leaveForm.catatan" placeholder="Detail your verification requirements here..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder-slate-300 focus:ring-4 focus:ring-indigo-100 transition-all min-h-[140px] p-6"></textarea>
                        </div>

                        <button @click="submitLeave" :disabled="leaveForm.processing" class="w-full bg-slate-900 dark:bg-indigo-600 text-white p-6 rounded-2xl font-black text-[11px] uppercase tracking-[0.4em] hover:scale-[1.02] active:scale-95 transition-all shadow-2xl disabled:opacity-50 flex items-center justify-center gap-3">
                            <Send class="w-5 h-5" />
                            Submit Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
