<script setup>
import { ref } from "vue";
import UserLayout from "@/Layouts/UserLayout.vue";
import { 
    Calendar, Clock, CheckCircle, TrendingUp,
    Shield, Briefcase, Building, Mail, Sparkles, UserPlus, HelpCircle
} from "lucide-vue-next";
import { usePage } from "@inertiajs/vue3";

const { auth } = usePage().props;

const props = defineProps({
    user: Object,
    stats: Array,
});

function getIcon(iconName) {
    const iconsMap = {
        'Clock': Clock,
        'CheckCircle': CheckCircle,
        'Calendar': Calendar,
        'TrendingUp': TrendingUp
    };
    return iconsMap[iconName] || HelpCircle;
}
</script>

<template>
    <UserLayout title="Employee Workspace">
        <template #header>
            <div class="space-y-1 py-6">
                <div class="flex items-center gap-2 mb-1">
                    <Sparkles class="w-5 h-5 text-indigo-500 animate-pulse" />
                    <span class="text-indigo-600 dark:text-indigo-400 font-black text-[10px] uppercase tracking-[0.2em]">Employee Workspace</span>
                </div>
                <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase">
                    Your <span class="text-indigo-600 dark:text-indigo-400">Activity Hub</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 font-bold text-sm italic">Manage your attendance and daily tasks center.</p>
            </div>
        </template>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all group">
                <div class="flex items-center gap-4">
                    <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-lg shadow-indigo-500/10', stat.bg]">
                        <component :is="getIcon(stat.icon)" :class="['w-6 h-6', stat.color]" />
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white leading-none">{{ stat.value }}</h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Side: Activity / Profile Details -->
            <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-[3rem] p-10 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-10 px-2">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none">Profile Metadata</h3>
                    <div class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 dark:bg-indigo-900/40 dark:border-indigo-800 dark:text-indigo-400">Employee Secured</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors rounded-2xl border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <Building class="w-5 h-5 text-indigo-500" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Division Unit</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ user.divisi?.nama || 'N/A' }}</p>
                            </div>
                        </div>

                         <div class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors rounded-2xl border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <Briefcase class="w-5 h-5 text-emerald-500" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Designated Jabatan</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ user.jabatan?.nama || 'N/A' }}</p>
                            </div>
                        </div>

                         <div class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors rounded-2xl border border-transparent hover:border-slate-100 dark:hover:border-slate-800">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <UserPlus class="w-5 h-5 text-indigo-500" />
                            </div>
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Supervisor Access</p>
                                <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ user.atasan?.name || 'Top Management' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column Detail -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-dashed border-slate-200 dark:border-slate-700 flex flex-col justify-center text-center">
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 italic">Security Credentials</div>
                        <div class="space-y-4">
                            <div class="flex flex-col items-center">
                                 <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ user.email }}</span>
                                 <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Primary Email Auth</p>
                            </div>
                            <div class="flex flex-col items-center">
                                 <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-tight">Active SSO Session</span>
                                 <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Authentication Layer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Personal Identity Card -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-indigo-900 rounded-[3rem] p-8 relative overflow-hidden text-center shadow-2xl shadow-indigo-500/30">
                     <div class="absolute -top-12 -right-12 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                     <div class="absolute -bottom-12 -left-12 w-24 h-24 bg-indigo-500/20 rounded-full blur-2xl"></div>
                     
                     <div class="relative z-10 flex flex-col items-center">
                          <div class="w-24 h-24 rounded-[2rem] bg-indigo-600 p-1 mb-6 rotate-3 shadow-xl">
                               <div class="w-full h-full rounded-[1.8rem] bg-slate-800 flex items-center justify-center font-black text-3xl text-indigo-400 italic">
                                    {{ user.name.charAt(0) }}
                               </div>
                          </div>
                          <h4 class="text-xl font-black text-white uppercase tracking-tight leading-none mb-1">{{ user.name }}</h4>
                          <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mb-8 italic italic">Authenticated Personnel</span>
                          
                          <div class="w-full grid grid-cols-2 gap-3">
                               <div class="bg-white/5 border border-white/5 p-4 rounded-2xl">
                                    <p class="text-[9px] font-black text-slate-500 uppercase mb-1">NIK ID</p>
                                    <p class="text-[11px] font-black text-slate-200 uppercase tracking-widest italic">{{ user.kode_karyawan || '---' }}</p>
                               </div>
                               <div class="bg-white/5 border border-white/5 p-4 rounded-2xl">
                                    <p class="text-[9px] font-black text-slate-500 uppercase mb-1">Level</p>
                                    <p class="text-[11px] font-black text-slate-200 uppercase tracking-widest italic">LVL {{ user.jabatan?.level || 0 }}</p>
                               </div>
                          </div>
                          
                          <button class="mt-8 w-full py-3 bg-white text-indigo-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-transform shadow-lg">
                               Clock-In Terminal
                          </button>
                     </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-[0.3em] mb-4">Quick Statistics</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-widest group">
                            <span>Attendance Score</span>
                            <span class="text-emerald-500 font-black">98%</span>
                        </div>
                        <div class="w-full h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 w-[98%]"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-widest group pt-2">
                            <span>Tasks Completion</span>
                            <span class="text-indigo-500 font-black">84%</span>
                        </div>
                        <div class="w-full h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 w-[84%]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
