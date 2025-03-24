<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormPurchase from "@/Components/Purchases/FormPurchase.vue";
import FormOrder from "@/Components/Order/FormOrder.vue";

export default {
    name: "ClonePurchase",
    components: {FormOrder, FormPurchase, FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        order: {
            required: true,
        }
    }, emits: ['newOrder', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async register() {
            try {
                const form = new FormData(document.getElementById("clone-order"));
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
        }
    }
}
</script>

<template>
    <FullScreenModal
        :visible="visible"
        @close="$emit('close')"
        @save="register"
        :title="'Clonando pedido de saída'"
    >
        <FormOrder
            form-id="clone-order"
            :prevent-enter-action="register"
            :auth-user="authUser"
            :order-prop="order"
            ref="form"
        />
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
