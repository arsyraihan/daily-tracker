<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { 
    ChevronLeft, CheckCircle2, XCircle, 
    Clock, User, MessageSquare, ShieldCheck, HelpCircle,
    Pencil, Save, X, Trash2, LogIn, LogOut, Coffee, Plus,
    UserPlus, Info
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);

const props = defineProps({
    session: Object,
    availableEmployees: Array,
});

const showAddForm = ref(false);
const addForm = useForm({
    user_id: '',
    jam_absen: '',
    status: 'hadir',
    status_persetujuan: 'disetujui',
    catatan: '',
});


const submitManualAttendance = () => {
    addForm.post(route('manager.absensi.record.store', props.session.id), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        }
    });
};

const deleteRecord = (id) => {
    if (confirm('Are you sure you want to delete this record? This cannot be undone.')) {
        useForm({}).delete(route('manager.absensi.record.destroy', id), {
            preserveScroll: true
        });
    }
};

const editingId = ref(null);
const editForm = useForm({
    jam_absen: '',
    status: '',
    status_persetujuan: '',
    catatan: '',
});

const startEdit = (record) => {
    editingId.value = record.id;
    editForm.jam_absen = record.jam_absen || '';
    editForm.status = record.status;
    editForm.status_persetujuan = record.status_persetujuan;
    editForm.catatan = record.catatan || '';
    isEditModalOpen.value = true;
};

const cancelEdit = () => {
    isEditModalOpen.value = false;
    editingId.value = null;
    editForm.reset();
};

const saveUpdate = () => {
    editForm.put(route('manager.absensi.record.update', editingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
            editingId.value = null;
        }
    });
};

const approveAttendance = (id) => {
    if (confirm('Approve this attendance?')) {
        useForm({}).post(route('manager.absensi.approve', id), {
            preserveScroll: true
        });
    }
};

const rejectAttendance = (id) => {
    if (confirm('Reject this attendance?')) {
        useForm({}).post(route('manager.absensi.reject', id), {
            preserveScroll: true
        });
    }
};

const approveAll = () => {
    if (confirm('Approve all pending submissions for this session?')) {
        useForm({}).post(route('manager.absensi.approve-all', props.session.id), {
            preserveScroll: true
        });
    }
};

// Pagination Logic
const recordsPerPage = 10;
const currentRecordsPage = ref(1);
const currentMissingPage = ref(1);

const paginatedRecords = computed(() => {
    const start = (currentRecordsPage.value - 1) * recordsPerPage;
    return props.session.absensi.slice(start, start + recordsPerPage);
});

const totalRecordsPages = computed(() => Math.ceil(props.session.absensi.length / recordsPerPage));

const paginatedMissing = computed(() => {
    const start = (currentMissingPage.value - 1) * recordsPerPage;
    return props.availableEmployees.slice(start, start + recordsPerPage);
});

const totalMissingPages = computed(() => Math.ceil(props.availableEmployees.length / recordsPerPage));

// Quick Manual Add
const isQuickAdd = ref(false);
const quickManualAdd = (emp) => {
    addForm.user_id = emp.id;
    addForm.jam_absen = props.session.jam_mulai.substring(0, 5);
    isQuickAdd.value = true;
    isAddModalOpen.value = true;
};

const openAddModal = () => {
    addForm.reset();
    addForm.jam_absen = props.session.jam_mulai.substring(0, 5);
    addForm.status = 'hadir';
    addForm.status_persetujuan = 'disetujui';
    isQuickAdd.value = false;
    isAddModalOpen.value = true;
};

</script>

