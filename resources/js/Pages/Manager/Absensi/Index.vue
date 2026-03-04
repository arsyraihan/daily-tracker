<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { 
    Plus, Calendar, Eye, Trash2, 
    Lock, Unlock, Clock, CheckCircle2,
    Layers, UserPlus, ChevronDown, Activity, Sparkles
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const isModalOpen = ref(false);
const isBulkModalOpen = ref(false);

const props = defineProps({
    sessions: Object,
    employees: Array,
    filters: Object,
});

const filterDate = ref(props.filters.date || '');

watch(filterDate, (value) => {
    router.get(route('manager.absensi.index'), { date: value }, {
        preserveState: true,
        replace: true
    });
});

const form = useForm({
    nama: '',
    tipe: 'masuk',
    tanggal: new Date().toISOString().split('T')[0],
    jam_mulai: '07:00',
    jam_selesai: '08:30',
    is_bulk: true,
    sessions: [
        { active: true, tipe: 'masuk', nama: 'Absensi Masuk Pagi', jam_mulai: '07:00', jam_selesai: '08:30' },
        { active: true, tipe: 'istirahat', nama: 'Istirahat Siang', jam_mulai: '12:00', jam_selesai: '13:00' },
        { active: true, tipe: 'pulang', nama: 'Absensi Pulang', jam_mulai: '16:30', jam_selesai: '18:00' },
    ],
});

const setTimePreset = (tipe) => {
    form.tipe = tipe;
    if (tipe === 'masuk') {
        form.nama = 'Absensi Masuk Pagi';
        form.jam_mulai = '07:00';
        form.jam_selesai = '08:30';
    } else if (tipe === 'istirahat') {
        form.nama = 'Istirahat Siang';
        form.jam_mulai = '12:00';
        form.jam_selesai = '13:00';
    } else if (tipe === 'pulang') {
        form.nama = 'Absensi Pulang';
        form.jam_mulai = '16:30';
        form.jam_selesai = '18:00';
    }
};

// Default preset
setTimePreset('masuk');

const submit = () => {
    form.post(route('manager.absensi.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            setTimePreset('masuk');
        }
    });
};

const closeSession = (id) => {
    if (confirm('Tutup sesi absensi ini? No more check-ins after cluster.')) {
        useForm({}).put(route('manager.absensi.update', id));
    }
};

const deleteSession = (id) => {
    if (confirm('Hapus sesi ini secara permanen?')) {
        useForm({}).delete(route('manager.absensi.destroy', id));
    }
};

const bulkForm = useForm({
    user_id: '',
    tanggal: new Date().toISOString().split('T')[0],
    records: {
        masuk: { status: 'hadir', active: true },
        istirahat: { status: 'hadir', active: false },
        pulang: { status: 'hadir', active: true },
    },
    catatan: '',
});

const submitBulk = () => {
    // Filter out inactive records before sending
    const activeRecords = {};
    Object.keys(bulkForm.records).forEach(key => {
        if (bulkForm.records[key].active && bulkForm.records[key].jam_absen) {
            activeRecords[key] = bulkForm.records[key];
        }
    });

    const payload = {
        user_id: bulkForm.user_id,
        tanggal: bulkForm.tanggal,
        catatan: bulkForm.catatan,
        records: activeRecords
    };

    useForm(payload).post(route('manager.absensi.record.bulk'), {
        preserveScroll: true,
        onSuccess: () => {
            isBulkModalOpen.value = false;
            bulkForm.reset();
        }
    });
};

</script>

