<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SupervisorSidebar from '@/Components/SupervisorSidebar.vue';
import Navbar from '@/Components/Navbar.vue';
import PremiumToast from '@/Components/PremiumToast.vue';

defineProps({
    title: String,
});

const isCollapsed = ref(false);

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans transition-colors duration-300">
        <Head :title="title" />

        <!-- Flash Messages -->
        <PremiumToast />

        <!-- Sidebar -->
        <SupervisorSidebar :isCollapsed="isCollapsed" @toggleCollapse="toggleSidebar" />

        <!-- Main Content -->
        <div 
            :class="[
                'transition-all duration-300 min-h-screen flex flex-col',
                isCollapsed ? 'ml-20' : 'ml-64'
            ]"
        >
            <Navbar />

            <main class="flex-grow p-4 md:p-8">
                <!-- Page Title Section -->
                <div v-if="$slots.header" class="mb-8">
                    <slot name="header" />
                </div>

                <!-- Page Content -->
                <slot />
            </main>

            <footer class="p-6 text-center text-slate-400 text-sm">
                &copy; {{ new Date().getFullYear() }} DailyTracker. Supervisor Dashboard.
            </footer>
        </div>
    </div>
</template>

<style>
* {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.dark * {
    transition-duration: 300ms;
}
</style>
