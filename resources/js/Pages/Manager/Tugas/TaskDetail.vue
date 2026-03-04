<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { 
    ChevronLeft, CheckCircle2, XCircle, 
    Clock, User, MessageSquare, ShieldCheck,
    Flag, Users, ArrowRight, PieChart,
    Calendar, AlertCircle, TrendingUp, BarChart3,
    CheckSquare, Activity
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    task: Object
});

const contributorSummaries = computed(() => {
    if (!props.task || !props.task.logs) return [];
    
    const summaries = {};
    const colors = [
        'bg-indigo-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500', 
        'bg-sky-500', 'bg-violet-500', 'bg-fuchsia-500', 'bg-teal-500'
    ];

    props.task.logs.forEach((log, index) => {
        const userId = log.user_id;
        const userName = log.user.name;
        const contribution = Math.max(0, log.progress_sesudah - log.progress_sebelum);
        
        if (!summaries[userId]) {
            summaries[userId] = {
                name: userName,
                totalContribution: 0,
                color: colors[Object.keys(summaries).length % colors.length],
                logsCount: 0
            };
        }
        summaries[userId].totalContribution += contribution;
        summaries[userId].logsCount += 1;
    });
    
    return Object.values(summaries).filter(s => s.totalContribution > 0);
});

const getPriorityClass = (priority) => {
    switch (priority) {
        case 'tinggi': return 'bg-rose-500 text-white';
        case 'sedang': return 'bg-amber-500 text-white';
        case 'rendah': return 'bg-emerald-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};

const getStatusBadge = (status, approval) => {
    if (status === 'selesai' && approval === 'menunggu') {
        return { label: 'WAITING APPROVAL', class: 'bg-indigo-500 text-white animate-pulse' };
    }
    if (approval === 'disetujui') {
        return { label: 'COMPLETED & VERIFIED', class: 'bg-emerald-500 text-white' };
    }
    if (status === 'dikerjakan') {
        return { label: 'ACTIVE WORK', class: 'bg-sky-500 text-white' };
    }
    if (status === 'ditolak' || approval === 'ditolak') {
        return { label: 'REJECTED', class: 'bg-rose-500 text-white' };
    }
    return { label: 'PENDING', class: 'bg-slate-400 text-white' };
};

const approve = () => {
    if (confirm('Approve this task?')) {
        useForm({}).post(route('manager.tugas.approve', props.task.id));
    }
};

const reject = () => {
    if (confirm('Reject this task?')) {
        useForm({}).post(route('manager.tugas.reject', props.task.id));
    }
};

const approvePlan = () => {
    if (confirm('Approve this work plan?')) {
        useForm({}).post(route('manager.tugas.approve-plan', props.task.id));
    }
};

</script>

<template>
    <ManagerLayout :title="`Analytics: ${task.judul}`">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <Link 
                        :href="route('manager.tugas.show', task.sesi_tugas_id)"
                        class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 shadow-sm border border-slate-200/60 dark:border-slate-800/60 transition-all hover:scale-110 active:scale-95"
                    >
                        <ChevronLeft class="w-6 h-6" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span :class="['px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest', getStatusBadge(task.status, task.status_persetujuan).class]">
                                {{ getStatusBadge(task.status, task.status_persetujuan).label }}
                            </span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ task.tipe_penugasan }} Resource</span>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none">{{ task.judul }}</h2>
                    </div>
                </div>

                <div v-if="task.status_persetujuan === 'menunggu'" class="flex items-center gap-3">
                    <button @click="reject" class="px-6 py-3 bg-white dark:bg-slate-900 text-rose-500 font-extrabold text-[11px] uppercase tracking-widest rounded-2xl border border-rose-100 dark:border-rose-900/30 hover:bg-rose-50 transition-all shadow-sm">Reject Task</button>
                    <button @click="approve" class="px-8 py-3 bg-indigo-600 text-white font-extrabold text-[11px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 active:scale-95 border border-indigo-500/50">Verify & Close</button>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Activity & Timeline -->
            <div class="col-span-12 lg:col-span-7 space-y-8">
                <!-- Overview Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 shadow-sm col-span-2 flex items-center justify-between group overflow-hidden relative">
                        <!-- Animated Background Glow -->
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/5 blur-[80px] group-hover:bg-indigo-500/10 transition-all duration-700"></div>
                        
                        <div class="relative z-10">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Real-time Completion</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-6xl font-black text-slate-900 dark:text-white leading-none tracking-tighter">{{ task.progress }}<span class="text-3xl text-indigo-600">%</span></span>
                            </div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-4 flex items-center gap-2">
                                <BarChart3 class="w-3 h-3 text-indigo-500" /> Current Milestone Degree
                            </p>
                        </div>

                        <!-- Circular Animated Gauge -->
                        <div class="relative w-32 h-32 flex items-center justify-center">
                            <svg class="w-full h-full -rotate-90">
                                <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-100 dark:text-slate-800" />
                                <circle cx="64" cy="64" r="58" stroke="currentColor" stroke-width="10" fill="transparent" 
                                    class="text-indigo-600"
                                    stroke-linecap="round"
                                    :style="{ 
                                        strokeDasharray: (3.14 * 58 * 2), 
                                        strokeDashoffset: (3.14 * 58 * 2) * (1 - task.progress / 100),
                                        transition: 'stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1)'
                                    }" 
                                />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center drop-shadow-xl">
                                <div class="w-20 h-20 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center shadow-inner border border-slate-100 dark:border-slate-800">
                                    <TrendingUp class="w-8 h-8 text-indigo-500 animate-pulse" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 p-8 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 shadow-sm flex flex-col justify-between group">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Priority</p>
                            <div class="flex items-center gap-3">
                                <div :class="['w-4 h-4 rounded-full ring-4 ring-white dark:ring-slate-950 shadow-lg', getPriorityClass(task.prioritas)]"></div>
                                <span class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">{{ task.prioritas }}</span>
                            </div>
                        </div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-6 opacity-60">Urgency Level</p>
                    </div>
                </div>

                <!-- Strategic Milestone Checklist -->
                <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                                <CheckSquare class="w-5 h-5 text-indigo-500" /> Milestone Roadmap
                            </h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tight">Structured workflow for this assignment</p>
                        </div>
                        <div v-if="task.sub_tugas?.some(s => s.status_usulan === 'pending')" class="flex items-center gap-3 animate-pulse">
                            <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest border border-amber-200 px-3 py-1 rounded-full bg-amber-50">Needs Approval</span>
                            <button @click="approvePlan" class="px-4 py-1.5 bg-indigo-600 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all">Approve Plan</button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div v-if="task.sub_tugas && task.sub_tugas.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="sub in task.sub_tugas" :key="sub.id" 
                                 :class="['p-6 rounded-[32px] border transition-all group flex items-start gap-4', 
                                         sub.is_completed ? 'bg-emerald-50/30 border-emerald-100 dark:border-emerald-900/30' : 'bg-slate-50/50 border-slate-100 dark:border-slate-800/50']">
                                <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-110', 
                                            sub.is_completed ? 'bg-emerald-500 text-white' : 'bg-white dark:bg-slate-800 text-slate-300']">
                                    <CheckCircle2 v-if="sub.is_completed" class="w-5 h-5" />
                                    <Clock v-else class="w-5 h-5" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <p :class="['text-xs font-black uppercase tracking-tight', sub.is_completed ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-900 dark:text-white']">
                                            {{ sub.judul }}
                                        </p>
                                        <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-lg">
                                            {{ sub.bobot }}%
                                        </span>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 rounded text-[7px] font-black text-slate-500">PIC</span>
                                            <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ sub.assignee?.name || 'Everyone' }}</span>
                                        </p>
                                        <p v-if="sub.is_completed" class="text-[8px] font-bold text-emerald-500 uppercase tracking-[0.05em] flex items-center gap-1.5">
                                            <CheckCircle2 class="w-2.5 h-2.5" />
                                            Done by {{ sub.user?.name || 'Member' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10 bg-slate-50 dark:bg-slate-800/30 rounded-[32px] border border-dashed border-slate-200 dark:border-slate-800">
                             <div class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <ListTodo class="w-6 h-6 text-slate-300" />
                             </div>
                             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No strategic milestones defined.</p>
                             <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 leading-relaxed">Waiting for Leader to propose a work breakdown.</p>
                        </div>
                    </div>
                </div>

                <!-- Main Activity Feed -->
                <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 shadow-sm overflow-hidden min-h-[500px]">
                    <div class="p-8 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest flex items-center gap-3">
                            <Activity class="w-5 h-5 text-indigo-500" /> Workflow Activity Log
                        </h3>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Chronological Order</div>
                    </div>

                    <div class="p-8">
                        <div v-if="task.logs && task.logs.length > 0" class="space-y-10 relative">
                            <!-- Timeline Connector -->
                            <div class="absolute left-[21px] top-2 bottom-6 w-0.5 bg-slate-100 dark:bg-slate-800"></div>

                            <div v-for="(log, idx) in task.logs" :key="log.id" class="relative pl-14 animate-in slide-in-from-left-4 duration-500" :style="{ transitionDelay: (idx * 100) + 'ms' }">
                                <!-- Marker -->
                                <div class="absolute left-0 top-1 w-11 h-11 bg-white dark:bg-slate-900 border-4 border-slate-50 dark:border-slate-800 rounded-2xl flex items-center justify-center z-10 shadow-sm transition-transform group hover:scale-110">
                                    <div class="w-4 h-4 rounded-full" :class="contributorSummaries.find(s => s.name === log.user.name)?.color || 'bg-indigo-500'"></div>
                                </div>

                                <div class="p-6 bg-slate-50/50 dark:bg-slate-800/20 rounded-[32px] border border-slate-100 dark:border-slate-800/50 hover:border-indigo-200 dark:hover:border-indigo-900/50 transition-all">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 flex items-center justify-center text-[10px] font-black shadow-sm">
                                                {{ log.user.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ log.user.name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ new Date(log.created_at).toLocaleString([], { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-white dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-100 dark:border-slate-700/50 shadow-sm">
                                            <span class="text-[9px] font-bold text-slate-400 line-through">{{ log.progress_sebelum }}%</span>
                                            <ArrowRight class="w-3 h-3 text-indigo-500" />
                                            <span class="text-[11px] font-black text-indigo-600 dark:text-indigo-400">{{ log.progress_sesudah }}%</span>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-300 leading-relaxed italic border-l-4 border-indigo-500/30 pl-4 py-1">
                                        {{ log.keterangan || 'Update progres rutin tanpa catatan tambahan.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-6">
                                <HelpCircle class="w-10 h-10 text-slate-200" />
                            </div>
                            <h4 class="text-lg font-black text-slate-300 uppercase tracking-widest">No activity reported yet</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Waiting for team synchronization...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Analytics & Sidebar -->
            <div class="col-span-12 lg:col-span-5 space-y-8">
                <!-- Participation Analytics -->
                <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-[0.2em] mb-1">Participation</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Team Contribution Breakdown</p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl flex items-center justify-center">
                            <PieChart class="w-6 h-6 text-indigo-500" />
                        </div>
                    </div>

                    <!-- Vertical Animated Bar Chart -->
                    <div class="flex items-end justify-around h-64 gap-4 px-4 mb-10">
                        <div v-for="(contributor, idx) in contributorSummaries" :key="idx" class="flex flex-col items-center flex-1 group/bar relative">
                            <!-- Tooltip on Hover -->
                            <div class="absolute -top-12 bg-slate-900 text-white text-[10px] font-black px-3 py-1.5 rounded-xl opacity-0 group-hover/bar:opacity-100 transition-all pointer-events-none scale-0 group-hover/bar:scale-100 mb-2 whitespace-nowrap z-20">
                                {{ contributor.totalContribution }}% Progress Added
                            </div>
                            
                            <!-- Bar -->
                            <div class="w-full max-w-[40px] relative">
                                <div :class="[contributor.color, 'w-full rounded-2xl transition-all duration-1000 ease-out origin-bottom shadow-lg group-hover/bar:scale-x-110']"
                                     :style="{ 
                                         height: (contributor.totalContribution / 100 * 200) + 'px',
                                         transitionDelay: (idx * 150) + 'ms'
                                     }"
                                >
                                    <div class="absolute inset-0 bg-white/20 rounded-2xl opacity-0 group-hover/bar:opacity-100 transition-opacity"></div>
                                </div>
                            </div>

                            <!-- Label -->
                            <div class="mt-4 text-center">
                                <div class="w-6 h-6 rounded-lg mx-auto mb-2 flex items-center justify-center text-[8px] font-black text-white shadow-sm" :class="contributor.color">
                                    {{ contributor.name.charAt(0) }}
                                </div>
                                <p class="text-[9px] font-black text-slate-900 dark:text-white uppercase tracking-tighter truncate max-w-[60px]">{{ contributor.name.split(' ')[0] }}</p>
                            </div>
                        </div>
                        <div v-if="contributorSummaries.length === 0" class="w-full h-full flex items-center justify-center border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-3xl">
                            <p class="text-[10px] font-black text-slate-300 uppercase italic">No participation data</p>
                        </div>
                    </div>

                    <!-- Detailed Participation Cards -->
                    <div class="space-y-3">
                        <div v-for="(contributor, idx) in contributorSummaries" :key="idx" class="flex items-center justify-between p-4 bg-slate-50/50 dark:bg-slate-800/30 rounded-[24px] border border-slate-100 dark:border-slate-800/50 hover:bg-white dark:hover:bg-slate-800 transition-all group">
                            <div class="flex items-center gap-4">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center text-[11px] font-black text-white shadow-md group-hover:rotate-6 transition-transform', contributor.color]">
                                    {{ contributor.name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ contributor.name }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase">{{ contributor.logsCount }} Activity Logs</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 leading-none mb-0.5">{{ contributor.totalContribution }}%</p>
                                <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Team Share</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Delegation Matrix (Who is assigned?) -->
                <div v-if="task.tipe_penugasan === 'divisi' && task.sub_tugas?.length > 0" class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-[0.2em] mb-1">Delegation Roster</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Assigned Strategic Workforce</p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl flex items-center justify-center">
                            <Users class="w-6 h-6 text-emerald-500" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div v-for="person in [...new Set(task.sub_tugas.map(s => s.assignee?.name || 'Open Participation'))]" :key="person" 
                             class="flex items-center justify-between p-4 bg-slate-50/50 dark:bg-slate-800/30 rounded-[24px] border border-slate-100 dark:border-slate-800/50">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 flex items-center justify-center text-[11px] font-black shadow-sm text-indigo-600">
                                    {{ person.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ person }}</p>
                                    <p class="text-[8px] font-bold text-slate-400 uppercase italic">Planned Responsibility</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 text-[8px] font-black px-2.5 py-1 rounded-lg uppercase">
                                    {{ task.sub_tugas.filter(s => (s.assignee?.name || 'Open Participation') === person).length }} Segments
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Details -->
                <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200/50 dark:border-slate-800/50 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-slate-50 dark:border-slate-800/50 bg-slate-50/30 dark:bg-slate-800/20 flex items-center justify-between">
                        <h4 class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-[0.2em] flex items-center gap-3">
                            <ShieldCheck class="w-4 h-4 text-emerald-500" /> Insight Logistics
                        </h4>
                        <div class="px-3 py-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm text-[9px] font-black text-slate-400 uppercase tracking-widest">Task ID #{{ task.id }}</div>
                    </div>
                    
                    <div class="p-8 space-y-8">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2 italic">
                                <Calendar class="w-3 h-3 text-indigo-500" /> Deadline Synchronization
                            </p>
                            <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[30px] border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ task.deadline ? new Date(task.deadline).toLocaleDateString([], { day: '2-digit', month: 'long', year: 'numeric' }) : 'No Deadline' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">{{ task.deadline ? new Date(task.deadline).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'Flexible Timing' }}</p>
                                </div>
                                <div class="w-12 h-12 bg-white dark:bg-slate-900 rounded-2xl flex items-center justify-center shadow-sm">
                                    <Clock class="w-6 h-6 text-indigo-500" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2 italic">
                                <User class="w-3 h-3 text-indigo-500" /> Authority Matrix
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 opacity-60 italic">Assigned By</p>
                                    <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase truncate">{{ task.assigner.name }}</p>
                                </div>
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 opacity-60 italic">Division Head</p>
                                    <p class="text-[10px] font-black text-slate-900 dark:text-white uppercase truncate">{{ task.sesi_tugas.creator?.name || 'Manager Hub' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-50 dark:border-slate-800">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2 italic">
                                <MessageSquare class="w-3 h-3 text-indigo-500" /> Primary Directive
                            </p>
                            <div class="bg-indigo-50/20 dark:bg-indigo-900/10 p-6 rounded-[32px] border border-indigo-100/30 dark:border-indigo-800/20 text-xs font-bold text-slate-600 dark:text-slate-300 leading-relaxed min-h-[100px] italic">
                                "{{ task.deskripsi || 'No detailed instructions provided for this assignment.' }}"
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ManagerLayout>
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

@keyframes slide-in-from-left-4 {
    from {
        opacity: 0;
        transform: translateX(-16px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-in {
    animation-fill-mode: both;
}
</style>
