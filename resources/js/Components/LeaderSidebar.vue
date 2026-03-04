<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    LayoutDashboard, 
    CheckSquare, 
    User,
    ChevronLeft,
    ChevronRight,
    Users,
    Clock,
    Zap,
    Briefcase,
    ListTodo,
    ClipboardList
} from 'lucide-vue-next';

defineProps({
    isCollapsed: Boolean,
});

const emit = defineEmits(['toggleCollapse']);

const { auth } = usePage().props;

const menuItems = [
    { name: 'Dashboard', icon: LayoutDashboard, route: 'employee.dashboard', active: 'employee.dashboard' },
    { name: 'Koordinasi Tim', icon: Users, route: 'leader.tugas.index', active: 'leader.tugas.*' },
    { name: 'Tugas Saya', icon: CheckSquare, route: 'employee.tugas.index', active: 'employee.tugas.*' },
    { name: 'Absensi Saya', icon: Clock, route: 'employee.absensi.index', active: 'employee.absensi.index' },
    { name: 'Laporan Saya', icon: ClipboardList, route: 'employee.reports.index', active: 'employee.reports.*' },
    { name: 'Profile', icon: User, route: 'profile.show', active: 'profile.show' },
];
</script>

<template>
    <aside 
        :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-50 flex flex-col',
            isCollapsed ? 'w-20' : 'w-64'
        ]"
    >
        <!-- Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
            <Link :href="route('dashboard')" class="flex items-center space-x-3 overflow-hidden">
                <div class="flex-shrink-0 w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <span class="text-white font-bold text-xl uppercase italic">L</span>
                </div>
                <span v-if="!isCollapsed" class="font-black text-lg text-slate-800 dark:text-white whitespace-nowrap tracking-tight uppercase">Leader <span class="text-indigo-600">Workspace</span></span>
            </Link>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-1 custom-scrollbar">
             <div v-if="!isCollapsed" class="px-3 mb-2">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Management Space</p>
            </div>

            <template v-for="item in menuItems" :key="item.name">
                <Link 
                    :href="route().has(item.route) ? route(item.route) : '#'"
                    :class="[
                        'flex items-center px-3 py-3 rounded-xl transition-all duration-200 group relative',
                        route().current(item.active) 
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400' 
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'
                    ]"
                >
                    <component 
                        :is="item.icon" 
                        :class="[
                            'w-6 h-6 transition-colors duration-200 flex-shrink-0',
                            route().current(item.active) ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400'
                        ]"
                    />
                    <span 
                        v-if="!isCollapsed" 
                        class="ml-3 font-bold transition-opacity duration-300 whitespace-nowrap uppercase text-[11px] tracking-widest"
                    >
                        {{ item.name }}
                    </span>
                    <div 
                        v-if="route().current(item.active) && !isCollapsed" 
                        class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400 shadow-sm"
                    ></div>
                </Link>
            </template>
        </nav>

        <!-- Footer -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0 bg-white dark:bg-slate-900">
            <button 
                @click="$emit('toggleCollapse')"
                class="flex items-center justify-center w-full py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-slate-600 dark:text-slate-400 transition-colors shadow-sm"
            >
                <component :is="isCollapsed ? ChevronRight : ChevronLeft" class="w-5 h-5" />
            </button>
        </div>
    </aside>
</template>

<style scoped>
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
