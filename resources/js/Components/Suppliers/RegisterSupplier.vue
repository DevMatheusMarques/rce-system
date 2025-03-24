<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormSupplier from "@/Components/Suppliers/FormSupplier.vue";

export default {
    name: "RegisterSupplier",
    components: {FormSupplier, FormProduct, InputImage, FullScreenModal, ButtonPrimary},
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
    emits: ['newSupplier', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async register() {
            try {
                const formData = new FormData(document.getElementById("register-supplier"));
                const product = {
                    cnpj: formData.get("cnpj"),
                    corporate_name: formData.get("corporate_name"),
                    trade_name: formData.get("trade_name"),
                    email: formData.get("email"),
                    cep: formData.get("cep"),
                    phone: formData.get("phone"),
                    address_city: formData.get("address_city"),
                    address_state: formData.get("address_state"),
                };

                const endpointRoute = '/' + this.authUser.level + '/supplier/register';
                const response = await axios.post(endpointRoute, product);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('newSupplier');
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
        title="Cadastrar novo fornecedor"
    >
        <FormSupplier
            form-id="register-supplier"
            :prevent-enter-action="register"
            :auth-user="authUser"
        />

    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/suppliers/register-supplier.css";
</style>
