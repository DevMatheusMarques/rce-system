<script>
import InputImage from "@/Components/InputImage.vue";

export default {
    name: "FormProduct",
    components: { InputImage },
    props: {
        preventEnterAction: {
            type: Function,
            required: true
        },
        product: {
            type: Object,
            default: () => ({}),
            required: false
        },
        formId: {
            type: String,
            required: true
        }
    },
    methods: {
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        }
    },
    computed: {
        translateArrayCategoryName() {
            return {
                'toner': 'Toner',
                'paper': 'Papel',
                'form': 'Formulário',
                'cartridge': 'Cartucho',
                'ribbon': 'Fita',
                'desk': 'Escritório',
                'others': 'Outros',
            };
        },
        productStatus() {
            return this.product.status || null;
        },
        productCategory() {
            return this.product.category || null;
        }
    }
}
</script>

<template>
    <div class="form__product">
        <form :id="formId">
            <div class="form__content">
                <div class="form__col">
                    <div class="input__item">
                        <label for="name" class="form__label">Nome</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="text" class="form__input" name="name" id="name"
                               :value="product.name ?? ''"
                        >
                    </div>
                    <div class="input__item">
                        <label for="description" class="form__label">Descrição</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="text" class="form__input" name="description" id="description"
                               :value="product.description ?? ''"
                        >
                    </div>
                    <div class="input__item">
                        <label for="category" class="form__label">Categoria</label>
                        <select class="form__input" name="category" id="category"
                                @change="removeStyleInputError"
                                @keydown.prevent.enter="preventEnterAction"
                                v-model="productCategory"
                        >
                            <option v-for="(category, index) in translateArrayCategoryName" :key="index" :value="index">{{ category }}</option>
                        </select>
                    </div>
                    <div class="input__item">
                        <label for="minimum" class="form__label">Mínimo</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="number" class="form__input" name="minimum" id="minimum"
                               :value="product.minimum ?? ''"
                        >
                    </div>
                    <div class="input__item">
                        <label for="stock" class="form__label">Estoque</label>
                        <input @keydown="removeStyleInputError"
                               @keydown.prevent.enter="preventEnterAction"
                               type="number" class="form__input" name="stock" id="stock"
                               :value="product.stock ?? ''"
                        >
                    </div>
                    <div class="input__item">
                        <label for="status" class="form__label">Status</label>
                        <select class="form__input" name="status" id="status"
                                @change="removeStyleInputError"
                                @keydown.prevent.enter="preventEnterAction"
                                v-model="productStatus"
                        >
                            <option value="active">Ativo</option>
                            <option value="inactive">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="form__col">
                    <div class="input__item">
                        <slot name="inputImage" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.form__content {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    gap: 4rem;
}

.form__col {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info__profile--file {
    margin-top: 15px;
    display: none;
}
</style>
