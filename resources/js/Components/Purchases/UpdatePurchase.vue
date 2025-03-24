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

export default {
    name: "UpdatePurchase",
    components: {FormPurchase, FormSupplier, FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        purchase: {
            required: true,
        }
    }, emits: ['updatePurchase', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async update() {
            try {
                const oldStatus = this.purchase.status;
                const newStatus = this.$refs.form.purchaseStatus;
                const items = this.$refs.form.items;

                const data = {
                    supplier_id: this.$refs.form.supplier.id,
                    status: newStatus === oldStatus ? null : newStatus,
                    items: items
                };


                const endpointRoute = '/' + this.authUser.level + '/purchase/update/' + this.purchase.id;
                const response = await axios.put(endpointRoute, data);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('updatePurchase');
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
        :title="'Atualizar pedido de compra - ' + purchase.id "
    >
        <FormPurchase
            :purchase-prop="purchase"
            form-id="update-purchase"
            :prevent-enter-action="update"
            :auth-user="authUser"
            ref="form"
        />
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
