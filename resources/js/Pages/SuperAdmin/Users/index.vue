<script setup>
import { ref, computed } from "vue";
import { useForm, router, Link } from "@inertiajs/vue3";
import SuperAdminLayout from "@/Layouts/SuperAdminLayout.vue";
import { 
    Users, Plus, Search, Pencil, Trash2, X, 
    Briefcase, Building, Shield, UserCheck, UserMinus,
    ArrowRight, ChevronLeft, ChevronRight, Hash, Mail
} from "lucide-vue-next";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    users: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    divisi: { type: Array, default: () => [] },
    jabatan: { type: Array, default: () => [] },
    availableSupervisors: { type: Array, default: () => [] },
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
            u.name?.toLowerCase().includes(q) ||
            u.email?.toLowerCase().includes(q) ||
            u.kode_karyawan?.toLowerCase().includes(q)
    );
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)));
const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredUsers.value.slice(start, start + perPage);
});

// ─── Modal State ──────────────────────────────────────────────────────────────
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "",
    kode_karyawan: "",
    divisi_id: "",
    jabatan_id: "",
    atasan_id: "",
    status: "aktif",
});

function openCreate() {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.status = "aktif";
    form.clearErrors();
    showModal.value = true;
}

function openEdit(user) {
    isEditing.value = true;
    editingId.value = user.id;
    form.clearErrors();
    form.name = user.name || "";
    form.email = user.email || "";
    form.password = "";
    form.password_confirmation = "";
    form.role = user.roles && user.roles.length ? user.roles[0].name : "";
    form.kode_karyawan = user.kode_karyawan || "";
    form.divisi_id = user.divisi_id || "";
    form.jabatan_id = user.jabatan_id || "";
    form.atasan_id = user.atasan_id || "";
    form.status = user.status || "aktif";
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submitForm() {
    if (isEditing.value) {
        form.put(route("super-admin.users.update", editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("super-admin.users.store"), {
            onSuccess: () => closeModal(),
        });
    }
}

function deleteItem(id) {
    if (confirm("Apakah Anda yakin ingin menghapus user ini?")) {
        router.delete(route("super-admin.users.destroy", id));
    }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function getRoleClass(roleName) {
    const name = (roleName || "").toLowerCase();
    if (name === "superadmin") return "bg-purple-50 text-purple-700 border-purple-200";
    if (name === "supervisor" || name === "manager") return "bg-blue-50 text-blue-700 border-blue-200";
    return "bg-slate-50 text-slate-600 border-slate-200";
}
</script>

<template>
    <SuperAdminLayout title="Karyawan Management">
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 py-8">
                <div class="space-y-1">
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                        Data <span class="text-indigo-600 dark:text-indigo-400">Karyawan</span>
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 font-bold text-sm italic">Pusat pengelolaan data induk karyawan dan organisasi.</p>
                </div>
                
                <button 
                    @click="openCreate"
                    class="group flex items-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[2rem] transition-all shadow-xl shadow-indigo-500/30 hover:scale-105 active:scale-95"
                >
                    <Plus class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform" />
                    <span class="font-black text-xs uppercase tracking-widest">Registrasi Karyawan</span>
                </button>
            </div>
        </template>

        <!-- Stats Overview Placeholder (Bisa ditambah nanti) -->

        <!-- Filters & Search -->
        <div class="mb-8 flex flex-col sm:flex-row items-center gap-4 bg-white dark:bg-slate-900 p-4 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="relative flex-1 w-full pl-2">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                <input 
                    v-model="search"
                    type="text" 
                    placeholder="Cari Nama, Email, atau NIK..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-2xl py-3 pl-12 pr-4 focus:ring-2 focus:ring-indigo-500 text-sm font-bold tracking-tight"
                >
            </div>
            <div class="flex items-center gap-2 pr-2">
                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ filteredUsers.length }} Users Registered</span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden mb-12">
            <div class="overflow-x-auto overflow-y-visible">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Identitas</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Penempatan</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status & Role</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all group">
                            <!-- Identity -->
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                                        <span class="font-black text-xl italic">{{ user.name.charAt(0) }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none">{{ user.name }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded-md font-black tracking-widest uppercase">NIK {{ user.kode_karyawan || '---' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-slate-400">
                                            <Mail class="w-3 h-3" />
                                            <span class="text-xs font-bold">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Organization -->
                            <td class="px-8 py-6">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                                            <Building class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                        </div>
                                        <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">{{ user.divisi?.nama || 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                                            <Briefcase class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">{{ user.jabatan?.nama || 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Access & Status -->
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <span v-if="user.roles[0]" :class="['text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border', getRoleClass(user.roles[0].name)]">
                                            {{ user.roles[0].name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div :class="['w-2 h-2 rounded-full', user.status === 'aktif' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></div>
                                        <span :class="['text-[10px] font-black uppercase tracking-[0.2em]', user.status === 'aktif' ? 'text-emerald-600' : 'text-rose-600']">
                                            System {{ user.status }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button 
                                        @click="openEdit(user)"
                                        class="w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 rounded-xl transition-all shadow-sm"
                                        title="Edit Profil"
                                    >
                                        <Pencil class="w-4.5 h-4.5" />
                                    </button>
                                    <button 
                                        @click="deleteItem(user.id)"
                                        class="w-10 h-10 flex items-center justify-center bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/40 rounded-xl transition-all shadow-sm"
                                        title="Terminasi"
                                    >
                                        <Trash2 class="w-4.5 h-4.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Custom Pagination -->
            <div v-if="totalPages > 1" class="px-8 py-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-800/20 flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Page {{ currentPage }} / {{ totalPages }}</span>
                <div class="flex gap-2">
                    <button 
                        @click="currentPage > 1 && (currentPage--)"
                        :class="['p-2 rounded-xl transition-all', currentPage === 1 ? 'text-slate-200 cursor-not-allowed' : 'text-slate-600 hover:bg-white dark:hover:bg-slate-800 shadow-sm']"
                    >
                        <ChevronLeft class="w-5 h-5" />
                    </button>
                    <button 
                        @click="currentPage < totalPages && (currentPage++)"
                        :class="['p-2 rounded-xl transition-all', currentPage === totalPages ? 'text-slate-200 cursor-not-allowed' : 'text-slate-600 hover:bg-white dark:hover:bg-slate-800 shadow-sm']"
                    >
                        <ChevronRight class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal" max-width="2xl">
            <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-[2.5rem]">
                <!-- Modal Backdrop Decoration (Compact) -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl -mr-16 -mt-16"></div>

                <div class="p-6 sm:p-8 relative z-10">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 rotate-3">
                                <UserCheck class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight leading-none">
                                    {{ isEditing ? 'Update User' : 'New User' }}
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Sistem Manajemen Data Karyawan</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <X class="w-4 h-4 text-slate-400" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Section: Identity -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-px bg-slate-100 dark:bg-slate-800 flex-1"></div>
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Identity Details</span>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <InputLabel value="Nama Lengkap" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <TextInput v-model="form.name" type="text" class="w-full text-xs" placeholder="Full Name" required />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Email Address" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <TextInput v-model="form.email" type="email" class="w-full text-xs" placeholder="email@company.com" required />
                                    <InputError :message="form.errors.email" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <InputLabel value="NIK / Employee ID" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <TextInput v-model="form.kode_karyawan" type="text" class="w-full text-xs" placeholder="Ex: KRW-001" />
                                    <InputError :message="form.errors.kode_karyawan" />
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="System Role" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <select v-model="form.role" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2 px-3 focus:ring-2 focus:ring-indigo-500 text-xs font-bold leading-tight">
                                        <option value="">-- Choose Access --</option>
                                        <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Organization -->
                        <div class="space-y-4 pt-2">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-px bg-slate-100 dark:bg-slate-800 flex-1"></div>
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Organization Placement</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <InputLabel value="Divisi" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <select v-model="form.divisi_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2 px-3 focus:ring-2 focus:ring-indigo-500 text-xs font-bold">
                                        <option value="">-- Pilih Divisi --</option>
                                        <option v-for="d in divisi" :key="d.id" :value="d.id">{{ d.nama }}</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Jabatan" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                    <select v-model="form.jabatan_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2 px-3 focus:ring-2 focus:ring-indigo-500 text-xs font-bold">
                                        <option value="">-- Pilih Jabatan --</option>
                                        <option v-for="j in jabatan" :key="j.id" :value="j.id">{{ j.nama }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <InputLabel value="Atasan Langsung" class="text-[9px] font-black uppercase text-slate-400 ml-1" />
                                <select v-model="form.atasan_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl py-2 px-3 focus:ring-2 focus:ring-indigo-500 text-xs font-bold">
                                    <option value="">-- No Supervisor (Top Level) --</option>
                                    <option v-for="s in availableSupervisors" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Section: Status & Security -->
                        <div class="bg-slate-50 dark:bg-slate-800/30 p-4 rounded-3xl border border-slate-100 dark:border-slate-800/50 space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <span class="text-[9px] font-black uppercase text-slate-400 tracking-widest">User Status</span>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" value="aktif" v-model="form.status" class="hidden" />
                                        <div :class="['w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all', form.status === 'aktif' ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300 dark:border-slate-600']">
                                            <div v-if="form.status === 'aktif'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                        <span :class="['text-[10px] font-black uppercase tracking-widest', form.status === 'aktif' ? 'text-emerald-600' : 'text-slate-400']">Aktif</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" value="nonaktif" v-model="form.status" class="hidden" />
                                        <div :class="['w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all', form.status === 'nonaktif' ? 'border-rose-500 bg-rose-500' : 'border-slate-300 dark:border-slate-600']">
                                            <div v-if="form.status === 'nonaktif'" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>
                                        <span :class="['text-[10px] font-black uppercase tracking-widest', form.status === 'nonaktif' ? 'text-rose-600' : 'text-slate-400']">Non-Aktif</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                                <div class="space-y-1">
                                    <InputLabel value="Password" class="text-[9px] font-black uppercase text-indigo-400 ml-1" />
                                    <TextInput v-model="form.password" type="password" class="w-full text-xs" placeholder="••••••••" />
                                </div>
                                <div class="space-y-1">
                                    <InputLabel value="Repeat Password" class="text-[9px] font-black uppercase text-indigo-400 ml-1" />
                                    <TextInput v-model="form.password_confirmation" type="password" class="w-full text-xs" placeholder="••••••••" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button 
                                type="button"
                                @click="closeModal"
                                class="px-5 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors"
                            >
                                Cancel
                            </button>
                            <PrimaryButton 
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 text-[10px] font-black uppercase tracking-[0.1em]"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ isEditing ? 'Save Changes' : 'Register User' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    </SuperAdminLayout>
</template>
