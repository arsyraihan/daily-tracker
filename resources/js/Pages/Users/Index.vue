<script setup>
import { ref } from 'vue';
import { useForm, Head, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { Users, Plus, Edit2, Trash2, Shield, User, Mail, Briefcase, Hash, Save, X } from 'lucide-vue-next';

const props = defineProps({
    users: Array,
    roles: Array,
});

const isModalOpen = ref(false);
const editMode = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    kode_karyawan: '',
    jabatan: '',
    divisi: '',
    role: 'user',
});

const openCreateModal = () => {
    editMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editMode.value = true;
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Don't prefill password
    form.kode_karyawan = user.kode_karyawan || '';
    form.jabatan = user.jabatan || '';
    form.divisi = user.divisi || '';
    form.role = user.roles && user.roles.length > 0 ? user.roles[0].name : 'user';
    form.clearErrors();
    isModalOpen.value = true;
};

const submit = () => {
    if (editMode.value) {
        form.put(route('users.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', id));
    }
};
</script>

<template>
    <SuperAdminLayout title="Employees Management">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Employees</h2>
                    <p class="mt-1 text-slate-500 dark:text-slate-400">Manage your company's workforce and their access levels.</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-black transition-all shadow-lg shadow-indigo-500/20 flex items-center"
                >
                    <Plus class="w-5 h-5 mr-2" />
                    Add Employee
                </button>
            </div>
        </template>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left font-medium">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <th class="px-6 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Employee</th>
                            <th class="px-6 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Position & Dept</th>
                            <th class="px-6 py-5 text-xs font-black text-slate-400 uppercase tracking-widest">Role</th>
                            <th class="px-6 py-5 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20 transition-colors">
                                        <User class="w-6 h-6 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-slate-900 dark:text-white font-black">{{ user.name }}</span>
                                        <span class="text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div v-if="user.jabatan" class="flex flex-col">
                                    <span class="text-slate-700 dark:text-slate-300 font-bold uppercase tracking-tight text-sm">{{ user.jabatan }}</span>
                                    <span class="text-xs text-slate-400 font-bold">{{ user.divisi || 'No Department' }}</span>
                                </div>
                                <span v-else class="text-xs text-slate-300 dark:text-slate-600 italic">Not set</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100 dark:border-indigo-900/50">
                                    {{ user.roles && user.roles.length > 0 ? user.roles[0].name : 'no role' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="p-2.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.id !== user.id"
                                        @click="deleteUser(user.id)"
                                        class="p-2.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Employee Modal -->
        <PremiumModal 
            :show="isModalOpen" 
            :title="editMode ? 'Edit Employee' : 'New Employee Entry'" 
            maxWidth="4xl"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 px-1">Basic Information</label>
                        <div class="space-y-4">
                            <div class="relative group">
                                <User class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.name" type="text" placeholder="Full Name" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold" required>
                            </div>
                            <div class="relative group">
                                <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.email" type="email" placeholder="Email Address" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold" required>
                            </div>
                            <div v-if="!editMode" class="relative group">
                                <Hash class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.password" type="password" placeholder="Account Password" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold" required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 px-1">System Role</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label v-for="role in roles" :key="role.id" class="flex items-center p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-indigo-500/20 cursor-pointer transition-all group has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:bg-indigo-900/10">
                                <input v-model="form.role" type="radio" :value="role.name" class="w-5 h-5 rounded-full border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">{{ role.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3 px-1">Organizational Details</label>
                        <div class="space-y-4">
                            <div class="relative group">
                                <Hash class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.kode_karyawan" type="text" placeholder="Employee Code (e.g. EMP-001)" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold">
                            </div>
                            <div class="relative group">
                                <Briefcase class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.jabatan" type="text" placeholder="Job Title / Jabatan" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold">
                            </div>
                            <div class="relative group">
                                <Users class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                                <input v-model="form.divisi" type="text" placeholder="Department / Divisi" class="w-full pl-12 pr-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 text-slate-700 dark:text-slate-200 transition-all font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex justify-end gap-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-8 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all uppercase tracking-widest"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-sm transition-all shadow-xl shadow-indigo-500/20 flex items-center disabled:opacity-50 uppercase tracking-widest"
                        >
                            <Save class="w-5 h-5 mr-3" />
                            {{ editMode ? 'Update' : 'Hire' }}
                        </button>
                    </div>
                </div>

                <!-- Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="col-span-full p-6 bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-3xl">
                    <p class="text-xs font-black text-red-600 uppercase tracking-widest mb-3">Please fix the following:</p>
                    <ul class="list-disc list-inside text-sm text-red-500 font-bold space-y-1">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>
            </form>
        </PremiumModal>
    </SuperAdminLayout>
</template>