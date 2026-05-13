<template>
    <div class="flex space-x-2">
        <button v-if="request.status === 'pending'" @click="approveRequest" class="text-green-600 hover:text-green-900 text-sm">Approve</button>
        <button v-if="request.status === 'pending'" @click="showRejectModal = true" class="text-red-600 hover:text-red-900 text-sm">Reject</button>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Reject Leave Request</h3>
                <textarea v-model="rejectionReason" placeholder="Reason for rejection" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                <p v-if="errors.rejection_reason" class="text-red-500 text-xs mt-1">{{ errors.rejection_reason[0] }}</p>
                <div class="mt-4 flex justify-end space-x-2">
                    <button @click="showRejectModal = false" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</button>
                    <button @click="rejectRequest" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Confirm Reject</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api';

const props = defineProps({
    request: Object,
});

const emit = defineEmits(['request-updated']);

const showRejectModal = ref(false);
const rejectionReason = ref('');
const errors = ref({});

const approveRequest = async () => {
    if (confirm('Are you sure you want to approve this leave request?')) {
        try {
            await api.patch(`/leave/${props.request.id}/approve`);
            emit('request-updated'); // Notify parent to refresh list
        } catch (err) {
            console.error('Error approving request:', err);
            alert('Failed to approve leave request.');
        }
    }
};

const rejectRequest = async () => {
    errors.value = {};
    try {
        await api.patch(`/leave/${props.request.id}/reject`, { rejection_reason: rejectionReason.value });
        showRejectModal.value = false;
        rejectionReason.value = '';
        emit('request-updated'); // Notify parent to refresh list
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
        } else {
            console.error('Error rejecting request:', err);
            alert('Failed to reject leave request.');
        }
    }
};
</script>