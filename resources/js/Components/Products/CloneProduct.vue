<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import FormProduct from "@/Components/Products/FormProduct.vue";

export default {
    name: "CloneProduct",
    components: {FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        product: {
            required: true,
        }
    }, emits: ['newProduct', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    computed: {
        translateArrayCategoryName() {
            return {
                'toner': 'Toner',
                'paper': 'Papel',
                'form': 'Formulário',
                'cartridge': 'Cartucho',
                'ribbon': 'Fita'
            };
        }
    },
    methods: {
        async register() {
            try {
                const form = document.getElementById("clone-product");
                const formData = new FormData(form);
                const picture = document.getElementById('picture').files[0];
                const product = {
                    name: formData.get("name"),
                    description: formData.get("description"),
                    category: formData.get("category"),
                    minimum: formData.get("minimum"),
                    stock: formData.get("stock"),
                    status: formData.get("status"),
                    picture: picture !== '' ? picture : null
                }

                const endpointRoute = '/' + this.authUser.level + '/product/register';
                const response = await axios.post(endpointRoute, product, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                useRemoveFormatInputError();
                this.$emit('close');
                this.$emit('newProduct');
            } catch
                (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
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
        :title="'Clonando Produto'"
    >
        <FormProduct
            form-id="clone-product"
            :prevent-enter-action="register"
            :product="product"
        >
            <template #inputImage>
                <InputImage
                    :auth-user="authUser"
                    :self-management="false"
                />
            </template>
        </FormProduct>
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
