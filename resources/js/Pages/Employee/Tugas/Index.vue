<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { 
    ClipboardList, Clock, AlertCircle, 
    CheckCircle2, Send, Save, ArrowRight,
    Flag, MessageSquare, ListTodo, Info,
    CheckSquare, Loader2, Search, Filter,
    LayoutGrid, List, ChevronRight, User as UserIcon,
    Plus, XCircle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({
    tasks: Array,
    divisionEmployees: Array
});

const isModalOpen = ref(false);
const isBreakdownModalOpen = ref(false);
const selectedTask = ref(null);
const searchQuery = ref('');

const isLeader = computed(() => {
    return usePage().props.auth.user.roles.some(r => r.name === 'leader');
});

const breakdownForm = useForm({
    sub_tugas: [
        { judul: '', bobot: 0, assigned_to: null }
    ]
});

const totalWeight = computed(() => {
    return breakdownForm.sub_tugas.reduce((acc, item) => acc + (Number(item.bobot) || 0), 0);
});

const addMilestone = () => {
    breakdownForm.sub_tugas.push({ judul: '', bobot: 0, assigned_to: null });
};

const removeMilestone = (index) => {
    breakdownForm.sub_tugas.splice(index, 1);
};

const submitProposeBreakdown = () => {
    breakdownForm.post(route('manager.tugas.propose-sub', selectedTask.value.id), {
        onSuccess: () => {
            isBreakdownModalOpen.value = false;
            breakdownForm.reset();
            alert('Proposal sent to manager!');
        }
    });
};

const claimPoint = (subId) => {
    if (confirm('Claim this milestone as completed?')) {
        useForm({}).post(route('manager.tugas.sub.complete', subId), {
            onSuccess: () => {
                // Update the local task progress data if needed or just let Inertia reload
            }
        });
    }
};

const filteredTasks = computed(() => {
    if (!searchQuery.value) return props.tasks;
    const query = searchQuery.value.toLowerCase();
    return props.tasks.filter(task => 
        task.judul.toLowerCase().includes(query) || 
        (task.deskripsi && task.deskripsi.toLowerCase().includes(query))
    );
});

const form = useForm({
    progress: 0,
    keterangan: '',
});

const openUpdateModal = (task) => {
    selectedTask.value = task;
    form.progress = task.progress;
    form.keterangan = '';
    isModalOpen.value = true;
};

const showSuccessAlert = ref(false);

const submitUpdate = () => {
    form.post(route('employee.tugas.submit-progress', selectedTask.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            showSuccessAlert.value = true;
            setTimeout(() => {
                showSuccessAlert.value = false;
            }, 3000);
        }
    });
};

const getPriorityStyle = (priority) => {
    switch (priority) {
        case 'tinggi': return 'bg-rose-500';
        case 'sedang': return 'bg-amber-500';
        case 'rendah': return 'bg-emerald-500';
        default: return 'bg-slate-400';
    }
};

