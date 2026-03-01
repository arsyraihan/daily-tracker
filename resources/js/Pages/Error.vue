<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ShieldAlert, Home, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden Access',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Sorry, you do not have permission to access this page.',
    }[props.status];
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-6 text-white font-sans overflow-hidden relative">
        <!-- Abstract Background Effects -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-red-600/10 rounded-full blur-[120px] -mr-64 -mt-64"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[120px] -ml-64 -mb-64"></div>

        <div class="max-w-md w-full text-center relative z-10">
            <!-- Icon -->
            <div class="mb-10 relative inline-block">
                <div class="w-24 h-24 bg-red-500/10 rounded-[2.5rem] flex items-center justify-center border border-red-500/20 shadow-2xl shadow-red-500/20 animate-pulse">
                    <ShieldAlert class="w-12 h-12 text-red-500" />
                </div>
            </div>

            <!-- Content -->
            <h1 class="text-6xl font-black mb-4 tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-indigo-500 uppercase italic">
                {{ status }}
            </h1>
            <h2 class="text-2xl font-black uppercase tracking-widest mb-4">
                {{ title.split(': ')[1] }}
            </h2>
            <p class="text-slate-400 font-medium mb-10 leading-relaxed max-w-sm mx-auto">
                {{ description }}
            </p>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button 
                    @click="goBack"
                    class="flex items-center justify-center gap-2 px-8 py-4 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 group"
                >
                    <ArrowLeft class="w-4 h-4 text-red-500 group-hover:-translate-x-1 transition-transform" />
                    Go Back
                </button>
                <Link 
                    :href="route('dashboard')"
                    class="flex items-center justify-center gap-2 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95 shadow-xl shadow-indigo-500/20"
                >
                    <Home class="w-4 h-4" />
                    Dashboard
                </Link>
            </div>
        </div>

        <!-- Footer Decoration -->
        <div class="absolute bottom-8 text-center w-full left-0 opacity-30">
            <p class="text-[10px] font-black uppercase tracking-[0.5em] text-slate-500">Security Protocols Active</p>
        </div>
    </div>
</template>
