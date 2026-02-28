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
            (u.kode_karyawan && u.kode_karyawan.toLowerCase().includes(q)) ||
            (u.jabatan && u.jabatan.toLowerCase().includes(q)) ||
            (u.divisi && u.divisi.toLowerCase().includes(q)),
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
const isEditing = ref(false);
const editingId = ref(null);
const showDeleteModal = ref(false);
const deleteTargetId = ref(null);
const deleteTargetName = ref("");

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    kode_karyawan: "",
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "",
    jabatan: "",
    divisi: "",
    tanggal_masuk: "",
    status: "active",
});

function openCreate() {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(user) {
    isEditing.value = true;
    editingId.value = user.id;
    form.reset();
    form.clearErrors();
    form.kode_karyawan = user.kode_karyawan || "";
    form.name = user.name || "";
    form.email = user.email || "";
    form.password = "";
    form.password_confirmation = "";
    form.role = user.roles && user.roles.length ? user.roles[0].name : "";
    form.jabatan = user.jabatan || "";
    form.divisi = user.divisi || "";
    form.tanggal_masuk = user.tanggal_masuk || "";
    form.status = user.status || "active";
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    form.clearErrors();
}

function submitForm() {
    if (isEditing.value) {
        form.put(route("super-admin.users.update", editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("super-admin.users.store"), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
function confirmDelete(user) {
    deleteTargetId.value = user.id;
    deleteTargetName.value = user.name;
    showDeleteModal.value = true;
}

function doDelete() {
    router.delete(route("super-admin.users.destroy", deleteTargetId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function getRoleClass(roleName) {
    const name = (roleName || "").toLowerCase();
    if (name === "superadmin")
        return "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:border-purple-800 dark:text-purple-300";
    if (name === "admin")
        return "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300";
    if (name === "member" || name === "user")
        return "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300";
    return "bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300";
}

function userInitial(name) {
    return name ? name.charAt(0).toUpperCase() : "?";
}

function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}
</script>

<template>
    <SuperAdminLayout title="User Management">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-800 dark:text-white"
                    >
                        User Management
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola semua pengguna dan peran mereka.
                    </p>
                </div>
                <button
                    id="btn-add-user"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Tambah User
                </button>
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
                    id="search-users"
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau email..."
                    class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                    @input="onSearch"
                />
            </div>
            <span
                class="text-sm text-slate-400 dark:text-slate-500 whitespace-nowrap"
            >
                {{ filteredUsers.length }} user ditemukan
            </span>
        </div>

        <!-- Table -->
        <div
            class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden overflow-x-auto"
        >
            <table class="w-full text-sm">
                <thead>
                    <tr
                        class="border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50"
                    >
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Nama
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Email
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Kode Karyawan
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Jabatan
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Divisi
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Tgl Masuk
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Role
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest whitespace-nowrap"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in paginatedUsers"
                        :key="user.id"
                        class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors"
                    >
                        <!-- Name -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-200 dark:from-indigo-900/60 dark:to-indigo-800/60 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-xs shadow-sm border border-indigo-200/50 dark:border-indigo-700/50 shrink-0"
                                >
                                    {{ userInitial(user.name) }}
                                </div>
                                <span
                                    class="font-bold text-slate-800 dark:text-slate-200 text-sm tracking-tight truncate max-w-[150px]"
                                >
                                    {{ user.name }}
                                </span>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="px-6 py-4 whitespace-nowrap overflow-hidden">
                            <span
                                class="text-sm text-slate-500 dark:text-slate-400 truncate block max-w-[180px]"
                                :title="user.email"
                            >
                                {{ user.email }}
                            </span>
                        </td>

                        <!-- Kode Karyawan -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 font-mono text-[10px] font-bold tracking-widest border border-slate-200 dark:border-slate-600 leading-none h-5 flex items-center w-fit"
                            >
                                {{ user.kode_karyawan || "-" }}
                            </span>
                        </td>

                        <!-- Jabatan -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="text-sm text-slate-700 dark:text-slate-300 font-medium"
                            >
                                {{ user.jabatan || "-" }}
                            </span>
                        </td>

                        <!-- Divisi -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ user.divisi || "-" }}
                            </span>
                        </td>

                        <!-- Tanggal Masuk -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div
                                class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-3.5 h-3.5 text-slate-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span class="font-medium">{{
                                    formatDate(user.tanggal_masuk)
                                }}</span>
                            </div>
                        </td>

                        <!-- Role -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <template
                                v-if="user.roles && user.roles.length"
                            >
                                <span
                                    :class="[
                                        'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest border border-solid',
                                        getRoleClass(user.roles[0].name),
                                    ]"
                                >
                                    {{ user.roles[0].name }}
                                </span>
                            </template>
                            <span
                                v-else
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest border border-solid bg-slate-50 text-slate-500 border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400"
                            >
                                No role
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'text-[10px] font-bold px-2 py-0.5 rounded border uppercase flex items-center gap-1.5 w-fit',
                                    user.status === 'active'
                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800'
                                        : 'bg-red-50 text-red-600 border-red-200 dark:bg-red-900/20 dark:border-red-800',
                                ]"
                            >
                                <span
                                    :class="[
                                        'w-1.5 h-1.5 rounded-full',
                                        user.status === 'active'
                                            ? 'bg-emerald-500 dark:bg-emerald-400'
                                            : 'bg-red-500 dark:bg-red-400',
                                    ]"
                                ></span>
                                {{ user.status || "active" }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    :id="'btn-edit-' + user.id"
                                    @click="openEdit(user)"
                                    title="Edit"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                                <button
                                    :id="'btn-delete-' + user.id"
                                    @click="confirmDelete(user)"
                                    title="Hapus"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-if="paginatedUsers.length === 0">
                        <td
                            colspan="4"
                            class="px-5 py-12 text-center text-slate-400 dark:text-slate-500"
                        >
                            Tidak ada user yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div
                v-if="totalPages > 1"
                class="px-5 py-3.5 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between"
            >
                <span class="text-xs text-slate-400">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                </span>
                <div class="flex gap-1.5">
                    <button
                        :disabled="currentPage === 1"
                        class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                        @click="currentPage--"
                    >
                        ← Prev
                    </button>
                    <button
                        :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                        @click="currentPage++"
                    >
                        Next →
                    </button>
                </div>
            </div>
        </div>

        <!-- ─── Create / Edit Modal ───────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="closeModal"
                    />

                    <div
                        class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 flex flex-col max-h-[90vh]"
                    >
                        <!-- Header -->
                        <div
                            class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between"
                        >
                            <h2
                                class="text-lg font-semibold text-slate-800 dark:text-white"
                            >
                                {{
                                    isEditing ? "Edit User" : "Tambah User Baru"
                                }}
                            </h2>
                            <button
                                @click="closeModal"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Body -->
                        <form
                            class="px-6 py-5 space-y-4 overflow-y-auto"
                            @submit.prevent="submitForm"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Kode Karyawan -->
                                <div>
                                    <label
                                        for="field-kode"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Kode Karyawan
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="field-kode"
                                        v-model="form.kode_karyawan"
                                        type="text"
                                        placeholder="MISAL: USR-123"
                                        required
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition uppercase"
                                    />
                                    <p
                                        v-if="form.errors.kode_karyawan"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.kode_karyawan }}
                                    </p>
                                </div>

                                <!-- Name -->
                                <div>
                                    <label
                                        for="field-name"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Nama Lengkap
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="field-name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Nama lengkap"
                                        required
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label
                                        for="field-email"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Email
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="field-email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="email@contoh.com"
                                        required
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                    <p
                                        v-if="form.errors.email"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <!-- Role -->
                                <div>
                                    <label
                                        for="field-role"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Peran Akses
                                    </label>
                                    <select
                                        id="field-role"
                                        v-model="form.role"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    >
                                        <option value="">
                                            -- Tanpa Peran --
                                        </option>
                                        <option
                                            v-for="role in roles"
                                            :key="role.id"
                                            :value="role.name"
                                        >
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.role"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.role }}
                                    </p>
                                </div>

                                <!-- Divisi -->
                                <div>
                                    <label
                                        for="field-divisi"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Divisi
                                    </label>
                                    <input
                                        id="field-divisi"
                                        v-model="form.divisi"
                                        type="text"
                                        placeholder="e.g. IT, HRD, Finance"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                </div>

                                <!-- Jabatan -->
                                <div>
                                    <label
                                        for="field-jabatan"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Jabatan
                                    </label>
                                    <input
                                        id="field-jabatan"
                                        v-model="form.jabatan"
                                        type="text"
                                        placeholder="e.g. Manager, Staff"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                </div>

                                <!-- Tanggal Masuk -->
                                <div>
                                    <label
                                        for="field-tanggal"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Tanggal Masuk
                                    </label>
                                    <input
                                        id="field-tanggal"
                                        v-model="form.tanggal_masuk"
                                        type="date"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition [color-scheme:light] dark:[color-scheme:dark]"
                                    />
                                </div>

                                <!-- Status -->
                                <div>
                                    <label
                                        for="field-status"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Status Karyawan
                                    </label>
                                    <select
                                        id="field-status"
                                        v-model="form.status"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    >
                                        <option value="active">Active</option>
                                        <option value="inactive">
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <hr
                                class="border-slate-200 dark:border-slate-700 my-4"
                            />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Password -->
                                <div>
                                    <label
                                        for="field-password"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Password
                                        <span
                                            v-if="!isEditing"
                                            class="text-red-500"
                                            >*</span
                                        >
                                        <span
                                            v-else
                                            class="text-slate-400 font-normal text-xs"
                                            >(kosongkan jika tetap)</span
                                        >
                                    </label>
                                    <input
                                        id="field-password"
                                        v-model="form.password"
                                        type="password"
                                        placeholder="••••••••"
                                        :required="!isEditing"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                    <p
                                        v-if="form.errors.password"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <!-- Password Confirm -->
                                <div>
                                    <label
                                        for="field-password-confirm"
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >
                                        Konfirmasi Password
                                        <span
                                            v-if="!isEditing"
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        id="field-password-confirm"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        placeholder="••••••••"
                                        :required="!isEditing"
                                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                    />
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-5 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-xl shadow-sm transition-colors"
                                >
                                    {{
                                        form.processing
                                            ? "Menyimpan..."
                                            : isEditing
                                              ? "Simpan Perubahan"
                                              : "Tambah User"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ─── Delete Confirm Modal ──────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div
                    v-if="showDeleteModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="showDeleteModal = false"
                    />
                    <div
                        class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-6 text-center"
                    >
                        <div
                            class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-red-600 dark:text-red-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="text-base font-semibold text-slate-800 dark:text-white mb-1"
                        >
                            Hapus User
                        </h3>
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400 mb-5"
                        >
                            Yakin ingin menghapus
                            <strong class="text-slate-700 dark:text-slate-200">
                                {{ deleteTargetName }} </strong
                            >? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div class="flex gap-3 justify-center">
                            <button
                                @click="showDeleteModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                @click="doDelete"
                                class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-sm transition-colors"
                            >
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>
