<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, AlertCircle, Info, X } from 'lucide-vue-next';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');

const show = (msg, t = 'success') => {
    message.value = msg;
    type.value = t;
    visible.value = true;
    
    setTimeout(() => {
        visible.value = false;
    }, 5000);
};

// Watch for flash messages from Inertia
watch(() => page.props.flash, (flash) => {
    if (flash.success) show(flash.success, 'success');
    if (flash.error) show(flash.error, 'error');
    if (flash.warning) show(flash.warning, 'warning');
    if (flash.info) show(flash.info, 'info');
}, { deep: true, immediate: true });

const icons = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertCircle,
    info: Info,
};

const colors = {
    success: 'bg-emerald-500 shadow-emerald-500/20',
    error: 'bg-rose-500 shadow-rose-500/20',
    warning: 'bg-amber-500 shadow-amber-500/20',
    info: 'bg-sky-500 shadow-sky-500/20',
};
</script>

<template>
    <div class="fixed bottom-8 right-8 z-[100] transition-all duration-500 ease-out pointer-events-none"
         :class="[visible ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-12 opacity-0 scale-90']">
        <div v-if="visible" 
             class="pointer-events-auto flex items-center p-4 rounded-2xl backdrop-blur-xl border border-white/20 shadow-2xl min-w-[320px] max-w-md"
             :class="colors[type]">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center mr-4">
                <component :is="icons[type]" class="w-6 h-6 text-white" />
            </div>
            
            <div class="flex-1 mr-4">
                <p class="text-white font-black text-sm uppercase tracking-wider mb-0.5">{{ type }}</p>
                <p class="text-white/90 text-[13px] font-bold leading-tight">{{ message }}</p>
            </div>
            
            <button @click="visible = false" class="text-white/50 hover:text-white transition-colors">
                <X class="w-5 h-5" />
            </button>
        </div>
    </div>
</template>
