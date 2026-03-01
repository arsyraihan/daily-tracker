<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { 
    Users, Shield, Key, Sparkles, Calendar, Clock,
    ChevronRight, UserPlus, ShieldCheck, Settings, HelpCircle
} from 'lucide-vue-next';
import { usePage, Link } from '@inertiajs/vue3';

const { auth } = usePage().props;
const userRoles = computed(() => auth.user?.roles || []);
const isSuperAdmin = computed(() => userRoles.value.includes('superadmin'));

const props = defineProps({
    stats: {
        type: Array,
        default: () => []
    },
});

function getIcon(iconName) {
    const iconsMap = {
        'Users': Users,
        'Shield': Shield,
        'Key': Key,
        'Sparkles': Sparkles,
        'Calendar': Calendar,
        'Clock': Clock,
        'UserPlus': UserPlus,
        'ShieldCheck': ShieldCheck,
        'Settings': Settings,
    };
    // If not in map, just return a default
    return iconsMap[iconName] || HelpCircle;
}

const currentTime = ref(new Date().toLocaleTimeString());
let timeInterval = null;

onMounted(() => {
    timeInterval = setInterval(() => {
        currentTime.value = new Date().toLocaleTimeString();
    }, 1000);
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
});

const quickActions = [
    { label: 'Manage Users', icon: Users, color: 'text-blue-600', bg: 'bg-blue-50', link: route().has('super-admin.users.index') ? route('super-admin.users.index') : '#', roles: ['superadmin'] },
    { label: 'Manage Roles', icon: Shield, color: 'text-emerald-600', bg: 'bg-emerald-50', link: route().has('super-admin.roles.index') ? route('super-admin.roles.index') : '#', roles: ['superadmin'] },
    { label: 'Permissions', icon: Key, color: 'text-indigo-600', bg: 'bg-indigo-50', link: route().has('super-admin.permissions.index') ? route('super-admin.permissions.index') : '#', roles: ['superadmin'] },
    { label: 'Access Control', icon: ShieldCheck, color: 'text-rose-600', bg: 'bg-rose-50', link: route().has('super-admin.give-access.index') ? route('super-admin.give-access.index') : '#', roles: ['superadmin'] },
];

const filteredQuickActions = computed(() => {
    if (!auth.user) return [];
    return quickActions.filter(action => {
        if (!action.roles) return true;
        return userRoles.value.some(role => action.roles.includes(role));
    });
});
</script>

<template>
    <SuperAdminLayout title="RBAC Dashboard">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 mb-1">
                        <Sparkles class="w-5 h-5 text-amber-500 animate-pulse" />
                        <span class="text-amber-600 dark:text-amber-400 font-black text-[10px] uppercase tracking-[0.2em]">{{ greeting }}</span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                        RBAC <span class="text-indigo-600 dark:text-indigo-400">Control Center</span>
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 font-bold">
                        {{ isSuperAdmin ? 'Manage system access, roles, and user permissions efficiently.' : 'Your personal dashboard for tracking and permissions.' }}
                    </p>
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

        <!-- Stats Grid (SuperAdmin Only) -->
        <div v-if="$hasRole('superadmin')" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div v-for="stat in stats" :key="stat.label" class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110', stat.bg]">
                        <component :is="getIcon(stat.icon)" :class="['w-6 h-6', stat.color]" />
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-lg">
                        {{ stat.trend }}
                    </span>
                </div>
                <h4 class="text-4xl font-black text-slate-900 dark:text-white mb-1">{{ stat.value }}</h4>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ stat.label }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Side -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Management Toolkit (Filtered) -->
                <div v-if="$hasRole('superadmin')" class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[3rem] p-10 overflow-hidden relative shadow-2xl shadow-indigo-500/30">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-2xl font-black text-white mb-2">Management Toolkit</h3>
                        <p class="text-indigo-100 font-bold mb-8 opacity-80">Direct access to core Role-Based Access Control features.</p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <Link v-for="action in filteredQuickActions" :key="action.label" :href="action.link" class="group bg-white/10 hover:bg-white backdrop-blur-md p-6 rounded-3xl border border-white/10 transition-all duration-300 transform hover:scale-105 active:scale-95 text-left flex flex-col items-center sm:items-start">
                                <div class="w-12 h-12 bg-white/20 group-hover:bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 transition-colors">
                                    <component :is="action.icon" class="w-6 h-6 text-white group-hover:text-indigo-600" />
                                </div>
                                <p class="text-white group-hover:text-indigo-900 font-black text-[10px] sm:text-xs uppercase tracking-widest text-center sm:text-left">{{ action.label }}</p>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-8 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between mb-8 px-2">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-widest">System Overview</h3>
                        <div class="text-indigo-600 font-black text-[10px] uppercase tracking-[0.2em] bg-indigo-50 px-3 py-1 rounded-full">Active Environment</div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex gap-4 group p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                <ShieldCheck class="w-6 h-6 text-emerald-500" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-slate-900 dark:text-white font-black text-sm uppercase tracking-tight mb-1">RBAC Integrity Checked</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed italic">All system roles and permissions are synchronized with the central authentication authority.</p>
                            </div>
                        </div>

                        <div class="flex gap-4 group p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                <Settings class="w-6 h-6 text-indigo-500" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-slate-900 dark:text-white font-black text-sm uppercase tracking-tight mb-1">Security Protocols</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed italic">Level-3 encryption active on all user session tokens and credential management.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Profile Info -->
            <div class="lg:col-span-4 space-y-8">
                <div class="bg-slate-900 rounded-[3rem] p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    
                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-24 h-24 rounded-[2rem] bg-indigo-600 p-1 mb-6 rotate-3 hover:rotate-0 transition-transform shadow-2xl shadow-indigo-500/50">
                            <div class="w-full h-full rounded-[1.8rem] bg-slate-800 flex items-center justify-center overflow-hidden">
                                <Users class="w-12 h-12 text-indigo-400" />
                            </div>
                        </div>
                        <h4 class="text-xl font-black text-white uppercase tracking-tighter">{{ auth.user.name }}</h4>
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] mb-8">{{ $hasRole('superadmin') ? 'System Architect' : 'System User' }}</p>
                        
                        <div class="w-full space-y-3">
                            <div class="bg-slate-800/50 p-4 rounded-2xl border border-white/5">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Primary Role</p>
                                <p class="text-xs font-black text-slate-200 uppercase tracking-widest">{{ auth.user.roles[0] || 'N/A' }}</p>
                            </div>
                            <div class="bg-slate-800/50 p-4 rounded-2xl border border-white/5">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Access Level</p>
                                <p class="text-xs font-black text-emerald-400 uppercase tracking-widest">{{ $hasRole('superadmin') ? 'Unrestricted' : 'Restricted' }}</p>
                            </div>  
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 dark:bg-indigo-900/10 rounded-[3rem] p-8 border border-indigo-100 dark:border-indigo-900/30">
                    <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-[0.3em] mb-6 flex items-center">
                        <Shield class="w-4 h-4 mr-2 text-indigo-500" />
                        Quick Notice
                    </h3>
                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 leading-loose italic uppercase tracking-wider">
                        Changes to roles and permissions take effect immediately. Please exercise caution when modifying elevated privilege groups.
                    </p>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<style scoped>
</style>

<style scoped>
</style>