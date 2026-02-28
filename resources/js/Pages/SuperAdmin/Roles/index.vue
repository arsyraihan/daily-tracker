<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import SuperAdminLayout from "@/Layouts/SuperAdminLayout.vue";

const props = defineProps({
    roles: Array,
    permissions: Array,
});

// ─── Modal State ──────────────────────────────────────────────────────────────
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const showDeleteModal = ref(false);
const deleteTargetId = ref(null);
const deleteTargetName = ref("");

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    name: "",
    permissions: [],
});

function openCreate() {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(role) {
    isEditing.value = true;
    editingId.value = role.id;
    form.name = role.name;
    form.permissions = role.permissions
        ? role.permissions.map((p) => p.name)
        : [];
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.reset();
    form.clearErrors();
}

function submitForm() {
    if (isEditing.value) {
        form.put(route("super-admin.roles.update", editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("super-admin.roles.store"), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
function confirmDelete(role) {
    deleteTargetId.value = role.id;
    deleteTargetName.value = role.name;
    showDeleteModal.value = true;
}

function doDelete() {
    router.delete(route("super-admin.roles.destroy", deleteTargetId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function roleInitial(name) {
    return name ? name.charAt(0).toUpperCase() : "?";
}

function getRoleColor(name) {
    const n = (name || "").toLowerCase();
    if (n === "superadmin")
        return "bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300";
    if (n === "admin")
        return "bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300";
    if (n === "member" || n === "user")
        return "bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300";
    return "bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300";
}
</script>

<template>
    <SuperAdminLayout title="Roles Management">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-800 dark:text-white"
                    >
                        Roles Management
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Kelola peran dan permission aplikasi.
                    </p>
                </div>
                <button
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
                    Tambah Role
                </button>
            </div>
        </template>

        <!-- Table -->
        <div
            class="bg-white dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
        >
            <table class="w-full text-sm">
                <thead>
                    <tr
                        class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
                    >
                        <th
                            class="px-5 py-3.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs"
                        >
                            Nama Role
                        </th>
                        <th
                            class="px-5 py-3.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs"
                        >
                            Guard
                        </th>
                        <th
                            class="px-5 py-3.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs"
                        >
                            Permissions
                        </th>
                        <th
                            class="px-5 py-3.5 text-right font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="role in roles"
                        :key="role.id"
                        class="border-b border-slate-50 dark:border-slate-700/50 hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors"
                    >
                        <!-- Name -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs',
                                        getRoleColor(role.name),
                                    ]"
                                >
                                    {{ roleInitial(role.name) }}
                                </div>
                                <span
                                    class="font-medium text-slate-700 dark:text-slate-200 capitalize"
                                    >{{ role.name }}</span
                                >
                            </div>
                        </td>

                        <!-- Guard -->
                        <td
                            class="px-5 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-xs uppercase"
                        >
                            {{ role.guard_name }}
                        </td>

                        <!-- Permissions count -->
                        <td class="px-5 py-3.5">
                            <span
                                class="text-xs font-medium px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                            >
                                {{
                                    role.permissions
                                        ? role.permissions.length
                                        : 0
                                }}
                                permission
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEdit(role)"
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
                                    @click="confirmDelete(role)"
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
                    <tr v-if="!roles || roles.length === 0">
                        <td
                            colspan="4"
                            class="px-5 py-12 text-center text-slate-400 dark:text-slate-500"
                        >
                            Tidak ada role yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ─── Create / Edit Modal ───────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="closeModal"
                    />
                    <div
                        class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 max-h-[90vh] flex flex-col"
                    >
                        <!-- Header -->
                        <div
                            class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between flex-shrink-0"
                        >
                            <h2
                                class="text-lg font-semibold text-slate-800 dark:text-white"
                            >
                                {{
                                    isEditing ? "Edit Role" : "Tambah Role Baru"
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
                            class="px-6 py-5 space-y-5 overflow-y-auto"
                            @submit.prevent="submitForm"
                        >
                            <!-- Role Name -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                                    >Nama Role</label
                                >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g. editor, moderator"
                                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Permissions -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3"
                                    >Assign Permissions</label
                                >
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"
                                >
                                    <label
                                        v-for="permission in permissions"
                                        :key="permission.id"
                                        class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 cursor-pointer transition-all has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/60 dark:has-[:checked]:bg-indigo-900/20"
                                    >
                                        <input
                                            v-model="form.permissions"
                                            type="checkbox"
                                            :value="permission.name"
                                            class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                        />
                                        <span
                                            class="text-xs font-medium text-slate-600 dark:text-slate-300 uppercase tracking-tight"
                                        >
                                            {{
                                                permission.name.replace(
                                                    /-/g,
                                                    " ",
                                                )
                                            }}
                                        </span>
                                    </label>
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
                                              : "Tambah Role"
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
                            Hapus Role
                        </h3>
                        <p
                            class="text-sm text-slate-500 dark:text-slate-400 mb-5"
                        >
                            Yakin ingin menghapus role
                            <strong
                                class="text-slate-700 dark:text-slate-200"
                                >{{ deleteTargetName }}</strong
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
