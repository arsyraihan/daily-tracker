<script setup>
import { Link } from "@inertiajs/vue3";
import {
    LayoutDashboard,
    ClipboardList,
    Settings,
    User,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Shield,
    Key,
    Users,
} from "lucide-vue-next";

defineProps({
    isCollapsed: Boolean,
});

const emit = defineEmits(["toggleCollapse"]);

const menuItems = [
    {
        name: "Dashboard",
        icon: LayoutDashboard,
        route: "dashboard",
        active: "dashboard",
    },
    {
        name: "Employees",
        icon: Users,
        route: "users.index",
        active: "users.*",
        permission: "manage-users",
    },
    {
        name: "Roles",
        icon: Shield,
        route: "super-admin.roles.index",
        active: "super-admin.roles.*",
        permission: "manage-rbac",
    },
    {
        name: "Permissions",
        icon: Key,
        route: "super-admin.permissions.index",
        active: "super-admin.permissions.*",
        permission: "manage-rbac",
    },
    {
        name: "Profile",
        icon: User,
        route: "profile.show",
        active: "profile.show",
    },
    {
        name: "Users",
        icon: User,
        route: "super-admin.users.index",
        active: "super-admin.users.index",
    },
];
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 h-full bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 z-50',
            isCollapsed ? 'w-20' : 'w-64',
        ]"
    >
        <div
            class="flex items-center justify-between h-16 px-4 border-b border-slate-200 dark:border-slate-800"
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

        <nav class="mt-6 px-3 space-y-1">
            <template v-for="item in menuItems" :key="item.name">
                <Link
                    v-if="
                        !item.permission ||
                        $page.props.auth.user.permissions.includes(
                            item.permission,
                        ) ||
                        $page.props.auth.user.roles.includes('superadmin')
                    "
                    :href="route(item.route)"
                    :class="[
                        'flex items-center px-3 py-3 rounded-xl transition-all duration-200 group',
                        route().current(item.active)
                            ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800',
                    ]"
                >
                    <component
                        :is="item.icon"
                        :class="[
                            'w-6 h-6 transition-colors duration-200',
                            route().current(item.active)
                                ? 'text-indigo-600 dark:text-indigo-400'
                                : 'text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400',
                        ]"
                    />
                    <span
                        v-if="!isCollapsed"
                        class="ml-3 font-medium transition-opacity duration-300"
                    >
                        {{ item.name }}
                    </span>
                    <div
                        v-if="route().current(item.active) && !isCollapsed"
                        class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"
                    ></div>
                </Link>
            </template>
        </nav>

        <div class="absolute bottom-6 w-full px-3">
            <button
                @click="$emit('toggleCollapse')"
                class="flex items-center justify-center w-full py-2 bg-slate-100 dark:bg-slate-800 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
            >
                <component
                    :is="isCollapsed ? ChevronRight : ChevronLeft"
                    class="w-5 h-5"
                />
            </button>
        </div>
    </aside>
</template>
