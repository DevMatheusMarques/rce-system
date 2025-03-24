<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import FormProduct from "@/Components/Products/FormProduct.vue";
import FormSupplier from "@/Components/Suppliers/FormSupplier.vue";

export default {
    name: "UpdateSupplier",
    components: {FormSupplier, FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        supplier: {
            required: true,
        }
    }, emits: ['updateSupplier', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async update() {
            try {
                const form = document.getElementById("update-supplier");
                const formData = new FormData(form);

                const supplier = {
                    cnpj: formData.get("cnpj") === '' ? null : formData.get("cnpj"),
                    corporate_name: formData.get("corporate_name") === '' ? null : formData.get("corporate_name"),
                    trade_name: formData.get("trade_name") === '' ? null : formData.get("trade_name"),
                    email: formData.get("email") === '' ? null : formData.get("email"),
                    cep: formData.get("cep") === '' ? null : formData.get("cep"),
                    phone: formData.get("phone") === '' ? null : formData.get("phone"),
                    address_city: formData.get("address_city") === '' ? null : formData.get("address_city"),
                    address_state: formData.get("address_state") === '' ? null : formData.get("address_state"),
                }
                let supplierUpdated = {};
                for (let key in supplier) {
                    if (this.supplier[key] !== supplier[key]) {
                        supplierUpdated[key] = supplier[key];
                    }
                }

                if (Object.keys(supplierUpdated).length === 0) {
                    this.toast.fire({
                        icon: 'warning',
                        title: 'Nada para atualizar'
                    });
                    return
                }
                const endpointRoute = '/' + this.authUser.level + '/supplier/update/' + this.supplier.id;

                const response = await axios.put(endpointRoute, supplierUpdated);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.$emit('updateSupplier');
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
        :title="'Atualizar fornecedor - ' + supplier.cnpj "
    >
        <FormSupplier
            form-id="update-supplier"
            :prevent-enter-action="update"
            :auth-user="authUser"
            :supplier-prop="supplier"
        />
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
