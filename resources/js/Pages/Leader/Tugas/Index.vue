<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { 
    Users, Briefcase, Plus, XCircle, 
    Send, Info, ListTodo, CheckCircle2,
    Search, Clock, ChevronRight, User as UserIcon,
    AlertCircle, LayoutGrid
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import DialogModal from '@/Components/DialogModal.vue';

const props = defineProps({
    tasks: Array,
    divisionEmployees: Array
});

const isBreakdownModalOpen = ref(false);
const selectedTask = ref(null);
const searchQuery = ref('');

const breakdownForm = useForm({
    sub_tugas: [
        { judul: '', bobot: 0, assigned_to: null }
    ]
});

const totalWeight = computed(() => {
    return breakdownForm.sub_tugas.reduce((acc, item) => acc + (Number(item.bobot) || 0), 0);
});

const openBreakdown = (task) => {
    selectedTask.value = task;
    // Pre-fill if there are existing sub-tasks
    if (task.sub_tugas?.length > 0) {
        breakdownForm.sub_tugas = task.sub_tugas.map(s => ({
            judul: s.judul,
            bobot: s.bobot,
            assigned_to: s.assigned_to
        }));
    } else {
        breakdownForm.sub_tugas = [{ judul: '', bobot: 0, assigned_to: null }];
    }
    isBreakdownModalOpen.value = true;
};

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
        }
    });
};

const filteredTasks = computed(() => {
    if (!searchQuery.value) return props.tasks;
    const q = searchQuery.value.toLowerCase();
    return props.tasks.filter(t => t.judul.toLowerCase().includes(q));
});

const getPriorityStyle = (priority) => {
    switch (priority) {
        case 'tinggi': return 'bg-rose-500';
        case 'sedang': return 'bg-amber-500';
        case 'rendah': return 'bg-emerald-500';
        default: return 'bg-slate-400';
    }
};

</script>

