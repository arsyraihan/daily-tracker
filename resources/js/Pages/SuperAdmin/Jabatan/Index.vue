<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Briefcase, Plus, Pencil, Trash2, X, ChevronRight, Hash } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    jabatan: Array
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    nama: '',
    level: 1
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.nama = item.nama;
    form.level = item.level;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('super-admin.jabatan.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('super-admin.jabatan.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) {
        router.delete(route('super-admin.jabatan.destroy', id));
    }
};

const getLevelIcon = (level) => {
    if (level <= 1) return 'text-slate-400 bg-slate-100';
    if (level <= 3) return 'text-indigo-600 bg-indigo-50';
    if (level <= 5) return 'text-emerald-600 bg-emerald-50';
    return 'text-amber-600 bg-amber-50';
};
</script>

<template>
    <SuperAdminLayout title="Kelola Jabatan">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-6">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                        Management <span class="text-indigo-600 dark:text-indigo-400">Jabatan</span>
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 font-bold text-sm italic">Definisikan struktur jabatan dan level hierarki organisasi.</p>
                </div>
                
                <button 
                    @click="openCreateModal"
                    class="group flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl transition-all shadow-lg shadow-indigo-500/30 hover:scale-105 active:scale-95"
                >
                    <Plus class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform" />
                    <span class="font-black text-xs uppercase tracking-widest">Tambah Jabatan</span>
                </button>
            </div>
        </template>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Jabatan</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Hierarki Level</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="item in jabatan" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                        <Briefcase class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ item.nama }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Role ID #{{ item.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center justify-center">
                                    <div :class="['px-4 py-2 rounded-xl flex items-center gap-2 font-black text-xs uppercase tracking-tight', getLevelIcon(item.level)]">
                                        <Hash class="w-3.5 h-3.5" />
                                        <span>Level {{ item.level }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        @click="openEditModal(item)"
                                        class="p-2.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all"
                                        title="Edit"
                                    >
                                        <Pencil class="w-5 h-5" />
                                    </button>
                                    <button 
                                        @click="deleteItem(item.id)"
                                        class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-xl transition-all"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="jabatan.length === 0">
                            <td colspan="3" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <Briefcase class="w-16 h-16 text-slate-200 dark:text-slate-800 mb-4" />
                                    <p class="text-slate-400 font-black uppercase tracking-widest text-xs">Belum ada data jabatan</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form -->
        <Modal :show="isModalOpen" @close="closeModal" max-width="lg">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        {{ isEditing ? 'Update' : 'Definisikan' }} <span class="text-indigo-600">Jabatan</span>
                    </h3>
                    <button @click="closeModal" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <InputLabel for="nama" value="Nama Jabatan" class="text-[10px] font-black uppercase tracking-widest text-slate-400" />
                        <TextInput
                            id="nama"
                            v-model="form.nama"
                            type="text"
                            class="w-full"
                            placeholder="Contoh: Manager, Supervisor, Staff"
                            required
                        />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div class="space-y-2">
                        <InputLabel for="level" value="Hierarchy Level" class="text-[10px] font-black uppercase tracking-widest text-slate-400" />
                        <TextInput
                            id="level"
                            v-model="form.level"
                            type="number"
                            min="1"
                            class="w-full"
                            placeholder="Makin besar makin tinggi"
                            required
                        />
                        <p class="text-[9px] text-slate-400 font-bold italic uppercase tracking-wider mt-1 px-2">* Level digunakan untuk menentukan urutan atasan & bawahan.</p>
                        <InputError :message="form.errors.level" />
                    </div>

                    <div class="flex justify-end pt-4">
                        <PrimaryButton 
                            class="w-full justify-center py-4 bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 uppercase tracking-widest font-black"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ isEditing ? 'Simpan Perubahan' : 'Definisikan Jabatan Baru' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </SuperAdminLayout>
</template>
