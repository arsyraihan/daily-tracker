<script setup>
import { ref, computed, onMounted } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    Clock, Calendar, CheckCircle, TrendingUp, Users, 
    Activity, ChevronRight, Search, LogIn, LogOut, 
    Plus, Target, MessageSquare, Sparkles
} from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

const { auth } = usePage().props;
// Reactive state for modals
const showAttendanceModal = ref(false);
const showTaskModal = ref(false);
const showActivityModal = ref(false);

const props = defineProps({
    auth: Object,
    activeTasks: Array,
    upcomingDeadlines: Array,
});

const layoutComponent = computed(() => {
    return props.auth.user.roles.includes('superadmin') ? SuperAdminLayout : UserLayout;
});

const currentTime = ref(new Date().toLocaleTimeString());
setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString();
}, 1000);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

const stats = [
    { label: 'Total Team', value: '24', icon: Users, color: 'text-blue-600', bg: 'bg-blue-50', trend: '+12%' },
    { label: 'Active Tasks', value: '18', icon: Target, color: 'text-indigo-600', bg: 'bg-indigo-50', trend: 'On track' },
    { label: 'Completed', value: '142', icon: CheckCircle, color: 'text-emerald-600', bg: 'bg-emerald-50', trend: 'This month' },
    { label: 'Growth', value: '94%', icon: TrendingUp, color: 'text-rose-600', bg: 'bg-rose-50', trend: '+5%' },
];
</script>