<template>
    <UserLayout title="Koordinasi Tim">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200/50">
                            <Users class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">Division <span class="text-indigo-600">Hub</span></h2>
                            <p class="text-slate-400 font-bold uppercase text-[9px] tracking-[0.2em] mt-2 text-indigo-500/80">Breakdown tasks & Delegate to your team</p>
                        </div>
                    </div>
                </div>

                <div class="relative w-full md:w-64 group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Search division tasks..."
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl pl-11 pr-4 py-3 text-xs font-bold focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/20 transition-all shadow-sm"
                    />
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4">
                <div v-for="task in filteredTasks" :key="task.id" 
                     class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 transition-all hover:shadow-xl hover:shadow-indigo-500/5 group">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span :class="['w-2 h-2 rounded-full', getPriorityStyle(task.prioritas)]"></span>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ task.judul }}</h3>
                                    <div v-if="task.sub_tugas?.length > 0" class="flex gap-2">
                                        <span v-if="task.sub_tugas.some(s => s.status_usulan === 'pending')" class="px-2 py-0.5 bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 text-[8px] font-black rounded uppercase">Awaiting Manager Approval</span>
                                        <span v-else class="px-2 py-0.5 bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 text-[8px] font-black rounded uppercase">Breakdown Approved</span>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 font-bold leading-relaxed line-clamp-2 max-w-2xl">{{ task.deskripsi || 'No description provided.' }}</p>
                            </div>

                            <div class="flex items-center gap-6">
                                <div class="text-right hidden sm:block">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Progres</p>
                                    <p class="text-xl font-black text-slate-900 dark:text-white leading-none">{{ task.progress }}%</p>
                                </div>
                                <button 
                                    @click="openBreakdown(task)"
                                    class="p-4 bg-indigo-600 text-white rounded-2xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-indigo-500/20 flex items-center gap-3 group/btn"
                                >
                                    <ListTodo class="w-5 h-5 group-hover/btn:rotate-12 transition-transform" />
                                    <span class="text-xs font-black uppercase tracking-widest">{{ task.sub_tugas?.length > 0 ? 'Edit Breakdown' : 'Breakdown & Delegate' }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="task.sub_tugas?.length > 0" class="mt-6 pt-6 border-t border-slate-50 dark:border-slate-800 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <div v-for="sub in task.sub_tugas" :key="sub.id" 
                                 class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                <p class="text-[9px] font-black text-slate-900 dark:text-white truncate uppercase mb-1">{{ sub.judul }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-[8px] font-black text-indigo-500">{{ sub.bobot }}%</span>
                                    <span v-if="sub.assignee" class="text-[8px] font-bold text-slate-400">{{ sub.assignee.name.split(' ')[0] }}</span>
                                    <span v-else class="text-[8px] font-bold text-slate-300">Open</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="filteredTasks.length === 0" class="p-24 text-center bg-white dark:bg-slate-900 rounded-[40px] border border-slate-100 dark:border-slate-800 shadow-sm">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                            <Briefcase class="w-10 h-10 text-slate-200" />
                        </div>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-2">No Active Task</h4>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">There are no tasks assigned to your division yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Modal -->
        <DialogModal :show="isBreakdownModalOpen" @close="isBreakdownModalOpen = false" maxWidth="lg">
            <template #title>
                <div class="p-2 border-b border-slate-100 dark:border-slate-800 -mx-6 -mt-6 rounded-t-3xl mb-6 bg-indigo-600 px-6 py-6 text-white shadow-xl">
                     <h3 class="text-xl font-black uppercase tracking-tight">Structured <span class="text-indigo-200">Task Breakdown</span></h3>
                     <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mt-1 opacity-80">Delegate milestones to your team members.</p>
                </div>
            </template>

            <template #content>
                 <div class="space-y-6">
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100/50 rounded-2xl flex items-start gap-4">
                        <Info class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                        <p class="text-[9px] font-bold text-amber-800 dark:text-amber-200 leading-relaxed uppercase">
                            Define specific milestones and assign them to employees. Total points must equal 100 for sync.
                        </p>
                    </div>

                    <div class="space-y-4 max-h-[400px] overflow-y-auto px-1 custom-scrollbar">
                        <div v-for="(item, index) in breakdownForm.sub_tugas" :key="index" 
                             class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-[32px] border border-slate-100 dark:border-slate-800 animate-in slide-in-from-bottom-2 duration-300 shadow-sm">
                            <div class="flex items-center justify-between mb-4 px-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Milestone #{{ index + 1 }}</span>
                                <button v-if="breakdownForm.sub_tugas.length > 1" @click="removeMilestone(index)" class="text-rose-500 p-2 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-all">
                                    <XCircle class="w-5 h-5" />
                                </button>
                            </div>
                            <div class="grid grid-cols-12 gap-4 mb-4">
                                <div class="col-span-8">
                                    <input v-model="item.judul" type="text" placeholder="Title..." class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl p-4 text-xs font-black uppercase tracking-tight focus:ring-4 focus:ring-indigo-100 placeholder:text-slate-300" />
                                </div>
                                <div class="col-span-4 relative">
                                    <input v-model="item.bobot" type="number" placeholder="Wgt" class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl p-4 text-xs font-black text-indigo-600 focus:ring-4 focus:ring-indigo-100 pr-10" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400">%</span>
                                </div>
                            </div>
                            <!-- Delegate Selection -->
                            <div class="flex flex-col gap-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest pl-1">Assign Responsible Participant:</label>
                                <select v-model="item.assigned_to" class="w-full bg-white dark:bg-slate-900 border-none rounded-2xl text-[10px] font-black uppercase tracking-tight focus:ring-4 focus:ring-indigo-100 py-3 px-4 shadow-sm">
                                    <option :value="null">Everyone in Division</option>
                                    <option v-for="emp in divisionEmployees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button @click="addMilestone" class="w-full py-4 border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-[32px] text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all flex items-center justify-center gap-2 group">
                        <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Add Strategic Milestone</span>
                    </button>

                    <div class="p-6 bg-slate-900 dark:bg-black rounded-[32px] flex items-center justify-between shadow-2xl">
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Cumulative Allocation</p>
                            <p :class="['text-3xl font-black leading-none transition-all', totalWeight === 100 ? 'text-emerald-500' : 'text-rose-500']">{{ totalWeight }} / 100%</p>
                        </div>
                        <div v-if="totalWeight === 100" class="flex items-center gap-2 text-emerald-500 animate-pulse">
                            <CheckCircle2 class="w-6 h-6" />
                            <span class="text-[9px] font-black uppercase tracking-widest font-black">Composition Valid</span>
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full">
                    <button @click="isBreakdownModalOpen = false" class="px-8 py-5 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 dark:hover:bg-slate-800 rounded-2xl transition-all">Dismiss</button>
                    <button 
                        @click="submitProposeBreakdown"
                        :disabled="totalWeight !== 100 || breakdownForm.processing"
                        class="flex-1 bg-indigo-600 text-white p-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl active:scale-95 disabled:opacity-40 disabled:grayscale flex items-center justify-center gap-3"
                    >
                        <Send class="w-4 h-4" />
                        Submit & Notify Manager
                    </button>
                </div>
            </template>
        </DialogModal>
    </UserLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
}
</style>