<template>
    <ManagerLayout title="Attendance Sessions">
        <template #header>
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none">Management <span class="text-indigo-600">Absensi</span></h2>
                    <p class="text-slate-500 font-bold uppercase text-[10px] tracking-[0.3em] mt-3">Registry of active and closed attendance windows for your team.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <Calendar class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                         </div>
                         <input 
                            type="date" 
                            v-model="filterDate"
                            class="pl-11 pr-4 py-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/20 transition-all outline-none"
                         />
                    </div>
                    <button 
                        @click="isBulkModalOpen = true"
                        class="bg-white dark:bg-slate-900 text-indigo-600 border border-slate-200 dark:border-slate-800 px-8 py-4 rounded-xl font-black text-xs uppercase tracking-[0.2em] hover:bg-slate-50 transition-all shadow-xl shadow-slate-200/50 flex items-center gap-3"
                    >
                        <UserPlus class="w-5 h-5" />
                        Manual Record
                    </button>
                    <button 
                        @click="isModalOpen = true"
                        class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-black text-xs uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 dark:shadow-none active:scale-95 flex items-center gap-3"
                    >
                        <Plus class="w-5 h-5" />
                        New Session
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="sessions.data.length === 0" class="bg-white dark:bg-slate-900 rounded-2xl p-20 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-8">
                    <Calendar class="w-12 h-12 text-slate-300" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-2">No Active Tracks</h3>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest italic">Open your first attendance session to start tracking logs.</p>
            </div>

            <div v-else class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex-1">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Session Info</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Schedule</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="session in sessions.data" :key="session.id" class="group hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-all">
                                <td class="p-8">
                                    <div class="flex items-center gap-6">
                                        <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center font-black transition-all group-hover:scale-110 shadow-sm border', 
                                            session.tipe === 'masuk' ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 
                                            session.tipe === 'istirahat' ? 'bg-amber-50 text-amber-600 border-amber-100' : 
                                            'bg-rose-50 text-rose-600 border-rose-100'
                                        ]">
                                             <Clock v-if="session.tipe === 'masuk'" class="w-6 h-6" />
                                             <Unlock v-else-if="session.tipe === 'istirahat'" class="w-6 h-6" />
                                             <Lock v-else class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight mb-1">{{ session.nama }}</h4>
                                            <div class="flex items-center gap-3">
                                                 <span :class="['text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-[0.1em] border', 
                                                     session.tipe === 'masuk' ? 'bg-indigo-100/30 text-indigo-600 border-indigo-100/50' : 
                                                     session.tipe === 'istirahat' ? 'bg-amber-100/30 text-amber-600 border-amber-100/50' : 
                                                     'bg-rose-100/30 text-rose-600 border-rose-100/50'
                                                 ]">
                                                     {{ session.tipe }}
                                                 </span>
                                                 <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ new Date(session.tanggal).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8 text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-widest">
                                    <div class="flex items-center gap-2">
                                         <Clock class="w-3.5 h-3.5 text-indigo-500" />
                                         {{ session.jam_mulai.substring(0,5) }} - {{ session.jam_selesai.substring(0,5) }}
                                    </div>
                                </td>
                                <td class="p-8">
                                    <span v-if="session.is_active" class="text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border bg-emerald-500 text-white border-emerald-400 animate-pulse">
                                        LIVE
                                    </span>
                                    <span v-else :class="['text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border', session.status === 'dibuka' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-50 text-slate-400 border-slate-100']">
                                        {{ session.status === 'dibuka' ? 'Scheduled' : 'Closed' }}
                                    </span>
                                </td>
                                <td class="p-8 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('manager.absensi.show', session.id)" class="px-5 py-2.5 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-md">
                                            Logs
                                        </Link>
                                        <div class="flex items-center bg-slate-50 dark:bg-slate-800 p-1 rounded-xl">
                                            <button v-if="session.status === 'dibuka'" @click="closeSession(session.id)" class="p-2.5 text-amber-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all shadow-sm">
                                                <Lock class="w-4 h-4" />
                                            </button>
                                            <button @click="deleteSession(session.id)" class="p-2.5 text-rose-600 hover:bg-white dark:hover:bg-slate-700 rounded-lg transition-all shadow-sm">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Footer -->
                <div v-if="sessions.links.length > 3" class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Viewing {{ sessions.from }} - {{ sessions.to }} of {{ sessions.total }} sessions
                     </p>
                     <div class="flex items-center gap-1">
                        <template v-for="(link, k) in sessions.links" :key="k">
                            <div v-if="link.url === null" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-400 bg-white dark:bg-slate-900 opacity-50 cursor-not-allowed" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="['px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase transition-all', link.active ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50']" v-html="link.label" />
                        </template>
                     </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <DialogModal :show="isModalOpen" @close="isModalOpen = false">
            <template #title>
                <div class="p-2">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Create <span class="text-indigo-600">Attendance Session</span></h3>
                     <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Configure your divisions attendance window.</p>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="submit" id="createSessionForm" class="p-2 space-y-8">
                    <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800 p-4 rounded-3xl border border-dashed border-slate-200">
                        <div class="flex items-center gap-3">
                            <Layers class="w-5 h-5 text-indigo-600" />
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Bulk Mode</h4>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Create multiple sessions at once</p>
                            </div>
                        </div>
                        <button type="button" @click="form.is_bulk = !form.is_bulk" :class="['w-12 h-6 rounded-full transition-all relative', form.is_bulk ? 'bg-indigo-600' : 'bg-slate-300']">
                            <div :class="['w-4 h-4 bg-white rounded-full absolute top-1 transition-all', form.is_bulk ? 'right-1' : 'left-1']"></div>
                        </button>
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-3 ml-1">Date</label>
                        <input type="date" v-model="form.tanggal" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100" />
                    </div>

                    <!-- Single Mode -->
                    <div v-if="!form.is_bulk" class="space-y-8 animate-in fade-in zoom-in-95 duration-200">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-3 ml-1">Session Identity</label>
                            <input 
                                type="text" 
                                v-model="form.nama"
                                placeholder="e.g. Morning Inbound"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100 transition-all"
                                required
                            />
                        </div>

                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-4 ml-1">Session Type</label>
                            <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="setTimePreset('masuk')" :class="['group p-5 rounded-[1.8rem] flex flex-col items-center gap-3 transition-all border-2', form.tipe === 'masuk' ? 'bg-indigo-600 border-indigo-600 shadow-2xl shadow-indigo-500/20 scale-105' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-400']">
                                    <Clock :class="['w-6 h-6', form.tipe === 'masuk' ? 'text-white' : 'text-slate-300']" />
                                    <span :class="['text-[10px] font-black uppercase tracking-widest', form.tipe === 'masuk' ? 'text-white' : 'text-slate-400']">IN</span>
                                </button>
                                <button type="button" @click="setTimePreset('istirahat')" :class="['group p-5 rounded-[1.8rem] flex flex-col items-center gap-3 transition-all border-2', form.tipe === 'istirahat' ? 'bg-amber-600 border-amber-600 shadow-2xl shadow-amber-500/20 scale-105' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-400']">
                                    <Unlock :class="['w-6 h-6', form.tipe === 'istirahat' ? 'text-white' : 'text-slate-300']" />
                                    <span :class="['text-[10px] font-black uppercase tracking-widest', form.tipe === 'istirahat' ? 'text-white' : 'text-slate-400']">BREAK</span>
                                </button>
                                <button type="button" @click="setTimePreset('pulang')" :class="['group p-5 rounded-[1.8rem] flex flex-col items-center gap-3 transition-all border-2', form.tipe === 'pulang' ? 'bg-rose-600 border-rose-600 shadow-2xl shadow-rose-500/20 scale-105' : 'bg-slate-50 dark:bg-slate-800 border-transparent text-slate-400']">
                                    <Lock :class="['w-6 h-6', form.tipe === 'pulang' ? 'text-white' : 'text-slate-300']" />
                                    <span :class="['text-[10px] font-black uppercase tracking-widest', form.tipe === 'pulang' ? 'text-white' : 'text-slate-400']">OUT</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-3 ml-1">Start</label>
                                <input type="time" v-model="form.jam_mulai" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                            </div>
                            <div>
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-3 ml-1">Deadline</label>
                                <input type="time" v-model="form.jam_selesai" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Mode -->
                    <div v-else class="space-y-4 animate-in fade-in zoom-in-95 duration-200">
                        <div v-for="(session, index) in form.sessions" :key="index" :class="['p-6 rounded-[2rem] border-2 transition-all', session.active ? 'bg-white border-indigo-100 shadow-lg' : 'bg-slate-50/50 border-transparent opacity-60']">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div @click="session.active = !session.active" :class="['w-8 h-8 rounded-lg flex items-center justify-center cursor-pointer transition-all', session.active ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-400']">
                                            <CheckCircle2 class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h4 class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ session.tipe }}</h4>
                                        </div>
                                    </div>
                                    <input v-if="session.active" type="text" v-model="session.nama" class="bg-slate-50 border-none rounded-xl p-2 text-[10px] font-black uppercase tracking-widest w-1/2" />
                                </div>
                                <div v-if="session.active" class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Start</label>
                                        <input type="time" v-model="session.jam_mulai" class="w-full bg-slate-50 border-none rounded-xl p-2 text-xs font-black" />
                                    </div>
                                    <div>
                                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Deadline</label>
                                        <input type="time" v-model="session.jam_selesai" class="w-full bg-slate-50 border-none rounded-xl p-2 text-xs font-black" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button 
                        @click="isModalOpen = false"
                        class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-2xl transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        form="createSessionForm"
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-indigo-600 text-white p-5 rounded-3xl font-black text-xs uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 disabled:opacity-50"
                    >
                        Launch Session
                    </button>
                </div>
            </template>
        </DialogModal>
        <!-- Bulk Attendance Modal -->
        <DialogModal :show="isBulkModalOpen" @close="isBulkModalOpen = false" maxWidth="2xl">
            <template #title>
                <div class="p-2">
                     <div class="flex items-center gap-3 mb-1">
                        <Sparkles class="w-5 h-5 text-indigo-500" />
                        <span class="text-indigo-600 dark:text-indigo-400 font-black text-[10px] uppercase tracking-[0.3em]">Direct Admin Override</span>
                     </div>
                     <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">MANUAL <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">ATTENDANCE</span></h3>
                     <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">Efficiently record multiple logs for a specific personnel.</p>
                </div>
            </template>

            <template #content>
                 <form @submit.prevent="submitBulk" id="bulkAttendanceForm" class="p-2 space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-700">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-4 ml-1">Personnel Selection</label>
                            <div class="relative group">
                                <select v-model="bulkForm.user_id" required class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 shadow-sm appearance-none">
                                    <option value="">CHOOSE EMPLOYEE</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                        {{ emp.name }} ({{ emp.kode_karyawan }})
                                    </option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none group-hover:text-indigo-500 transition-colors" />
                            </div>
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-4 ml-1">Operational Date</label>
                            <div class="relative">
                                <input type="date" v-model="bulkForm.tanggal" class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100 shadow-sm" />
                                <Calendar class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-2 ml-1">Daily Log Targets</label>
                        
                        <div class="space-y-3">
                            <!-- Matrix for Records -->
                            <div v-for="(record, key) in bulkForm.records" :key="key" 
                                :class="['p-5 rounded-[2rem] border-2 transition-all relative overflow-hidden group', 
                                    record.active ? 
                                        (key === 'masuk' ? 'bg-indigo-50/50 border-indigo-100' : 
                                         key === 'istirahat' ? 'bg-amber-50/50 border-amber-100' : 
                                         'bg-rose-50/50 border-rose-100') : 
                                        'bg-slate-50 border-transparent opacity-60']"
                            >
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div @click="record.active = !record.active" 
                                            :class="['w-10 h-10 rounded-xl flex items-center justify-center cursor-pointer transition-all', 
                                                record.active ? 
                                                    (key === 'masuk' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 
                                                     key === 'istirahat' ? 'bg-amber-600 text-white shadow-lg shadow-amber-200' : 
                                                     'bg-rose-600 text-white shadow-lg shadow-rose-200') : 
                                                    'bg-slate-200 text-slate-400']"
                                        >
                                            <CheckCircle2 class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h4 class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ key }} Window</h4>
                                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none mt-1">Automatic Preset</p>
                                        </div>
                                    </div>

                                    <div v-if="record.active" class="flex items-center gap-3 animate-in fade-in zoom-in-95">
                                        <div class="relative">
                                            <select v-model="record.status" class="bg-white border-none rounded-xl pl-8 pr-10 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-sm appearance-none min-w-[120px]">
                                                <option value="hadir">Present</option>
                                                <option v-if="key === 'masuk'" value="terlambat">LATE</option>
                                                <option value="sakit">SICK</option>
                                                <option value="izin">PERMIT</option>
                                            </select>
                                            <Activity class="absolute left-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400" />
                                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2.5rem] border border-dashed border-slate-200">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-4 ml-1 italic">Authorized Context / Override Note</label>
                        <textarea v-model="bulkForm.catatan" class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl p-6 text-sm font-bold text-slate-600 dark:text-slate-300 focus:ring-4 focus:ring-indigo-100 min-h-[120px] shadow-sm italic placeholder:text-slate-300" placeholder="Provide justification for this manual entry..."></textarea>
                    </div>
                 </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-6 pb-6">
                    <button @click="isBulkModalOpen = false" class="px-8 py-5 text-slate-400 font-extrabold text-[10px] uppercase tracking-[0.3em] hover:text-rose-500 hover:bg-rose-50 rounded-2xl transition-all">Discard</button>
                    <button form="bulkAttendanceForm" type="submit" :disabled="bulkForm.processing" class="flex-grow bg-slate-950 dark:bg-indigo-600 text-white p-5 rounded-[1.8rem] font-black text-[11px] uppercase tracking-[0.4em] hover:scale-[1.02] shadow-2xl shadow-indigo-500/20 active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                        <CheckCircle2 class="w-5 h-5" />
                        Execute Submission
                    </button>
                </div>
            </template>
        </DialogModal>
    </ManagerLayout>
</template>