const getStatusBadge = (task) => {
    if (task.status === 'selesai') {
        if (task.status_persetujuan === 'disetujui') return { label: 'VERIFIED', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200' };
        if (task.status_persetujuan === 'ditolak') return { label: 'REJECTED', class: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200' };
        return { label: 'PENDING APRV', class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200' };
    }
    if (task.status === 'dikerjakan') return { label: 'WORKING', class: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border-indigo-200' };
    return { label: 'QUEUED', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border-slate-200' };
};

</script>

<template>
    <UserLayout title="Daftar Tugas Saya">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200/50">
                            <CheckSquare class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">Task <span class="text-indigo-600">Workspace</span></h2>
                            <p class="text-slate-400 font-bold uppercase text-[9px] tracking-[0.2em] mt-2">Personalize your productivity workflow</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="relative w-full md:w-64 group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Find your tasks..."
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl pl-11 pr-4 py-3 text-xs font-bold focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/20 transition-all shadow-sm"
                        />
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Summary Stats (Lighter & Cleaner) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                        <ListTodo class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ongoing</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ tasks.filter(t => t.status !== 'selesai').length }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600">
                        <CheckCircle2 class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Verified</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ tasks.filter(t => t.status_persetujuan === 'disetujui').length }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center text-amber-600">
                        <Clock class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ tasks.filter(t => t.status_persetujuan === 'menunggu').length }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                    <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/30 rounded-xl flex items-center justify-center text-rose-600">
                        <AlertCircle class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Late</p>
                        <p class="text-xl font-black text-slate-900 dark:text-white">{{ tasks.filter(t => t.is_overdue).length }}</p>
                    </div>
                </div>
            </div>

            <!-- Task Board (Modern List/Table Style) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest w-2"></th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Task Details</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timeline</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Assigned By</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Progress</th>
                                <th class="p-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            <tr v-for="task in filteredTasks" :key="task.id" 
                                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="p-0">
                                    <div :class="['w-1.5 h-16 rounded-r-full transition-all group-hover:w-2.5', getPriorityStyle(task.prioritas)]"></div>
                                </td>
                                <td class="p-6">
                                    <div class="flex flex-col gap-1.5 min-w-[300px]">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight line-clamp-1 truncate block">{{ task.judul }}</span>
                                            <span v-if="task.tipe_penugasan === 'divisi'" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[7px] font-black rounded-md uppercase">Team</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400 font-bold line-clamp-1">{{ task.deskripsi || 'No instructions.' }}</p>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div v-if="task.deadline" class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <Clock class="w-3 h-3 text-slate-400" />
                                            <span :class="['text-[10px] font-black uppercase tracking-tight', task.is_overdue ? 'text-rose-500' : 'text-slate-600 dark:text-slate-300']">
                                                {{ new Date(task.deadline).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                            </span>
                                        </div>
                                        <div class="text-[8px] font-black text-slate-400 mt-1 pl-4 uppercase">
                                            {{ new Date(task.deadline).toLocaleDateString([], { day: '2-digit', month: 'short' }) }}
                                        </div>
                                    </div>
                                    <span v-else class="text-[9px] font-black text-slate-300">NO LIMIT</span>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-500 font-black text-[10px]">
                                            {{ task.assigner.name.charAt(0) }}
                                        </div>
                                        <span class="text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-tight">{{ task.assigner.name.split(' ')[0] }}</span>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div :class="['w-32', task.status === 'dikerjakan' ? 'working-pulse' : '']">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span :class="['px-2 py-0.5 rounded text-[7px] font-black border', getStatusBadge(task).class]">
                                                {{ getStatusBadge(task).label }}
                                            </span>
                                            <span class="text-[9px] font-black text-slate-900 dark:text-white">{{ task.progress }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div :class="['h-full transition-all duration-700', getPriorityStyle(task.prioritas)]" :style="`width: ${task.progress}%`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <button 
                                        v-if="task.status !== 'selesai' || task.status_persetujuan === 'ditolak'"
                                        @click="openUpdateModal(task)"
                                        class="p-3 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl hover:scale-105 active:scale-95 transition-all shadow-md group/btn"
                                    >
                                        <ArrowRight class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" />
                                    </button>
                                    <div v-else class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-emerald-500">
                                        <CheckCircle2 class="w-5 h-5" />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredTasks.length === 0">
                                <td colspan="6" class="p-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                            <ListTodo class="w-8 h-8 text-slate-200" />
                                        </div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No tasks found in your worklist.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Update Progress Modal -->
        <DialogModal :show="isModalOpen" @close="isModalOpen = false" maxWidth="lg">
            <template #title>
                <div class="p-2 border-b border-slate-100 dark:border-slate-800 -mx-6 -mt-6 rounded-t-3xl mb-6 bg-slate-50/50 dark:bg-slate-800/30 px-6 py-5">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Report <span class="text-indigo-600">Work Progres</span></h3>
                     <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">Submit current status to your manager.</p>
                </div>
            </template>

            <template #content>
                <div v-if="selectedTask" class="space-y-6">
                    <div class="p-5 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100/30">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest leading-none">Task Subject:</p>
                            <span v-if="selectedTask.sub_tugas?.length > 0" class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[7px] font-black rounded uppercase">Structured Plan</span>
                        </div>
                        <p class="text-sm font-black text-indigo-900 dark:text-indigo-200 uppercase leading-snug">{{ selectedTask.judul }}</p>
                    </div>

                    <!-- Milestone Checklist (If Sub-tasks Exist) -->
                    <div v-if="selectedTask.sub_tugas?.length > 0" class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Work Roadmap</label>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ selectedTask.progress }}% Synced</span>
                        </div>
                        
                        <div class="space-y-2">
                            <div v-for="sub in selectedTask.sub_tugas" :key="sub.id" 
                                 :class="['p-4 rounded-2xl border transition-all flex items-center justify-between group', 
                                         sub.is_completed ? 'bg-emerald-50/20 border-emerald-100 dark:border-emerald-900/20' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-100 dark:border-slate-800']">
                                <div class="flex items-center gap-3 overflow-hidden">
                                     <div :class="['w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform', 
                                            sub.is_completed ? 'bg-emerald-500 text-white' : 'bg-white dark:bg-slate-900 text-slate-400 group-hover:scale-110']">
                                        <CheckCircle2 v-if="sub.is_completed" class="w-4 h-4" />
                                        <div v-else class="text-[9px] font-black">{{ sub.bobot }}</div>
                                    </div>
                                    <div class="truncate">
                                        <p :class="['text-[11px] font-black uppercase truncate', sub.is_completed ? 'text-emerald-700 dark:text-emerald-400 line-through' : 'text-slate-900 dark:text-white']">{{ sub.judul }}</p>
                                        <p v-if="sub.is_completed" class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">Fixed by {{ sub.user?.name.split(' ')[0] }}</p>
                                        <p v-else class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">Allocated Point: {{ sub.bobot }}%</p>
                                    </div>
                                </div>
                                <button 
                                    v-if="!sub.is_completed && sub.status_usulan === 'disetujui' && (!sub.assigned_to || sub.assigned_to === $page.props.auth.user.id)"
                                    @click="claimPoint(sub.id)"
                                    class="px-4 py-2 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 active:scale-95 flex-shrink-0 ml-4"
                                >Claim</button>
                                <span v-else-if="sub.status_usulan === 'pending'" class="text-[7px] font-black text-amber-500 uppercase tracking-[0.2em] border border-amber-200 px-2 py-1 rounded-lg">Wait Apprv</span>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 text-center">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed italic">Progression is locked to roadmap points.<br>Complete milestones to increase total percentage.</p>
                        </div>
                    </div>

                    <!-- Traditional Progress (If no sub-tasks) -->
                    <form v-else @submit.prevent="submitUpdate" id="updateProgressForm" class="space-y-8 px-2">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Completion Degree</label>
                                <span class="text-3xl font-black text-indigo-600">{{ form.progress }}%</span>
                            </div>
                            <div class="relative pt-6 pb-2">
                                <input 
                                    v-model="form.progress" 
                                    type="range" min="0" max="100" step="5"
                                    class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                />
                                <div class="flex justify-between mt-4 text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] opacity-60">
                                    <span>Initiation</span>
                                    <span>Milestone</span>
                                    <span>Finalization</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mb-2">Progress Details</label>
                            <textarea 
                                v-model="form.keterangan" 
                                rows="3" 
                                placeholder="What have you accomplished so far?"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-5 text-xs font-bold focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/40 transition-all placeholder:text-slate-300"
                            ></textarea>
                        </div>

                        <div v-if="form.progress >= 100" class="p-4 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100/50 rounded-2xl flex items-center gap-4 animate-in fade-in zoom-in duration-300">
                             <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg">
                                <CheckSquare class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">Mark as Done</p>
                                <p class="text-[10px] font-bold text-slate-500 leading-tight">By submitting 100% progress, this task will enter the verification stage with your manager.</p>
                            </div>
                        </div>
                    </form>

                    <!-- Breakdown Proposer for Team Leaders -->
                    <div v-if="isLeader && !selectedTask.sub_tugas?.length > 0" class="pt-6 border-t border-slate-100 dark:border-slate-800">
                        <button 
                            @click="isBreakdownModalOpen = true; isModalOpen = false"
                            class="w-full flex items-center justify-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 rounded-2xl border border-slate-100 dark:border-slate-800 hover:bg-white transition-all text-[10px] font-black uppercase tracking-widest"
                        >
                            <ListTodo class="w-4 h-4" /> Propose Work Breakdown & Delegate
                        </button>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full">
                    <button @click="isModalOpen = false" class="px-8 py-5 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all">Dismiss</button>
                    <button 
                        v-if="!selectedTask?.sub_tugas?.length > 0"
                        form="updateProgressForm" 
                        type="submit" 
                        :disabled="form.processing" 
                        class="flex-1 bg-indigo-600 text-white p-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3"
                    >
                        <template v-if="form.processing">
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Processing...
                        </template>
                        <template v-else>
                            <Send class="w-4 h-4" />
                            Submit Progress
                        </template>
                    </button>
                </div>
            </template>
        </DialogModal>

        <!-- Propose Breakdown Modal -->
        <DialogModal :show="isBreakdownModalOpen" @close="isBreakdownModalOpen = false" maxWidth="lg">
            <template #title>
                <div class="p-2 border-b border-slate-100 dark:border-slate-800 -mx-6 -mt-6 rounded-t-3xl mb-6 bg-indigo-600 px-6 py-6 text-white shadow-xl">
                     <h3 class="text-xl font-black uppercase tracking-tight">Structured <span class="text-indigo-200">Task Breakdown</span></h3>
                     <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mt-1 opacity-80">Define specific milestones & point weights.</p>
                </div>
            </template>

            <template #content>
                <div class="space-y-6">
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100/50 rounded-2xl flex items-start gap-4">
                        <Info class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                        <p class="text-[9px] font-bold text-amber-800 dark:text-amber-200 leading-relaxed uppercase">
                            Propose a granular roadmap to your manager. Total points MUST equal 100 to reach 100% progress. once approved, this breakdown becomes the fixed progression path.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in breakdownForm.sub_tugas" :key="index" 
                             class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800 animate-in slide-in-from-bottom-2 duration-300">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Milestone #{{ index + 1 }}</span>
                                <button v-if="breakdownForm.sub_tugas.length > 1" @click="removeMilestone(index)" class="text-rose-500 p-1 hover:bg-rose-50 rounded-lg transition-colors">
                                    <XCircle class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-12 gap-4 mb-3">
                                <div class="col-span-8">
                                    <input v-model="item.judul" type="text" placeholder="e.g. Database Architecture" class="w-full bg-white dark:bg-slate-900 border-none rounded-xl p-3 text-xs font-black uppercase tracking-tight focus:ring-4 focus:ring-indigo-100 placeholder:text-slate-300" />
                                </div>
                                <div class="col-span-4 relative">
                                    <input v-model="item.bobot" type="number" placeholder="Weight" class="w-full bg-white dark:bg-slate-900 border-none rounded-xl p-3 text-xs font-black text-indigo-600 focus:ring-4 focus:ring-indigo-100 pr-8" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">%</span>
                                </div>
                            </div>
                            <!-- Assign To Selection -->
                            <div class="flex items-center gap-3">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Assign To:</label>
                                <select v-model="item.assigned_to" class="flex-1 bg-white dark:bg-slate-900 border-none rounded-xl text-[10px] font-black uppercase tracking-tight focus:ring-4 focus:ring-indigo-100 py-2">
                                    <option :value="null">Everyone in Team</option>
                                    <option v-for="emp in divisionEmployees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button @click="addMilestone" class="w-full py-4 border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-[28px] text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center justify-center gap-2 group">
                        <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Add Strategic Milestone</span>
                    </button>

                    <div class="p-6 bg-slate-900 dark:bg-black rounded-[32px] flex items-center justify-between">
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Cumulative Allocation</p>
                            <p :class="['text-2xl font-black leading-none', totalWeight === 100 ? 'text-emerald-500' : 'text-rose-500']">{{ totalWeight }} / 100%</p>
                        </div>
                        <div v-if="totalWeight === 100" class="flex items-center gap-2 text-emerald-500 animate-pulse">
                            <CheckCircle2 class="w-5 h-5" />
                            <span class="text-[9px] font-black uppercase">Valid Composition</span>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full">
                    <button @click="isBreakdownModalOpen = false; isModalOpen = true" class="px-8 py-5 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-2xl transition-all">Back</button>
                    <button 
                        @click="submitProposeBreakdown"
                        :disabled="totalWeight !== 100 || breakdownForm.processing"
                        class="flex-1 bg-indigo-600 text-white p-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl active:scale-95 disabled:opacity-40 disabled:grayscale flex items-center justify-center gap-3"
                    >
                        <Send class="w-4 h-4" />
                        Submit for Manager Review
                    </button>
                </div>
            </template>
        </DialogModal>
        <!-- Success Celebration Overlay -->
        <div v-if="showSuccessAlert" class="fixed inset-0 z-[200] flex items-center justify-center pointer-events-none p-4">
            <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border border-indigo-100 dark:border-indigo-900/30 p-8 rounded-[40px] shadow-2xl flex flex-col items-center animate-in zoom-in fade-in duration-500 max-w-sm text-center">
                <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center mb-6 shadow-xl shadow-emerald-500/30 animate-bounce">
                    <CheckCircle2 class="w-10 h-10 text-white" />
                </div>
                <h4 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-2">Progress <span class="text-indigo-600">Updated!</span></h4>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Your workflow has been synchronized. Excellent work!</p>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.working-pulse {
    animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse-ring {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .7; transform: scale(1.05); }
}

input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 28px;
    width: 28px;
    border-radius: 12px;
    background: #4f46e5;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
    transition: all 0.2s;
    border: 4px solid white;
}

.dark input[type=range]::-webkit-slider-thumb {
    border-color: #0f172a;
}

input[type=range]:hover::-webkit-slider-thumb {
    transform: scale(1.15) translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.5);
}
</style>
