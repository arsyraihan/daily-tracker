<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
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

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-black text-slate-900 dark:text-white uppercase leading-none">
                <slot name="title" />
            </div>

            <div class="mt-8 text-sm text-slate-600 dark:text-slate-400 font-bold">
                <slot name="content" />
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-6 bg-slate-50 dark:bg-slate-800/50 text-end border-t border-slate-100 dark:border-slate-800">
            <slot name="footer" />
        </div>
    </Modal>
</template>
