<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormSupplier from "@/Components/Suppliers/FormSupplier.vue";
import FormPurchase from "@/Components/Purchases/FormPurchase.vue";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormOrder from "@/Components/Order/FormOrder.vue";

export default {
    name: "UpdateOrder",
    components: {FormOrder, FormPurchase, FormSupplier, FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        order: {
            required: true,
        }
    }, emits: ['updateOrder', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async update() {
            try {
                const form = new FormData();
                const file = this.$refs.form.inputFile.files[0] ?? null;

                if (file !== null) {
                    form.append('proof_file', this.$refs.form.inputFile.files[0] ?? null);
                }

                const internalInformation = document.getElementById('internal_information').value;

                form.append('requester_user_id', this.$refs.form.user.id);
                form.append('internal_information', internalInformation ?? '');

                this.$refs.form.items.forEach((item, index) => {
                    form.append(`items[${index}][product_id]`, item.product_id);
                    form.append(`items[${index}][product_quantity]`, item.product_quantity);
                    form.append(`items[${index}][status]`, item.status);
                });

                const endpointRoute = '/' + this.authUser.level + '/order/update/' + this.order.id;
                const response = await axios.post(endpointRoute, form, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('updateOrder');
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
        @save="update"
        :title="'Atualizar saída de estoque - ' + order.id "
    >
        <FormOrder
            form-id="update-order"
            :prevent-enter-action="update"
            :auth-user="authUser"
            :order-prop="order"
            ref="form"
        />
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
