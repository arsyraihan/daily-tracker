<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { Bell, CheckSquare, ArrowRight, X } from 'lucide-vue-next';

const page = usePage();
const notifications = ref([]);
const visible = ref(false);
const activeNotification = ref(null);

// Store seen notification IDs in local storage to avoid re-showing
const getSeenIds = () => JSON.parse(localStorage.getItem('seen_task_notifications') || '[]');
const saveSeenId = (id) => {
    const seen = getSeenIds();
    if (!seen.includes(id)) {
        seen.push(id);
        localStorage.setItem('seen_task_notifications', JSON.stringify(seen));
    }
};

const showNextNotification = () => {
    const pending = page.props.notifications?.unapproved_tasks || [];
    const seenIds = getSeenIds();
    
    // Find the first unseen notification
    const next = pending.find(task => !seenIds.includes(task.id));
    
    if (next) {
        activeNotification.value = next;
        visible.value = true;
        saveSeenId(next.id);
        
        // Auto hide after 8 seconds
        setTimeout(() => {
            visible.value = false;
        }, 8000);
    }
};

// Watch for changes in the shared notifications prop
watch(() => page.props.notifications?.unapproved_tasks, (newTasks) => {
    if (newTasks && newTasks.length > 0) {
        showNextNotification();
    }
}, { deep: true, immediate: true });

</script>

<template>
    <div class="fixed bottom-24 right-8 z-[110] transition-all duration-700 ease-in-out"
         :class="[visible ? 'translate-y-0 opacity-100 scale-100' : 'translate-y-12 opacity-0 scale-90 pointer-events-none']">
        
        <div v-if="activeNotification" 
             class="bg-white dark:bg-slate-900 border border-indigo-100 dark:border-indigo-900/40 p-5 rounded-3xl shadow-[0_20px_50px_-15px_rgba(79,70,229,0.2)] max-w-xs ring-1 ring-black/5 backdrop-blur-sm">
            
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/30 flex-shrink-0 animate-pulse">
                    <CheckSquare class="w-6 h-6 text-white" />
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Task Completed</span>
                        <button @click="visible = false" class="text-slate-300 hover:text-slate-500 transition-colors">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                    
                    <p class="text-xs font-black text-slate-900 dark:text-white uppercase leading-tight line-clamp-1 mb-1">
                        {{ activeNotification.judul }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-400 leading-tight">
                        <span class="text-indigo-500">{{ activeNotification.user.name }}</span> has finished their assignment and is waiting for your review.
                    </p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
                <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest">Now</p>
                <Link 
                    :href="route('manager.tugas.show', activeNotification.sesi_tugas_id)"
                    class="flex items-center gap-1.5 text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:gap-2.5 transition-all"
                >
                    Review Now <ArrowRight class="w-3 h-3" />
                </Link>
            </div>
        </div>
    </div>
</template>