<template>
    <SupervisorLayout title="Session Details">
        <template #header>
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-6">
                    <Link :href="route('manager.absensi.index')" class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 rounded-2xl hover:text-indigo-600 transition-all shadow-sm group">
                        <ChevronLeft class="w-5 h-5 group-hover:-translate-x-1 transition-transform" />
                    </Link>
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-2 uppercase">
                            SESSION <span class="text-indigo-600">OVERVIEW</span>
                        </h2>
                        <span class="text-[10px] font-black uppercase text-white bg-slate-900 dark:bg-indigo-600 px-3 py-1 rounded-full tracking-[0.2em] shadow-lg shadow-indigo-500/20">
                            {{ new Date(session.tanggal).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <button v-if="session.absensi.some(r => r.status_persetujuan === 'menunggu')" 
                        @click="approveAll" 
                        class="bg-emerald-600 text-white px-6 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-500/20 flex items-center gap-3"
                    >
                        <ShieldCheck class="w-5 h-5" />
                        Approve All
                    </button>
                    <button @click="openAddModal" class="bg-indigo-600 text-white px-6 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-500/20 flex items-center gap-3">
                        <UserPlus class="w-5 h-5" />
                        Manual Record
                    </button>
                    <div class="bg-indigo-50 dark:bg-slate-900 p-4 rounded-xl border border-indigo-100 dark:border-slate-800 flex items-center gap-4">
                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm">
                            <Clock class="w-5 h-5 text-indigo-500" />
                        </div>
                        <div>
                             <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">SUBMISSIONS</p>
                             <h4 class="text-xl font-black text-slate-900 dark:text-white leading-none">{{ session.absensi.length }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Manual Add Modal -->
        <DialogModal :show="isAddModalOpen" @close="isAddModalOpen = false">
            <template #title>
                <div class="p-2">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Manual <span class="text-indigo-600">Attendance</span></h3>
                     <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Directly record an entry for this session.</p>
                </div>
            </template>

            <template #content>
                <div v-if="availableEmployees.length === 0" class="p-8 bg-slate-50 dark:bg-slate-800 rounded-3xl text-center border border-dashed border-slate-200">
                     <Info class="w-8 h-8 text-slate-300 mx-auto mb-3" />
                     <p class="text-sm font-black text-slate-400 tracking-tight uppercase">All employees have already clocked in.</p>
                </div>

                <form v-else @submit.prevent="submitManualAttendance" id="manualAddForm" class="p-2 space-y-6">
                    <div v-if="!isQuickAdd">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Employee Target</label>
                        <select v-model="addForm.user_id" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 transition-all">
                            <option value="">Select Employee</option>
                            <option v-for="emp in availableEmployees" :key="emp.id" :value="emp.id">
                                {{ emp.name }} ({{ emp.kode_karyawan }})
                            </option>
                        </select>
                    </div>
                    <div v-else class="p-4 bg-indigo-50/50 dark:bg-slate-800/50 rounded-2xl border border-indigo-100 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                             <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center font-black text-indigo-600 text-xs border border-indigo-100">
                                 {{ availableEmployees.find(e => e.id === addForm.user_id)?.name.charAt(0) }}
                             </div>
                             <div>
                                 <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Locked Profile</p>
                                 <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase">{{ availableEmployees.find(e => e.id === addForm.user_id)?.name }}</h4>
                             </div>
                        </div>
                        <button type="button" @click="isQuickAdd = false" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">Change</button>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Clock Time</label>
                            <input v-model="addForm.jam_absen" type="time" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100 transition-all" />
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Attendance Status</label>
                            <select v-model="addForm.status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 transition-all">
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Reason / Note</label>
                        <textarea v-model="addForm.catatan" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-bold text-slate-600 focus:ring-4 focus:ring-indigo-100 min-h-[100px]"></textarea>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button @click="isAddModalOpen = false" class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-2xl transition-all">Cancel</button>
                    <button form="manualAddForm" type="submit" :disabled="addForm.processing || availableEmployees.length === 0" class="flex-1 bg-indigo-600 text-white p-5 rounded-3xl font-black text-xs uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 disabled:opacity-50">Add Record</button>
                </div>
            </template>
        </DialogModal>

        <div class="grid grid-cols-1 gap-6">
            <div v-if="session.absensi.length > 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                        <CheckCircle2 class="w-5 h-5 text-emerald-500" />
                        Attendance Log
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Employee</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Scan Time</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Approval Status</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="record in paginatedRecords" :key="record.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-all">
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-slate-800 flex items-center justify-center font-black text-indigo-600 border border-indigo-100 dark:border-slate-700 text-xs shadow-sm group-hover:scale-110 transition-transform">
                                            {{ record.user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ record.user.name }}</h4>
                                            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">{{ record.user.kode_karyawan }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-2">
                                        <Clock class="w-3.5 h-3.5 text-slate-400" />
                                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tighter">{{ record.jam_absen }}</span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <span :class="['px-3 py-1.5 rounded-full text-[9px] uppercase font-black tracking-widest border', 
                                        record.status === 'hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                                        record.status === 'terlambat' ? 'bg-amber-50 text-amber-600 border-amber-100' :
                                        'bg-rose-50 text-rose-600 border-rose-100']">
                                        {{ record.status }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <div :class="['text-[9px] font-black uppercase tracking-widest flex items-center gap-2', record.status_persetujuan === 'disetujui' ? 'text-emerald-500' : 'text-amber-500']">
                                        <div :class="['w-1.5 h-1.5 rounded-full', record.status_persetujuan === 'disetujui' ? 'bg-emerald-500' : 'bg-amber-500']"></div>
                                        {{ record.status_persetujuan }}
                                    </div>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        <template v-if="record.status_persetujuan === 'menunggu'">
                                            <button @click="approveAttendance(record.id)" class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                <ShieldCheck class="w-4 h-4" />
                                            </button>
                                            <button @click="rejectAttendance(record.id)" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-100 transition-all shadow-sm">
                                                <XCircle class="w-4 h-4" />
                                            </button>
                                        </template>
                                        <button @click="startEdit(record)" class="p-2 bg-slate-50 dark:bg-slate-800 text-slate-400 rounded-lg hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteRecord(record.id)" class="p-2 bg-rose-50/50 text-rose-300 rounded-lg hover:bg-rose-600 hover:text-white transition-all">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination for Records -->
                <div v-if="totalRecordsPages > 1" class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Viewing Page {{ currentRecordsPage }} of {{ totalRecordsPages }} ({{ session.absensi.length }} total logs)</p>
                     <div class="flex items-center gap-2">
                        <button :disabled="currentRecordsPage === 1" @click="currentRecordsPage--" class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 disabled:opacity-30 hover:bg-slate-50 transition-all"><ChevronLeft class="w-4 h-4" /></button>
                        <span class="text-xs font-black text-slate-900 dark:text-white mx-3">{{ currentRecordsPage }}</span>
                        <button :disabled="currentRecordsPage === totalRecordsPages" @click="currentRecordsPage++" class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 disabled:opacity-30 hover:bg-slate-50 transition-all rotate-180"><ChevronLeft class="w-4 h-4" /></button>
                     </div>
                </div>
            </div>

            <div v-else class="bg-white dark:bg-slate-900 p-16 rounded-[3.5rem] text-center border-2 border-dashed border-slate-200 dark:border-slate-800">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <User class="w-10 h-10 text-slate-300" />
                </div>
                <h3 class="text-xl font-black text-slate-400 uppercase tracking-widest">No entries found for this session.</h3>
                <p class="text-slate-400 text-xs font-bold mt-2 italic">Waiting for employee actions...</p>
            </div>

            <div v-if="availableEmployees.length > 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm mt-8">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                        <XCircle class="w-5 h-5 text-rose-500" />
                        Not Yet Recorded ({{ availableEmployees.length }})
                    </h3>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Incomplete division submission</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-8">
                    <div v-for="emp in paginatedMissing" :key="emp.id" class="flex items-center justify-between p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-indigo-100 transition-all group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 flex items-center justify-center font-black text-slate-400 border border-slate-100 dark:border-slate-700 text-xs shadow-sm group-hover:bg-rose-50 group-hover:text-rose-500 transition-all">
                                {{ emp.name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ emp.name }}</h4>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">{{ emp.kode_karyawan }}</p>
                            </div>
                        </div>
                        <button @click="quickManualAdd(emp)" class="p-3 bg-white dark:bg-slate-900 text-slate-400 rounded-xl hover:text-indigo-600 shadow-sm transition-all">
                            <Plus class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                <!-- Pagination for Missing -->
                <div v-if="totalMissingPages > 1" class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Viewing Page {{ currentMissingPage }} of {{ totalMissingPages }} ({{ availableEmployees.length }} missing)</p>
                     <div class="flex items-center gap-2">
                        <button :disabled="currentMissingPage === 1" @click="currentMissingPage--" class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 disabled:opacity-30 hover:bg-slate-50 transition-all"><ChevronLeft class="w-4 h-4" /></button>
                        <span class="text-xs font-black text-slate-900 dark:text-white mx-3">{{ currentMissingPage }}</span>
                        <button :disabled="currentMissingPage === totalMissingPages" @click="currentMissingPage++" class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 disabled:opacity-30 hover:bg-slate-50 transition-all rotate-180"><ChevronLeft class="w-4 h-4" /></button>
                     </div>
                </div>
            </div>
        </div>

        <!-- Edit Record Modal -->
        <DialogModal :show="isEditModalOpen" @close="cancelEdit">
            <template #title>
                <div class="p-2 text-indigo-600 font-black uppercase tracking-tight">Edit <span class="text-slate-900 dark:text-white">Attendance Record</span></div>
            </template>

            <template #content>
                 <form @submit.prevent="saveUpdate" id="editRecordForm" class="p-2 space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Clock Time</label>
                        <input v-model="editForm.jam_absen" type="time" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100 transition-all" />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Attendance Status</label>
                            <select v-model="editForm.status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 transition-all">
                                <option value="menunggu">Menunggu</option>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="cuti">Cuti</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Approval Status</label>
                            <select v-model="editForm.status_persetujuan" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 transition-all">
                                <option value="menunggu">Menunggu</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 block ml-2">Reason / Note</label>
                        <textarea v-model="editForm.catatan" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-bold text-slate-600 focus:ring-4 focus:ring-indigo-100 min-h-[100px]"></textarea>
                    </div>
                 </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button @click="cancelEdit" class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-2xl transition-all">Cancel</button>
                    <button form="editRecordForm" type="submit" :disabled="editForm.processing" class="flex-1 bg-indigo-600 text-white p-5 rounded-3xl font-black text-xs uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 disabled:opacity-50">Apply Changes</button>
                </div>
            </template>
        </DialogModal>
    </SupervisorLayout>
</template>
