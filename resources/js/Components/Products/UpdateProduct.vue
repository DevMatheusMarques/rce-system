<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import InputImage from "@/Components/InputImage.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import FormProduct from "@/Components/Products/FormProduct.vue";

export default {
    name: "UpdateProduct",
    components: {FormProduct, InputImage, FullScreenModal, ButtonPrimary},
    props: {
        visible: Boolean,
        title: String,
        authUser: Object,
        product: {
            required: true,
        }
    }, emits: ['updateProduct', 'close'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        async update() {
            try {
                const form = document.getElementById("update-product");
                const formData = new FormData(form);
                let picture = document.getElementById('picture').files[0]; //foto selecionada pelo input

                //foto que aparece no front, se não tiver nenhuma, é a URL da foto default, se estiver uma foto, é a url da foto,
                //se eu estiver adicionando uma nova, é um blob, ou seja, a foto temporária
                let urlCurrentPicture = document.querySelector('.info__img--img').src;

                //se eu tiver no seeder foto com nome que tenha espaço e caracter especiais
                const other = decodeURIComponent(urlCurrentPicture);
                let parsedUrl = new URL(other);
                let currentUrlPathName = parsedUrl.pathname;

                //verifica se houve alteração na foto, ou seja, se removi a que já tem, ou adicionei uma nova
                if (currentUrlPathName === this.product.picture_path) {
                    picture = currentUrlPathName
                }

                //se minha foto for default, picture recebe undefined
                if (currentUrlPathName === '/assets/img/no-image.jpg') {
                    picture = ''
                }

                const product = {
                    name: formData.get("name") === '' ? null : formData.get("name"),
                    description: formData.get("description") === '' ? null : formData.get("description"),
                    category: formData.get("category") === '' ? null : formData.get("category"),
                    status: formData.get("status") === '' ? null : formData.get("status"),
                    minimum: isNaN(parseInt(formData.get("minimum"))) ? null : parseInt(formData.get("minimum")),
                    stock: isNaN(parseInt(formData.get("stock"))) ? null : parseInt(formData.get("stock")),
                    picture_path: picture
                }
                if (this.product.picture_path === null) {
                    //para que na comparação, não fique null === ''
                    this.product.picture_path = '';
                }

                let productUpdated = {};
                //verifica se no meu produto atual, possui alguma diferença do formulário, ou seja,
                //verifica se teve alguma altereção no produto, pra evitar uma requisição desnecessária
                for (let key in product) {
                    if (this.product[key] !== product[key]) {
                        productUpdated[key] = product[key];
                    }
                }

                if ('picture_path' in productUpdated) {
                    delete productUpdated.picture_path;
                    productUpdated.picture = picture;
                }

                if (Object.keys(productUpdated).length === 0) {
                    this.toast.fire({
                        icon: 'warning',
                        title: 'Nada para atualizar'
                    });
                    return
                }

                const endpointRoute = '/' + this.authUser.level + '/product/update/' + this.product.id;

                const response = await axios.post(endpointRoute, productUpdated, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.$emit('updateProduct');
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
        }
        ,
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
        :title="'Atualizar Produto - ' + product.name "
    >
        <FormProduct
            form-id="update-product"
            :prevent-enter-action="update"
            :product="product"
        >
            <template #inputImage>
                <InputImage
                    :auth-user="authUser"
                    :self-management="false"
                    :image-selected="product.picture_path"
                />
            </template>
        </FormProduct>
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/products/register-product.css";
</style>
