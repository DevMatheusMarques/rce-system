<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormOrder from "@/Components/Order/FormOrder.vue";

export default {
    name: "RegisterOrder",
    components: {FormOrder, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        product: Object
    },
    data() {
        return {
            toast: null
        };
    },
    emits: ['newOrder', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async register() {
            try {
                const form = new FormData(document.getElementById("register-order"));
                const data = {
                    requester_user_id: this.$refs.form.user.id,
                    proof_file: form.get('proof_file'),
                    internal_information: form.get('internal_information'),
                    items: this.$refs.form.items,
                };

                const endpointRoute = '/' + this.authUser.level + '/order/register';
                const response = await axios.post(endpointRoute, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('newOrder');
                this.$emit('close')
            } catch (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds);
                } else {
                    useRemoveFormatInputError();
                }
                this.errors = errors.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        },
    }
}
</script>

<template>
    <FullScreenModal
        :visible="visible"
        @close="$emit('close')"
        @save="register"
        title="Registrar nova saída de estoque"
    >
        <FormOrder
            form-id="register-order"
            :prevent-enter-action="register"
            :auth-user="authUser"
            ref="form"
        >

        </FormOrder>

    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/suppliers/register-supplier.css";
</style>
