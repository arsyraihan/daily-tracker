<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { 
    Sun, 
    Moon, 
    Bell, 
    Search, 
    LogOut,
    User,
    Settings
} from 'lucide-vue-next';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const isDark = ref(false);

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <header class="h-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between px-8 sticky top-0 z-40 transition-all duration-300">
        <!-- Search bar -->
        <div class="relative w-96 hidden md:block group">
            <span class="absolute inset-y-0 left-4 flex items-center">
                <Search class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
            </span>
            <input 
                type="text" 
                class="block w-full pl-12 pr-4 py-3 border-none bg-slate-50 dark:bg-slate-800/50 rounded-2xl focus:ring-2 focus:ring-indigo-500 text-sm dark:text-gray-200 transition-all shadow-inner placeholder:text-slate-400 placeholder:font-medium" 
                placeholder="Search resources..."
            >
        </div>

        <div class="flex items-center space-x-5">
            <!-- Theme Toggle -->
            <button 
                @click="toggleDarkMode"
                class="p-2.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all hover:scale-110 active:scale-90"
                aria-label="Toggle Dark Mode"
            >
                <Sun v-if="isDark" class="w-5 h-5 text-amber-500" />
                <Moon v-else class="w-5 h-5" />
            </button>

            <!-- Notifications -->
            <button class="p-2.5 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl relative transition-all hover:scale-110 active:scale-90">
                <Bell class="w-5 h-5" />
                <span class="absolute top-2 right-2 w-2 h-2 bg-indigo-500 rounded-full border-2 border-white dark:border-slate-900 animate-pulse"></span>
            </button>

            <!-- User Dropdown -->
            <div class="relative">
                <Dropdown align="right" width="64">
                    <template #trigger>
                        <button class="flex items-center space-x-3 p-1.5 rounded-2xl hover:bg-white dark:hover:bg-slate-800 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:shadow-xl group">
                            <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white text-xs font-black shadow-lg shadow-indigo-500/30 group-hover:rotate-6 transition-transform">
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <div class="text-left hidden lg:block pr-2">
                                <p class="text-xs font-black text-slate-900 dark:text-white leading-none uppercase tracking-tight">{{ $page.props.auth.user.name }}</p>
                                <p class="text-[9px] text-indigo-500 dark:text-indigo-400 mt-1.5 uppercase tracking-[0.2em] font-black italic">Administrator</p>
                            </div>
                        </button>
                    </template>

                    <template #content>
                        <div class="p-2">
                            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 rounded-xl mb-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Signed in as</p>
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 truncate">{{ $page.props.auth.user.email }}</p>
                            </div>
                            
                            <DropdownLink :href="route('profile.show')" class="rounded-xl transition-all">
                                <div class="flex items-center font-bold text-slate-600 dark:text-slate-400 py-1">
                                    <User class="w-4 h-4 mr-3" />
                                    <span>Account Settings</span>
                                </div>
                            </DropdownLink>
                            
                            <div class="border-t border-slate-100 dark:border-slate-800 my-2"></div>
                            
                            <form @submit.prevent="logout">
                                <DropdownLink as="button" class="rounded-xl transition-all group">
                                    <div class="flex items-center font-black text-rose-500 py-1 uppercase tracking-widest text-[10px]">
                                        <LogOut class="w-4 h-4 mr-3 group-hover:translate-x-1 transition-transform" />
                                        <span>Terminate Session</span>
                                    </div>
                                </DropdownLink>
                            </form>
                        </div>
                    </template>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
