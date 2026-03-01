<script setup>
import { ref } from "vue";
import SupervisorLayout from "@/Layouts/SupervisorLayout.vue";
import { 
    Users, Calendar, Clock, ClipboardList, 
    TrendingUp, UserCheck, Briefcase, Building, HelpCircle 
} from "lucide-vue-next";
import { usePage } from "@inertiajs/vue3";

const { auth } = usePage().props;

const props = defineProps({
    team: Array,
    stats: Array,
});

function getIcon(iconName) {
    const iconsMap = {
        'Users': Users,
        'Calendar': Calendar,
        'Clock': Clock,
        'ClipboardList': ClipboardList
    };
    return iconsMap[iconName] || HelpCircle;
}
</script>

<template>
    <SupervisorLayout title="Manager Control Center">
        <template #header>
            <div class="space-y-1 py-6">
                <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                    Manager <span class="text-emerald-600 dark:text-emerald-400">Workspace</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 font-bold text-sm italic">Oversight and team synchronization dashboard.</p>
            </div>
        </template>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-slate-900 p-6 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all group">
                <div class="flex items-center gap-4">
                    <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 shadow-lg shadow-emerald-500/10', stat.bg]">
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
            <!-- Team List -->
            <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Your Direct Reports</h3>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Managed personnel within your hierarchy.</p>
                    </div>
                    <Users class="w-6 h-6 text-slate-300" />
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Employee</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Position</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="member in team" :key="member.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-emerald-500/20">
                                            {{ member.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ member.name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold tracking-tight">{{ member.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2">
                                        <Briefcase class="w-3 h-3 text-slate-300" />
                                        <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase">{{ member.jabatan?.nama || 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span :class="['text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest', member.status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600']">
                                        {{ member.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="team.length === 0">
                                <td colspan="3" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 opacity-20">
                                        <Users class="w-12 h-12" />
                                        <p class="text-xs font-black uppercase tracking-widest">No team members assigned</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Side Cards -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Profile Area -->
                <div class="bg-slate-900 rounded-[3rem] p-8 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-20 h-20 rounded-[1.5rem] bg-emerald-600 flex items-center justify-center text-white mb-4 rotate-3 shadow-xl">
                            <span class="text-3xl font-black italic">{{ auth.user.name.charAt(0) }}</span>
                        </div>
                        <h4 class="text-white font-black text-lg uppercase tracking-tight leading-none">{{ auth.user.name }}</h4>
                        <p class="text-emerald-400 font-black text-[10px] uppercase tracking-[0.3em] mt-1 italic">Authorized Manager</p>
                        
                        <div class="mt-8 w-full space-y-3">
                             <div class="bg-white/5 border border-white/5 p-4 rounded-2xl flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-500 uppercase">Division</span>
                                <span class="text-xs font-black text-slate-300 uppercase">{{ auth.user.divisi?.nama || 'N/A' }}</span>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Task/Info Area -->
                <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-[3rem] p-8 border border-emerald-100 dark:border-emerald-900/30">
                    <h3 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-[0.3em] mb-4">Upcoming Responsibilities</h3>
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 leading-relaxed italic uppercase tracking-wider">
                        Step 4 (Attendance Sessions) will populate here once active. You will be able to manage daily check-ins for your team.
                     </p>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
