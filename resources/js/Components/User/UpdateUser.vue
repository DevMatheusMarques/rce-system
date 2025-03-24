<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormSupplier from "@/Components/Suppliers/FormSupplier.vue";
import FormUser from "@/Components/User/FormUser.vue";

export default {
    name: "UpdateUser",
    components: {FormUser, FormSupplier, FormProduct, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        user: {
            required: true,
        }
    }, emits: ['updateUser', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 1500);
    },
    methods: {
        async update() {
            try {
                const form = document.getElementById("update-user");
                const formData = new FormData(form);

                const user = {
                    name: formData.get("name") === '' ? null : formData.get("name"),
                    email: formData.get("email") === '' ? null : formData.get("email"),
                    level: formData.get("level") === '' ? null : formData.get("level"),
                    registration: formData.get("registration") === '' ? null : formData.get("registration"),
                    sector: formData.get("sector") === '' ? null : formData.get("sector"),
                    phone: formData.get("phone") === '' ? null : formData.get("phone"),
                    status: formData.get("status") === '' ? null : formData.get("status"),
                }

                let userUpdated = {};
                for (let key in user) {
                    if (this.user[key] !== user[key]) {
                        userUpdated[key] = user[key];
                    }
                }

                if (Object.keys(userUpdated).length === 0) {
                    this.toast.fire({
                        icon: 'warning',
                        title: 'Nada para atualizar'
                    });
                    return
                }
                const endpointRoute = '/' + this.authUser.level + '/user/update/' + this.user.id;

                const response = await axios.put(endpointRoute, userUpdated);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.$emit('updateUser');
                this.$emit('close');
            } catch
                (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
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
        }
    }
}
</script>

<template>
    <FullScreenModal
        :visible="visible"
        @close="$emit('close')"
        @save="update"
        :title="'Atualizar usuário - ' + user.name "
    >
        <FormUser
            form-id="update-user"
            :prevent-enter-action="update"
            :auth-user="authUser"
            :user-prop="user"
        />
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
