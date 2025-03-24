<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormUser from "@/Components/User/FormUser.vue";

export default {
    name: "RegisterUser",
    components: {FormUser, FormProduct, FullScreenModal, ButtonPrimary},
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
    emits: ['newUser', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async register() {
            try {
                const formData = new FormData(document.getElementById("register-user"));
                const data = {
                    name: formData.get("name"),
                    email: formData.get("email"),
                    level: formData.get("level"),
                    registration: formData.get("registration"),
                    sector: formData.get("sector"),
                    phone: formData.get("phone"),
                    status: formData.get("status"),
                };

                const endpointRoute = '/' + this.authUser.level + '/user/register';
                const response = await axios.post(endpointRoute, data);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('newUser');
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
        title="Cadastrar novo usuário"
    >
        <FormUser
            form-id="register-user"
            :prevent-enter-action="register"
            :auth-user="authUser"
        />

    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/suppliers/register-supplier.css";
</style>