<template>
    <component :is="layoutComponent" title="Dashboard Performa">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 mb-1">
                        <Sparkles class="w-5 h-5 text-amber-500 animate-pulse" />
                        <span class="text-amber-600 dark:text-amber-400 font-black text-[10px] uppercase tracking-[0.2em]">{{ greeting }}</span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                        Welcome back, <span class="text-indigo-600 dark:text-indigo-400 capitalize">{{ auth.user.name.split(' ')[0] }}</span>
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 font-bold">Here's what's happening with your team today.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center gap-3">
                        <Calendar class="w-4 h-4 text-slate-400" />
                        <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                    </div>
                    <div class="px-4 py-2 bg-indigo-600 rounded-xl flex items-center gap-3 shadow-lg shadow-indigo-500/20">
                        <Clock class="w-4 h-4 text-white" />
                        <span class="text-sm font-black text-white">{{ currentTime }}</span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110', stat.bg]">
                        <component :is="stat.icon" :class="['w-6 h-6', stat.color]" />
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-lg">
                        {{ stat.trend }}
                    </span>
                </div>
                <h4 class="text-3xl font-black text-slate-900 dark:text-white mb-1">{{ stat.value }}</h4>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ stat.label }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Side: Actions -->
            <div class="lg:col-span-8 space-y-8">
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[3rem] p-10 overflow-hidden relative shadow-2xl shadow-indigo-500/30">
                    <!-- Abstract Background Decorations -->
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black text-white mb-2">Quick Actions</h3>
                        <p class="text-indigo-100 font-bold mb-8 opacity-80">Boost your productivity with these instantaneous tools.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <button @click="showAttendanceModal = true" class="group bg-white/10 hover:bg-white backdrop-blur-md p-6 rounded-3xl border border-white/10 transition-all duration-300 transform hover:scale-105 active:scale-95 text-left">
                                <div class="w-12 h-12 bg-white/20 group-hover:bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                    <Clock class="w-6 h-6 text-white group-hover:text-indigo-600" />
                                </div>
                                <p class="text-white group-hover:text-indigo-900 font-black text-sm uppercase tracking-widest">Attendance</p>
                            </button>
                            
                            <button @click="showTaskModal = true" class="group bg-white/10 hover:bg-white backdrop-blur-md p-6 rounded-3xl border border-white/10 transition-all duration-300 transform hover:scale-105 active:scale-95 text-left">
                                <div class="w-12 h-12 bg-white/20 group-hover:bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                    <Plus class="w-6 h-6 text-white group-hover:text-indigo-600" />
                                </div>
                                <p class="text-white group-hover:text-indigo-900 font-black text-sm uppercase tracking-widest">New Task</p>
                            </button>
                            
                            <button @click="showActivityModal = true" class="group bg-white/10 hover:bg-white backdrop-blur-md p-6 rounded-3xl border border-white/10 transition-all duration-300 transform hover:scale-105 active:scale-95 text-left">
                                <div class="w-12 h-12 bg-white/20 group-hover:bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                    <MessageSquare class="w-6 h-6 text-white group-hover:text-indigo-600" />
                                </div>
                                <p class="text-white group-hover:text-indigo-900 font-black text-sm uppercase tracking-widest">Log Activity</p>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-8 px-2">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-widest">Recent Activity</h3>
                        <button class="text-indigo-600 font-black text-xs uppercase tracking-widest hover:underline px-4 py-2">View History</button>
                    </div>
                    
                    <div class="space-y-6">
                        <div v-for="i in 3" :key="i" class="flex gap-4 group p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded-3xl transition-all border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0 group-hover:bg-white dark:group-hover:bg-slate-700 transition-colors shadow-sm">
                                <Activity class="w-6 h-6 text-slate-400 group-hover:text-indigo-500" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-slate-900 dark:text-white font-black text-sm uppercase tracking-tight">System Update</p>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ i }}h ago</span>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed underline decoration-indigo-500/20 underline-offset-4">Successfully deployed new biometric authentication module to manufacturing wing.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Profile/Mini Info -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-[3rem] p-8 border-2 border-indigo-100 dark:border-indigo-900/30">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-24 h-24 rounded-[2rem] bg-indigo-600 p-1 mb-6 rotate-3 hover:rotate-0 transition-transform shadow-2xl shadow-indigo-500/50">
                            <div class="w-full h-full rounded-[1.8rem] bg-white dark:bg-slate-900 flex items-center justify-center overflow-hidden">
                                <User class="w-12 h-12 text-indigo-600" />
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">{{ auth.user.name }}</h4>
                        <p class="text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-[0.2em] mb-6">Master Administrator</p>
                        
                        <div class="w-full grid grid-cols-2 gap-4">
                            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-indigo-50 dark:border-indigo-900/30">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">ID Code</p>
                                <p class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase font-mono">STAFF-001</p>
                            </div>
                            <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-indigo-50 dark:border-indigo-900/30">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                <p class="text-sm font-black text-emerald-500 uppercase tracking-widest">Active</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Targets -->
                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-[0.3em] mb-6 flex items-center">
                        <Target class="w-4 h-4 mr-2 text-indigo-500" />
                        Weekly Goals
                    </h3>
                    <div class="space-y-4">
                        <div v-for="j in 2" :key="j" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-indigo-500/20 transition-all cursor-default">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Efficiency Rate</span>
                                <span class="text-[10px] font-black text-indigo-600 tracking-widest">85%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 w-[85%] rounded-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Modal -->
        <PremiumModal :show="showAttendanceModal" title="Personnel Tracking" maxWidth="md" @close="showAttendanceModal = false">
            <div class="text-center py-6">
                <div class="w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-white dark:border-slate-800 shadow-xl">
                    <Clock class="w-10 h-10 text-indigo-600 dark:text-indigo-400" />
                </div>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-2 leading-none uppercase tracking-tighter">{{ currentTime }}</h3>
                <p class="text-slate-500 dark:text-slate-400 font-bold mb-8 uppercase text-xs tracking-[0.2em]">{{ new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                
                <div class="grid grid-cols-2 gap-4 px-4">
                    <button class="flex flex-col items-center gap-3 p-6 bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white rounded-[2rem] border-2 border-emerald-100 dark:border-emerald-500/20 transition-all group active:scale-95 shadow-lg shadow-emerald-500/10">
                        <LogIn class="w-8 h-8 group-hover:scale-125 transition-transform" />
                        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Clock In</span>
                    </button>
                    <button class="flex flex-col items-center gap-3 p-6 bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white rounded-[2rem] border-2 border-rose-100 dark:border-rose-500/20 transition-all group active:scale-95 shadow-lg shadow-rose-500/10">
                        <LogOut class="w-8 h-8 group-hover:scale-125 transition-transform" />
                        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Clock Out</span>
                    </button>
                </div>
            </div>
        </PremiumModal>

        <!-- Task Modal -->
        <PremiumModal :show="showTaskModal" title="Initialize Operation" maxWidth="lg" @close="showTaskModal = false">
            <form class="space-y-6 pt-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Task Nomenclature</label>
                    <input type="text" placeholder="e.g. System Audit Phase 1" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold placeholder:text-slate-400" />
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Category Assignment</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <button v-for="cat in ['Critical', 'Normal', 'Low', 'System']" :key="cat" type="button" class="px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">{{ cat }}</button>
                    </div>
                </div>
                <button type="submit" class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[2rem] font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98]">Launch Task</button>
            </form>
        </PremiumModal>

        <!-- Activity Modal -->
        <PremiumModal :show="showActivityModal" title="Activity Transmission" maxWidth="lg" @close="showActivityModal = false">
            <form class="space-y-6 pt-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-1">Detailed Report</label>
                    <textarea rows="5" placeholder="Document the operations performed..." class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-[2rem] focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-medium placeholder:text-slate-400"></textarea>
                </div>
                <button type="submit" class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[2rem] font-black text-sm uppercase tracking-[0.2em] shadow-xl shadow-indigo-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <Activity class="w-5 h-5" />
                    Transmit Log
                </button>
            </form>
        </PremiumModal>
    </component>
</template>

<style scoped>
</style>