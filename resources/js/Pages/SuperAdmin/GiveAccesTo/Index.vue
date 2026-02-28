<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import SuperAdminLayout from "@/Layouts/SuperAdminLayout.vue";

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
    permissions: {
        type: Array,
        default: () => [],
    },
});

// ─── Search & Pagination ──────────────────────────────────────────────────────
const search = ref("");
const currentPage = ref(1);
const perPage = 10;

const filteredUsers = computed(() => {
    if (!search.value) return props.users;
    const q = search.value.toLowerCase();
    return props.users.filter(
        (u) =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q) ||
            (u.kode_karyawan && u.kode_karyawan.toLowerCase().includes(q))
    );
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredUsers.value.length / perPage)),
);

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredUsers.value.slice(start, start + perPage);
});

function onSearch() {
    currentPage.value = 1;
}

// ─── Modal State ──────────────────────────────────────────────────────────────
const showModal = ref(false);
const editingUserId = ref(null);
const editingUserName = ref("");

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    roles: [],
    permissions: [],
});

function openEdit(user) {
    editingUserId.value = user.id;
    editingUserName.value = user.name;
    form.roles = user.roles.map(r => r.name);
    form.permissions = user.permissions.map(p => p.name);
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
}

function submitForm() {
    form.put(route("super-admin.give-access.update", editingUserId.value), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function getRoleClass(roleName) {
    const name = (roleName || "").toLowerCase();
    if (name === "superadmin")
        return "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:border-purple-800 dark:text-purple-300";
    if (name === "admin")
        return "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300";
    return "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300";
}

function userInitial(name) {
    return name ? name.charAt(0).toUpperCase() : "?";
}
</script>

<template>
    <SuperAdminLayout title="Access Management">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                        Access Management
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Atur peran dan izin akses untuk setiap pengguna.
                    </p>
                </div>
            </div>
        </template>

        <!-- Search Bar -->
        <div class="mb-5 flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg
                    class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                    />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau email..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    @input="onSearch"
                />
            </div>
            <span class="text-sm text-slate-400 dark:text-slate-500">
                {{ filteredUsers.length }} user ditemukan
            </span>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Roles</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in paginatedUsers" :key="user.id" class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shadow-sm border border-indigo-200/50 dark:border-indigo-700/50">
                                    {{ userInitial(user.name) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ user.name }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ user.email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="role in user.roles" :key="role.name" :class="['text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest border border-solid', getRoleClass(role.name)]">
                                    {{ role.name }}
                                </span>
                                <span v-if="user.roles.length === 0" class="text-[10px] text-slate-400 italic">No roles</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="openEdit(user)" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors" title="Edit Access">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <span class="text-xs text-slate-400">Halaman {{ currentPage }} dari {{ totalPages }}</span>
                <div class="flex gap-1.5">
                    <button :disabled="currentPage === 1" @click="currentPage--" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">← Prev</button>
                    <button :disabled="currentPage === totalPages" @click="currentPage++" class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Next →</button>
                </div>
            </div>
        </div>

        <!-- ─── Edit Access Modal ───────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />
                    <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Edit Akses: {{ editingUserName }}</h2>
                            <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="p-6 space-y-6 overflow-y-auto">
                            <!-- Roles -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wider">Pilih Role</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label v-for="role in roles" :key="role.id" class="relative flex items-center p-3 rounded-xl border cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-slate-700/50" :class="form.roles.includes(role.name) ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-slate-200 dark:border-slate-700'">
                                        <input type="checkbox" :value="role.name" v-model="form.roles" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 bg-white dark:bg-slate-700" />
                                        <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-200">{{ role.name }}</span>
                                    </label>
                                </div>
                            </div>

                            <hr class="border-slate-100 dark:border-slate-700" />

                            <!-- Permissions -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 uppercase tracking-wider">Izin Tambahan (Direct Permissions)</label>
                                <div class="grid grid-cols-1 gap-2">
                                    <label v-for="permission in permissions" :key="permission.id" class="flex items-center p-2.5 rounded-lg border border-slate-100 dark:border-slate-700/50 hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors">
                                        <input type="checkbox" :value="permission.name" v-model="form.permissions" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 bg-white dark:bg-slate-700" />
                                        <span class="ml-3 text-sm text-slate-600 dark:text-slate-300">{{ permission.name }}</span>
                                    </label>
                                </div>
                                <p v-if="permissions.length === 0" class="text-sm text-slate-400 italic">Tidak ada izin yang tersedia.</p>
                            </div>

                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Batal</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-xl shadow-md shadow-indigo-200 dark:shadow-none transition-all">
                                    {{ form.processing ? "Menyimpan..." : "Simpan Akses" }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

/* Custom scrollbar for form body */
form::-webkit-scrollbar { width: 5px; }
form::-webkit-scrollbar-track { background: transparent; }
form::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.dark form::-webkit-scrollbar-thumb { background: #334155; }
</style>
