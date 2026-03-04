<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { 
    ChevronLeft, CheckCircle2, XCircle, 
    Clock, User, MessageSquare, ShieldCheck, HelpCircle,
    Pencil, Save, X, Trash2, LogIn, LogOut, Coffee, Plus,
    UserPlus, Info, AlertCircle, ChevronDown, ListTodo,
    Flag, Users, ArrowRight, PieChart, BarChart
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({
    session: Object,
    availableEmployees: Array,
    divisions: Array,
});

const isModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedTask = ref(null);
const activeFilter = ref('all');

const filteredTasks = computed(() => {
    if (!props.session?.tugas) return [];
    if (activeFilter.value === 'all') return props.session.tugas;
    
    return props.session.tugas.filter(task => {
        const taskDivId = task.divisi_id || task.user?.divisi_id;
        return taskDivId == activeFilter.value;
    });
});

const taskForm = useForm({
    tipe_penugasan: 'individu',
    ditugaskan_ke: '',
    divisi_id: props.session?.divisi_id,
    judul: '',
    deskripsi: '',
    prioritas: 'sedang',
    deadline_type: 'today', // none, today, custom
    deadline_time: '17:00',
    deadline_custom: '',
    deadline: '', // Calculated
});

const editTaskForm = useForm({
    judul: '',
    deskripsi: '',
    prioritas: 'sedang',
    deadline: '',
    status: '',
    status_persetujuan: '',
    progress: 0,
});

const openCreateModal = () => {
    taskForm.reset();
    // Ensure divisi_id is correctly set on open
    taskForm.divisi_id = props.session?.divisi_id; 
    isModalOpen.value = true;
};

const submitTask = () => {
    if (taskForm.deadline_type === 'today') {
        taskForm.deadline = `${props.session.tanggal} ${taskForm.deadline_time}:00`;
    } else if (taskForm.deadline_type === 'custom') {
        taskForm.deadline = taskForm.deadline_custom;
    } else {
        taskForm.deadline = null;
    }

    taskForm.post(route('manager.tugas.record.store', props.session.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            taskForm.reset();
        }
    });
};

const openEditModal = (task) => {
    selectedTask.value = task;
    editTaskForm.judul = task.judul;
    editTaskForm.deskripsi = task.deskripsi;
    editTaskForm.prioritas = task.prioritas;
    editTaskForm.deadline = task.deadline ? new Date(task.deadline).toISOString().substring(0, 16) : '';
    editTaskForm.status = task.status;
    editTaskForm.status_persetujuan = task.status_persetujuan;
    editTaskForm.progress = task.progress;
    isEditModalOpen.value = true;
};

const submitUpdateTask = () => {
    editTaskForm.put(route('manager.tugas.record.update', selectedTask.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
        }
    });
};

const deleteTask = (id) => {
    if (confirm('Hapus tugas ini?')) {
        useForm({}).delete(route('manager.tugas.record.destroy', id), {
            preserveScroll: true
        });
    }
};

const approveTask = (id) => {
    useForm({}).post(route('manager.tugas.approve', id), {
        preserveScroll: true
    });
};

const rejectTask = (id) => {
    useForm({}).post(route('manager.tugas.reject', id), {
        preserveScroll: true
    });
};

