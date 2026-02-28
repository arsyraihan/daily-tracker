<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

watch(() => props.show, (value) => {
    if (value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = null;
    }
});

const maxWidthClass = {
    'sm': 'sm:max-w-sm',
    'md': 'sm:max-w-md',
    'lg': 'sm:max-w-lg',
    'xl': 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
}[props.maxWidth];
</script>

<template>
    <teleport to="body">
        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 z-[100] transform transition-all group">
                <!-- Glassmorphism Backdrop -->
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md dark:bg-black/60" @click="close" />

                <div class="flex items-center justify-center min-h-screen px-4 py-6 sm:px-0 pointer-events-none">
                    <transition
                        enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-90"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                    >
                        <div 
                            v-show="show" 
                            class="pointer-events-auto bg-white dark:bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl transform transition-all sm:w-full border border-white/20 dark:border-slate-800"
                            :class="maxWidthClass"
                        >
                            <!-- Header -->
                            <div class="px-8 py-6 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                                    <slot name="title">{{ title }}</slot>
                                </h3>
                                <button 
                                    v-if="closeable"
                                    @click="close"
                                    class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-all"
                                >
                                    <X class="w-6 h-6" />
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="px-8 py-8">
                                <slot />
                            </div>

                            <!-- Footer Slot -->
                            <div v-if="$slots.footer" class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 flex justify-end items-center gap-3">
                                <slot name="footer" />
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
/* Optional styling */
</style>
