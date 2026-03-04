<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { 
    Plus, Calendar, Eye, Trash2, 
    Lock, Unlock, Clock, CheckCircle2,
    Layers, UserPlus, ChevronDown, Activity, ListTodo
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DialogModal from '@/Components/DialogModal.vue';

const isModalOpen = ref(false);

const props = defineProps({
    sessions: Object,
    filters: Object,
});

const filterDate = ref(props.filters.date || '');

watch(filterDate, (value) => {
    router.get(route('manager.tugas.index'), { date: value }, {
        preserveState: true,
        replace: true
    });
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
});

const submit = () => {
    form.post(route('manager.tugas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};

const deleteSession = (id) => {
    if (confirm('Hapus sesi tugas ini secara permanen? Semua tugas di dalamnya akan ikut terhapus.')) {
        useForm({}).delete(route('manager.tugas.destroy', id));
    }
};

</script>

<template>
    <ManagerLayout title="Task Management">
        <template #header>
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none">Management <span class="text-indigo-600">Tasks</span></h2>
                    <p class="text-slate-500 font-bold uppercase text-[10px] tracking-[0.3em] mt-3">Organize daily assignments and divisional productivity.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                         <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <Calendar class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                         </div>
                         <input 
                            type="date" 
                            v-model="filterDate"
                            class="pl-11 pr-4 py-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-900/20 transition-all outline-none"
                         />
                    </div>
                    <button 
                        @click="isModalOpen = true"
                        class="bg-indigo-600 text-white px-8 py-4 rounded-xl font-black text-xs uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 dark:shadow-none active:scale-95 flex items-center gap-3"
                    >
                        <Plus class="w-5 h-5" />
                        Create Day Session
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="sessions.data.length === 0" class="bg-white dark:bg-slate-900 rounded-2xl p-20 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-8">
                    <ListTodo class="w-12 h-12 text-slate-300" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight mb-2">No Task Sessions</h3>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest italic">Open a new daily task session to start assigning work.</p>
            </div>

            <div v-else class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex-1">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800">
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Date Info</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Tasks</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="session in sessions.data" :key="session.id" class="group hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-all">
                                <td class="p-8">
                                    <div class="flex items-center gap-6">
                                        <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center font-black transition-all group-hover:scale-110 shadow-sm border border-indigo-100 dark:border-indigo-800">
                                             <ListTodo class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight mb-1">Session: {{ new Date(session.tanggal).toLocaleDateString('id-ID', { weekday: 'long' }) }}</h4>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ new Date(session.tanggal).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' }) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <div class="flex items-center gap-3">
                                        <div class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg">
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ session.tugas_count }} TASKS</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <span :class="['text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border', session.status === 'dibuka' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100']">
                                        {{ session.status === 'dibuka' ? 'ACTIVE' : 'LOCKED' }}
                                    </span>
                                </td>
                                <td class="p-8 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <Link :href="route('manager.tugas.show', session.id)" class="px-6 py-3 bg-slate-900 dark:bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-md">
                                            Assign Tasks
                                        </Link>
                                        <button @click="deleteSession(session.id)" class="p-3 text-rose-600 bg-slate-50 dark:bg-slate-800 hover:bg-white dark:hover:bg-slate-700 rounded-xl transition-all shadow-sm">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Footer -->
                <div v-if="sessions.links.length > 3" class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Showing {{ sessions.from }} - {{ sessions.to }} of {{ sessions.total }} task days
                     </p>
                     <div class="flex items-center gap-1">
                        <template v-for="(link, k) in sessions.links" :key="k">
                            <div v-if="link.url === null" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase text-slate-400 bg-white dark:bg-slate-900 opacity-50 cursor-not-allowed" v-html="link.label"></div>
                            <Link v-else :href="link.url" :class="['px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-black uppercase transition-all', link.active ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50']" v-html="link.label" />
                        </template>
                     </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <DialogModal :show="isModalOpen" @close="isModalOpen = false">
            <template #title>
                <div class="p-2">
                     <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">New <span class="text-indigo-600">Task Session</span></h3>
                     <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Initialize a daily container for divisional tasks.</p>
                </div>
            </template>

            <template #content>
                <form @submit.prevent="submit" id="createTugasSessionForm" class="p-2 space-y-8">
                    <div>
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] block mb-3 ml-1">Target Date</label>
                        <div class="relative">
                            <input type="date" v-model="form.tanggal" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl p-4 text-sm font-black focus:ring-4 focus:ring-indigo-100" />
                            <Calendar class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <div class="flex items-center gap-4 w-full px-2 pb-2">
                    <button 
                        @click="isModalOpen = false"
                        class="px-8 py-4 text-slate-500 font-black text-xs uppercase tracking-widest hover:bg-slate-100 rounded-2xl transition-all"
                    >
                        Cancel
                    </button>
                    <button 
                        form="createTugasSessionForm"
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-indigo-600 text-white p-5 rounded-3xl font-black text-xs uppercase tracking-[0.3em] hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 active:scale-95 disabled:opacity-50"
                    >
                        Confirm Day
                    </button>
                </div>
            </template>
        </DialogModal>
    </ManagerLayout>
</template>
