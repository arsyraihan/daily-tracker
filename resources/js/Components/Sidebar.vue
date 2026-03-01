<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    LayoutDashboard,
    User,
    ChevronLeft,
    ChevronRight,
    ShieldCheck,
    Key,
    Users,
    Building,
    Briefcase,
} from "lucide-vue-next";

defineProps({
    isCollapsed: Boolean,
});

const emit = defineEmits(["toggleCollapse"]);

const { auth } = usePage().props;

const menuItems = [
    {
        title: "DASHBOARD",
        type: "header",
    },
    {
        title: "Overview",
        route: "dashboard",
        icon: LayoutDashboard,
        active: "dashboard",
    },
    {
        title: "MANAGER HUB",
        type: "header",
        roles: ['superadmin', 'manager', 'supervisor'],
    },
    {
        title: "Team Overview",
        route: "manager.dashboard",
        icon: Users,
        active: "manager.*",
        roles: ['superadmin', 'manager', 'supervisor'],
    },
    {
        title: "EMPLOYEE HUB",
        type: "header",
    },
    {
        title: "Personal Activity",
        route: "employee.dashboard",
        icon: User,
        active: "employee.*",
    },
    {
        title: "EMPLOYEE MANAGEMENT",
        type: "header",
        roles: ['superadmin'],
    },
    {
        title: "Employee Master",
        route: "super-admin.users.index",
        icon: Users,
        active: "super-admin.users.*",
        roles: ['superadmin'],
    },
    {
        title: "Divisions",
        route: "super-admin.divisi.index",
        icon: Building,
        active: "super-admin.divisi.*",
        roles: ['superadmin'],
    },
    {
        title: "Positions",
        route: "super-admin.jabatan.index",
        icon: Briefcase,
        active: "super-admin.jabatan.*",
        roles: ['superadmin'],
    },
    {
        title: "Roles & Permissions",
        route: "super-admin.roles.index",
        icon: ShieldCheck,
        active: "super-admin.roles.*",
        roles: ['superadmin'],
    },
    {
        title: "Access Control",
        route: "super-admin.give-access.index",
        icon: Key,
        active: "super-admin.give-access.*",
        roles: ['superadmin'],
    },
    {
        title: "SYSTEM & LOGS",
        type: "header",
    },
    {
        title: "Profile",
        route: "profile.show",
        icon: User,
        active: "profile.show",
    },
];

const filteredMenuItems = computed(() => {
    const user = auth.user;
    if (!user) return [];
    return menuItems.filter(item => {
        // Handle roles
        if (item.roles) {
            const hasRole = item.roles.some(role => user.roles.includes(role));
            if (!hasRole) return false;
        }
        
        // Handle permissions
        if (item.permissions) {
            const hasPerm = item.permissions.some(perm => user.permissions.includes(perm));
            if (!hasPerm) return false;
        }

        return true;
    });
});
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-50 flex flex-col',
            isCollapsed ? 'w-20' : 'w-64',
        ]"
    >
        <!-- Header: Fixed -->
        <div
            class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-slate-800 flex-shrink-0"
        >
            <Link
                :href="route('dashboard')"
                class="flex items-center space-x-3 overflow-hidden"
            >
                <div
                    class="flex-shrink-0 w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center"
                >
                    <span class="text-white font-bold text-xl">D</span>
                </div>
                <span
                    v-if="!isCollapsed"
                    class="font-bold text-xl text-slate-800 dark:text-white whitespace-nowrap"
                    >DailyTracker</span
                >
            </Link>
        </div>

        <!-- Navigation: Scrollable -->
        <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-1 custom-scrollbar">
            <template v-for="item in filteredMenuItems" :key="item.title">
                <div v-if="item.type === 'header'" class="mt-6 mb-2 px-4">
                    <span v-if="!isCollapsed" class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ item.title }}</span>
                    <div v-else class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>
                </div>
                <Link
                    v-else
                    :href="route().has(item.route) ? route(item.route) : '#'"
                    :class="[
                        'flex items-center px-3 py-3 rounded-xl transition-all duration-200 group',
                        route().has(item.route) && route().current(item.active)
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800',
                    ]"
                >
                    <component
                        :is="item.icon"
                        :class="[
                            'w-6 h-6 transition-colors duration-200 flex-shrink-0',
                            route().has(item.route) && route().current(item.active)
                                ? 'text-indigo-600 dark:text-indigo-400'
                                : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400',
                        ]"
                    />
                    <span
                        v-if="!isCollapsed"
                        class="ml-3 font-medium transition-opacity duration-300 whitespace-nowrap"
                    >
                        {{ item.title }}
                    </span>
                    <div
                        v-if="route().has(item.route) && route().current(item.active) && !isCollapsed"
                        class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"
                    ></div>
                </Link>
            </template>
        </nav>

        <!-- Footer: Fixed -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0 bg-white dark:bg-slate-900">
            <button
                @click="$emit('toggleCollapse')"
                class="flex items-center justify-center w-full py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl text-slate-600 dark:text-slate-400 transition-colors shadow-sm"
            >
                <component
                    :is="isCollapsed ? ChevronRight : ChevronLeft"
                    class="w-5 h-5"
                />
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

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