const getPriorityClass = (priority) => {
    switch (priority) {
        case 'tinggi': return 'bg-rose-100 text-rose-700 border-rose-200';
        case 'sedang': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'rendah': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'selesai': return 'bg-emerald-500 text-white';
        case 'dikerjakan': return 'bg-indigo-500 text-white';
        case 'ditolak': return 'bg-rose-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};

const contributorSummaries = computed(() => {
    if (!selectedTask.value || !selectedTask.value.logs) return [];
    
    const summaries = {};
    const colors = [
        'bg-indigo-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500', 
        'bg-sky-500', 'bg-violet-500', 'bg-fuchsia-500', 'bg-teal-500'
    ];

    selectedTask.value.logs.forEach((log, index) => {
        const userId = log.user_id;
        const userName = log.user?.name || 'Unknown';
        // Calculate incremental progress contributed by this user in this log
        const contribution = Math.max(0, log.progress_sesudah - log.progress_sebelum);
        
        if (!summaries[userId]) {
            summaries[userId] = {
                name: userName,
                totalContribution: 0,
                color: colors[Object.keys(summaries).length % colors.length]
            };
        }
        summaries[userId].totalContribution += contribution;
    });
    
    return Object.values(summaries).filter(s => s.totalContribution > 0);
});

const formatTime = (timeStr) => {
    if (!timeStr) return 'N/A';
    const d = new Date(timeStr);
    return isNaN(d.getTime()) ? 'N/A' : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? '' : d.toLocaleDateString([], { day: '2-digit', month: 'short' });
};

const formatFullDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? '' : d.toDateString();
};

</script>

<style scoped>
.working-pulse {
    animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse-ring {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .7; transform: scale(1.02); }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>

<template>
    <ManagerLayout :title="`Task Session: ${session?.tanggal || ''}`">
        <template #header>
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-6">
                    <Link :href="route('manager.tugas.index')" class="w-12 h-12 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl flex items-center justify-center hover:bg-slate-50 transition-all shadow-sm group">
                        <ChevronLeft class="w-5 h-5 text-slate-400 group-hover:text-indigo-600 transition-colors" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">Day Tasks <span class="text-indigo-600">Assignment</span></h2>
                        </div>
                        <p v-if="session?.divisi" class="text-slate-500 font-bold uppercase text-[9px] tracking-[0.2em] mt-2">Managing tasks for <span class="text-indigo-600">{{ session.divisi?.nama }}</span> on {{ formatFullDate(session.tanggal) }}</p>
                    </div>
                </div>
                <button 
                  @click="openCreateModal"
                  class="bg-slate-900 dark:bg-indigo-600 text-white px-8 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl active:scale-95 flex items-center gap-3"
                >
                    <Plus class="w-5 h-5" />
                    New Assignment
                </button>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6">
            <!-- Summary Stats -->
            <div class="grid grid-cols-4 gap-4 mb-4">
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Assigned</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ session?.tugas?.length || 0 }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Completed</p>
                    <p class="text-2xl font-black text-emerald-600">{{ (session?.tugas || []).filter(t => t.status === 'selesai').length }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-1">On Progress</p>
                    <p class="text-2xl font-black text-indigo-600">{{ (session?.tugas || []).filter(t => t.status === 'dikerjakan').length }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
                    <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest mb-1">Pending Approval</p>
                    <p class="text-2xl font-black text-rose-600">{{ (session?.tugas || []).filter(t => t.status_persetujuan === 'menunggu' && t.status === 'selesai').length }}</p>
                </div>
            </div>

            <!-- Task Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest leading-none">Employee Assignments</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase mt-2 tracking-widest italic" v-if="activeFilter !== 'all'">Filtering by: {{ props.divisions.find(d => d.id == activeFilter)?.nama }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="relative group">
                             <select v-model="activeFilter" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl pl-4 pr-10 py-3 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 appearance-none cursor-pointer shadow-sm min-w-[200px] transition-all">
                                <option value="all">🌐 ALL DEPARTMENTS</option>
                                <option v-for="div in props.divisions" :key="div.id" :value="div.id">
                                    🏢 {{ div.nama }}
                                </option>
                             </select>
                             <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none group-hover:text-indigo-500 transition-colors" />
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Task Details</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Deadline</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Progress</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-bold uppercase text-[10px] tracking-widest">
                            <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-all font-bold uppercase text-[10px] tracking-widest">
                                <td class="p-6">
                                    <div class="flex items-center gap-3">
                                        <div v-if="task.tipe_penugasan === 'individu'" class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-black">
                                            {{ task.user?.name ? task.user.name.charAt(0) : '?' }}
                                        </div>
                                        <div v-else class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-black">
                                            <ListTodo class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-slate-900 dark:text-white">{{ task.tipe_penugasan === 'individu' ? (task.user?.name || 'Unknown') : (task.divisi?.nama || 'Division') }}</p>
                                            <p class="text-slate-400 text-[9px]">{{ task.tipe_penugasan === 'individu' ? 'INDIVIDUAL' : 'DEPARTMENT-WIDE' }}</p>
                                            
                                            <!-- Team Breakdown Indicator for Manager -->
                                            <div v-if="task.tipe_penugasan === 'divisi' && task.sub_tugas?.length > 0" class="mt-2 flex flex-wrap gap-1">
                                                <div v-for="sub in task.sub_tugas" :key="sub.id" 
                                                     class="group/sub relative p-1 bg-slate-50 dark:bg-slate-800 rounded-md border border-slate-100 dark:border-slate-700"
                                                     :title="`${sub.judul} - ${sub.assignee?.name || 'Everyone'}`"
                                                >
                                                    <div class="flex items-center gap-1.5 px-1">
                                                        <div :class="['w-1.5 h-1.5 rounded-full', sub.is_completed ? 'bg-emerald-500' : 'bg-slate-300']"></div>
                                                        <span class="text-[7px] font-black text-slate-500 dark:text-slate-400 uppercase truncate max-w-[50px]">{{ sub.assignee?.name.split(' ')[0] || 'Open' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <p class="text-slate-900 dark:text-white mb-1 line-clamp-1 pr-6">{{ task.judul }}</p>
                                    <div class="flex items-center gap-2">
                                        <span :class="['px-2 py-0.5 rounded-md text-[8px] font-black border', getPriorityClass(task.prioritas)]">
                                            {{ task.prioritas }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-6 text-slate-500">
                                    <div v-if="task.deadline" class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <Clock class="w-3 h-3" />
                                            {{ formatTime(task.deadline) }}
                                        </div>
                                        <div v-if="task.deadline && formatDate(task.deadline) !== formatDate(session?.tanggal)" class="text-[8px] font-black uppercase text-slate-400 pl-5">
                                            {{ formatDate(task.deadline) }}
                                        </div>
                                        <div v-if="task.is_overdue" class="mt-1">
                                            <span class="px-2 py-0.5 bg-rose-500 text-white text-[7px] font-black rounded-full animate-pulse">LATE / OVERDUE</span>
                                        </div>
                                    </div>
                                    <span v-else>N/A</span>
                                </td>
                                <td class="p-6">
                                    <div :class="['w-24', task.status === 'dikerjakan' ? 'working-pulse' : '']">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[9px] font-black">{{ task.progress }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" :style="`width: ${task.progress}%`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex flex-col gap-1.5">
                                        <span :class="['px-2 py-1 rounded-md text-[8px] font-black w-fit text-center', getStatusClass(task.status)]">
                                            {{ task.status }}
                                        </span>
                                        <span v-if="task.status_persetujuan === 'menunggu' && task.status === 'selesai'" class="flex items-center gap-1 text-[8px] text-rose-500 font-black">
                                            <AlertCircle class="w-3 h-3" /> APPROVAL REQ
                                        </span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <div v-if="task.status_persetujuan === 'menunggu' && task.status === 'selesai'" class="flex gap-1 pr-2 border-r border-slate-100 dark:border-slate-800 mr-2">
                                            <button @click="approveTask(task.id)" class="p-2 bg-emerald-100 text-emerald-600 rounded-lg hover:bg-emerald-200" title="Approve">
                                                <CheckCircle2 class="w-4 h-4" />
                                            </button>
                                            <button @click="rejectTask(task.id)" class="p-2 bg-rose-100 text-rose-600 rounded-lg hover:bg-rose-200" title="Reject">
                                                <XCircle class="w-4 h-4" />
                                            </button>
                                        </div>
                                        <Link 
                                            :href="route('manager.tugas.detail', task.id)"
                                            class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 rounded-lg transition-all border border-indigo-100 dark:border-indigo-900/20"
                                            title="View Detailed Analytics"
                                        >
                                            <BarChart class="w-4 h-4" />
                                        </Link>
                                        <button @click="openEditModal(task)" class="p-2 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-all">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteTask(task.id)" class="p-2 text-rose-400 hover:bg-rose-50 rounded-lg">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="session.tugas.length === 0">
                                <td colspan="6" class="p-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                            <ListTodo class="w-8 h-8 text-slate-300" />
                                        </div>
                                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">No tasks assigned yet.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <DialogModal :show="isModalOpen" @close="isModalOpen = false" maxWidth="xl">
            <template #title>
                <div class="p-2">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Assign <span class="text-indigo-600">New Task</span></h3>
                     <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Delegating responsibilities to your team members.</p>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="submitTask" id="createTaskForm" class="p-2 space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-1">Assignment Scope</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800/50 rounded-2xl mb-4 border border-slate-200/50 dark:border-slate-700/50">
                            <button type="button" @click="taskForm.tipe_penugasan = 'individu'" :class="['px-3 py-3 rounded-xl text-[9px] font-black uppercase transition-all', taskForm.tipe_penugasan === 'individu' ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors']">Personnel</button>
                            <button type="button" @click="taskForm.tipe_penugasan = 'divisi'" :class="['px-3 py-3 rounded-xl text-[9px] font-black uppercase transition-all', taskForm.tipe_penugasan === 'divisi' ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors']">Division</button>
                        </div>

                        <div v-if="taskForm.tipe_penugasan === 'individu'" class="animate-in fade-in slide-in-from-top-2 duration-200">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Assign To Personnel</label>
                            <select v-model="taskForm.ditugaskan_ke" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100">
                                <option value="">SELECT EMPLOYEE</option>
                                <option v-for="emp in availableEmployees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }}
                                </option>
                            </select>
                        </div>
                        <div v-else class="animate-in fade-in slide-in-from-top-2 duration-200">
                             <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block ml-1">Target Department</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <div class="w-8 h-8 bg-emerald-600/10 text-emerald-600 rounded-lg flex items-center justify-center">
                                            <Users class="w-4 h-4" />
                                        </div>
                                    </div>
                                    <select v-model="taskForm.divisi_id" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl pl-16 pr-10 py-5 text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/20 shadow-sm appearance-none cursor-pointer">
                                        <option value="" disabled>-- Select Target Department --</option>
                                        <option v-for="div in $page.props.global_divisions" :key="div.id" :value="div.id">{{ div.nama }}</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                                        <ChevronDown class="w-4 h-4" />
                                    </div>
                                </div>
                             </div>
                             <p class="mt-4 text-[9px] text-slate-400 font-bold italic uppercase px-1 flex items-center gap-2">
                                <Info class="w-3 h-3 text-indigo-500" />
                                * Task will be shared across the entire selected team workspace.
                             </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Task Title</label>
                        <input v-model="taskForm.judul" type="text" placeholder="e.g. Weekly Report Compilation" required class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Description</label>
                        <textarea v-model="taskForm.deskripsi" rows="3" placeholder="Provide clear instructions..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-100"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Priority Level</label>
                            <div class="flex gap-2">
                                <button type="button" @click="taskForm.prioritas = 'rendah'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase', taskForm.prioritas === 'rendah' ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-slate-50 text-slate-400 border-transparent']">Low</button>
                                <button type="button" @click="taskForm.prioritas = 'sedang'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase', taskForm.prioritas === 'sedang' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-slate-50 text-slate-400 border-transparent']">Mid</button>
                                <button type="button" @click="taskForm.prioritas = 'tinggi'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase', taskForm.prioritas === 'tinggi' ? 'bg-rose-600 border-rose-600 text-white' : 'bg-slate-50 text-slate-400 border-transparent']">High</button>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-1">Task Deadline Configuration</label>
                            <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100/50 dark:bg-slate-800 rounded-2xl">
                                <button type="button" @click="taskForm.deadline_type = 'none'" :class="['px-3 py-3 rounded-xl text-[9px] font-black uppercase transition-all', taskForm.deadline_type === 'none' ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-400 opacity-60']">No Limit</button>
                                <button type="button" @click="taskForm.deadline_type = 'today'" :class="['px-3 py-3 rounded-xl text-[9px] font-black uppercase transition-all', taskForm.deadline_type === 'today' ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-400 opacity-60']">Time Only</button>
                                <button type="button" @click="taskForm.deadline_type = 'custom'" :class="['px-3 py-3 rounded-xl text-[9px] font-black uppercase transition-all', taskForm.deadline_type === 'custom' ? 'bg-white dark:bg-slate-700 text-indigo-600 shadow-sm' : 'text-slate-400 opacity-60']">Custom Day</button>
                            </div>

                            <div v-if="taskForm.deadline_type === 'today'" class="animate-in fade-in slide-in-from-top-2 duration-200">
                                 <div class="flex items-center gap-4 bg-indigo-50/50 dark:bg-indigo-900/10 p-4 rounded-2xl border border-indigo-100/20">
                                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md">
                                        <Clock class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-1 leading-none">Execute before (Time)</p>
                                        <input v-model="taskForm.deadline_time" type="time" class="w-full bg-transparent border-none p-0 text-lg font-black text-indigo-900 dark:text-indigo-200 focus:ring-0" />
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Auto Date</p>
                                        <p class="text-[10px] font-black text-slate-900 dark:text-white">{{ new Date(session.tanggal).toLocaleDateString([], { day: '2-digit', month: 'short' }) }}</p>
                                    </div>
                                 </div>
                            </div>

                            <div v-if="taskForm.deadline_type === 'custom'" class="animate-in fade-in slide-in-from-top-2 duration-200">
                                <input v-model="taskForm.deadline_custom" type="datetime-local" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-5 text-sm font-black focus:ring-4 focus:ring-indigo-100" />
                            </div>
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button @click="isModalOpen = false" class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-xl transition-all">Cancel</button>
                    <button form="createTaskForm" type="submit" :disabled="taskForm.processing" class="flex-1 bg-indigo-600 text-white p-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg active:scale-95 disabled:opacity-50">Assign Task</button>
                </div>
            </template>
        </DialogModal>

        <!-- Edit/Details Modal -->
        <DialogModal :show="isEditModalOpen" @close="isEditModalOpen = false" maxWidth="xl">
            <template #title>
                <div class="p-2">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Modify <span class="text-indigo-600">Assignment</span></h3>
                     <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Update task properties or status manual override.</p>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="submitUpdateTask" id="editTaskForm" class="p-2 space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Title</label>
                        <input v-model="editTaskForm.judul" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Instruction</label>
                        <textarea v-model="editTaskForm.deskripsi" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-bold focus:ring-4 focus:ring-indigo-100"></textarea>
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Priority Level</label>
                        <div class="flex gap-2">
                            <button type="button" @click="editTaskForm.prioritas = 'rendah'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase transition-all', editTaskForm.prioritas === 'rendah' ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all']">Low</button>
                            <button type="button" @click="editTaskForm.prioritas = 'sedang'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase transition-all', editTaskForm.prioritas === 'sedang' ? 'bg-amber-600 border-amber-600 text-white shadow-lg shadow-amber-500/20' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all']">Mid</button>
                            <button type="button" @click="editTaskForm.prioritas = 'tinggi'" :class="['flex-1 p-3 rounded-xl border text-[9px] font-black uppercase transition-all', editTaskForm.prioritas === 'tinggi' ? 'bg-rose-600 border-rose-600 text-white shadow-lg shadow-rose-500/20' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 border-transparent hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-all']">High</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Manual Progress Override (%)</label>
                            <input v-model="editTaskForm.progress" type="number" min="0" max="100" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Deadline Update</label>
                            <input v-model="editTaskForm.deadline" type="datetime-local" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black focus:ring-4 focus:ring-indigo-100" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Workflow Status</label>
                            <div class="relative">
                                <select v-model="editTaskForm.status" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 appearance-none">
                                    <option value="menunggu">WAITING</option>
                                    <option value="dikerjakan">ON WORK</option>
                                    <option value="selesai">DONE</option>
                                    <option value="ditolak">CANCELLED</option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Manager Approval</label>
                            <div class="relative">
                                <select v-model="editTaskForm.status_persetujuan" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl p-4 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 appearance-none">
                                    <option value="menunggu">PENDING</option>
                                    <option value="disetujui">APPROVED</option>
                                    <option value="ditolak">REJECTED</option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Contribution Breakdown -->
                    <div v-if="selectedTask && selectedTask.logs && selectedTask.logs.length > 0 && contributorSummaries.length > 0" class="mt-8">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-4 flex items-center gap-2">
                            <PieChart class="w-3.5 h-3.5 text-indigo-500" /> Participation Analytics
                        </label>
                        
                        <div class="bg-indigo-50/20 dark:bg-indigo-900/10 border border-indigo-100/30 dark:border-indigo-800/20 rounded-[32px] p-6">
                            <!-- Stacked Bar Visualization -->
                            <div class="h-3.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full flex overflow-hidden mb-6 ring-4 ring-white dark:ring-slate-900/50 shadow-inner">
                                <div v-for="(contributor, idx) in contributorSummaries" :key="idx"
                                     :class="[contributor.color, 'h-full transition-all duration-1000 ease-out']"
                                     :style="{ width: (contributor.totalContribution / Math.max(1, selectedTask.progress)) * 100 + '%' }"
                                ></div>
                            </div>

                            <!-- Legend -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                                <div v-for="(contributor, idx) in contributorSummaries" :key="idx" class="flex items-center justify-between p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-colors group/contributor">
                                    <div class="flex items-center gap-3">
                                        <div :class="['w-4 h-4 rounded-lg shadow-sm group-hover/contributor:scale-110 transition-transform', contributor.color]"></div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-tight truncate max-w-[120px]">{{ contributor.name }}</span>
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">Team Member</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[12px] font-black text-indigo-600 dark:text-indigo-400 leading-none mb-0.5">{{ contributor.totalContribution }}<span class="text-[9px]">%</span></p>
                                        <p class="text-[7px] font-black text-slate-400 uppercase tracking-tighter">Share of work</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Activity Logs -->
                    <div v-if="selectedTask && selectedTask.logs && selectedTask.logs.length > 0" class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-6">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-4">Work Progress History</label>
                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <div v-for="log in selectedTask.logs" :key="log.id" class="p-4 bg-slate-50 dark:bg-slate-800/10 rounded-2xl border border-slate-100 dark:border-slate-800 group/log">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-tight">{{ log.user.name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400">• {{ new Date(log.created_at).toLocaleString([], { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300 leading-relaxed">{{ log.keterangan || 'No description provided.' }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 text-right">Progress</span>
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-[9px] font-bold text-slate-400 line-through">{{ log.progress_sebelum }}%</span>
                                            <ArrowRight class="w-2.5 h-2.5 text-slate-300" />
                                            <span class="text-xs font-black text-emerald-600">{{ log.progress_sesudah }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button @click="isEditModalOpen = false" class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-xl transition-all">Cancel</button>
                    <button form="editTaskForm" type="submit" :disabled="editTaskForm.processing" class="flex-1 bg-indigo-600 text-white p-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg active:scale-95 disabled:opacity-50">Save Changes</button>
                </div>
            </template>
        </DialogModal>
    </ManagerLayout>
</template>
