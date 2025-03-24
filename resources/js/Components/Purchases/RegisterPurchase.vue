<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormPurchase from "@/Components/Purchases/FormPurchase.vue";

export default {
    name: "RegisterPurchase",
    components: {FormPurchase,  FullScreenModal, ButtonPrimary},
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
    emits: ['newPurchase', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async register() {
            try {
                const data = {
                    supplier_id: this.$refs.form.supplier.id,
                    items: this.$refs.form.items
                };

                const endpointRoute = '/' + this.authUser.level + '/purchase/register';
                const response = await axios.post(endpointRoute, data);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('newPurchase');
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
        title="Cadastrar novo pedido de compra"
        @click.self="this.$refs.form.resultsVisible = false"
    >
        <FormPurchase
            form-id="register-purchase"
            :prevent-enter-action="register"
            :auth-user="authUser"
            ref="form"
        />

    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/suppliers/register-supplier.css";
</style>
