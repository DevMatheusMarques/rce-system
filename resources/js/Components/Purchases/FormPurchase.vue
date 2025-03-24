<script>
import {mask} from "vue-the-mask";
import axios from "axios";
import SearchBar from "@/Components/SearchBar.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonDanger from "@/Components/ButtonDanger.vue";
import useFormatDate from "@/Composables/useFormatDate.js";
import useTranslateText from "../../Composables/useTranslateText.js";
import Anchor from "@/Components/Anchor.vue";
export default {
    name: "FormPurchase",
    components: {Anchor, ButtonDanger, ButtonPrimary, SearchBar},
    directives: {mask},
    props: {
        preventEnterAction: {
            type: Function,
            required: true
        },
        purchaseProp: {
            type: Object,
            default: () => ({}),
            required: false
        },
        formId: {
            type: String,
            required: true
        },
        authUser: {
            type: Object,
            default: () => ({}),
        }
    },
    mounted() {
        document.querySelector(".modal__body").classList.add('custom__modal-body--order');
        this.supplier = this.purchaseProp.supplier ?? {};
        this.items = this.purchaseProp.purchase_items ?? [];
        this.purchaseStatus = this.purchaseProp.status ?? '';
        this.toast = useSwalAlert({}, 2000);
    },
    methods: {
        useTranslateText,
        useFormatDate,
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        },
        async getSuppliers(searchValue) {
            try {
                const response = await axios.get(this.authUser.level + '/supplier/get', {
                    params: {
                        per_page: 12,
                        order_by: 'created_at',
                        order_direction: 'desc',
                        filters: {
                            search: searchValue
                        }
                    }
                });

                this.suppliers = response.data.data.data;

                if (response.data.data.total === 0) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return
                }
                this.resultsSuppliersVisible = true;
                return true
            } catch (errors) {
                //se erro, lança toast
                const message = errors.response.data.message;
                let icon = 'error';
                if (message === 'Nenhum registro encontrado') {
                    icon = 'warning';
                }
                this.toast.fire({
                    icon: icon,
                    title: message
                });
                return false
            } finally {
                //finaliza o estado de carregamento da tabela
                // this.loading = false;
            }
        }, async getProducts(searchValue) {
            try {
                const response = await axios.get(this.authUser.level + '/product/get', {
                    params: {
                        per_page: 12,
                        order_by: 'stock',
                        order_direction: 'asc',
                        filters: {
                            status: 'active',
                            search: searchValue
                        }
                    }
                });

                this.products = response.data.data.data;

                if (response.data.data.total === 0) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return
                }
                this.resultsProductsVisible = true;
                return true
            } catch (errors) {
                //se erro, lança toast
                const message = errors.response.data.message;
                let icon = 'error';
                if (message === 'Nenhum registro encontrado') {
                    icon = 'warning';
                }
                this.toast.fire({
                    icon: icon,
                    title: message
                });
                return false
            } finally {
                //finaliza o estado de carregamento da tabela
                // this.loading = false;
            }
        },
        handleSelectSupplier(supplier) {
            this.resultsSuppliersVisible = false;
            this.supplier = supplier;
        },
        handleClearFormSuppliers() {
            this.resultsSuppliersVisible = false;
            this.supplier = {};
        },
        handleSelectProduct(product) {
            this.customProductValue = product.name + " | Estoque: " + product.stock + " | Mínimo: " + product.minimum + " | Processando: " + product.processing
            this.resultsProductsVisible = false;
            this.product = product;
        },
        handleClearFormProducts() {
            this.resultsProductsVisible = false;
            this.customProductValue = '';
            this.product = {};
        },
        addProducts() {
            const productQuantity = document.getElementById('product_quantity');
            const item = {
                product_id: this.product.id,
                product_name: this.product.name,
                status: this.product.stock > this.product.minimum ? 'refused' : 'approved',
                product_quantity: productQuantity.value
            }
            const exists = this.items.some(el => el.product_id === item.product_id);
            if (exists) {
                return this.toast.fire({
                    icon: "warning",
                    title: 'Item já adicionado'
                });
            }
            if (Object.keys(this.product).length === 0) {
                return this.toast.fire({
                    icon: "warning",
                    title: 'Selecione um produto para adicionar'
                });
            }
            if (item.product_quantity <= 0) {
                return this.toast.fire({
                    icon: "warning",
                    title: 'Mínimo de 1 unidade por produto'
                });
            }
            this.items.push(item);
            this.handleClearFormProducts()
        },
        removeProducts(item) {
            this.items = this.items.filter(el => el.product_id !== item.product_id);
        }
    }, data() {
        return {
            suppliers: {},
            supplier: {},
            products: {},
            product: {},
            items: [],
            customProductValue: '',
            formLoading: false,
            resultsSuppliersVisible: false,
            resultsProductsVisible: false,
            purchaseStatus: '',
            itemsOld: []
        }
    }, computed: {
        updating() {
            return this.purchaseProp.created_at !== undefined
        },
        isOperator() {
            return this.authUser.level === 'operator';
        },
        disableOptionsIfOperator() {
            if (this.isOperator) {
                return this.purchaseStatus === 'refused' || this.purchaseStatus === 'approved'
            }
            return false
        }
    }
}
</script>

<template>
    <div class="form__supplier">
        <form :id="formId">
            <div class="form__content" v-if="!formLoading">
                <div class="form__col">
                    <div class="input__item">
                        <label for="supplier_id" class="form__label">Fornecedor:</label>
                        <SearchBar
                            placeholder="Pesquise por um fornecedor"
                            @search="getSuppliers"
                            custom-id="supplier_id"
                            custom-name="supplier_id"
                            custom-type="text"
                            :custom-value="supplier.cnpj ?? ''"
                            @clear-form="handleClearFormSuppliers"
                            ref="search_bar"
                        />
                        <ul class="search__list" v-if="resultsSuppliersVisible">
                            <li
                                class="search__list-item form__input"
                                v-for="(supplier, index) in suppliers"
                                :key="supplier.id"
                                :tabindex="index"
                                @click="handleSelectSupplier(supplier)"
                            >
                                {{ supplier.cnpj + " - " + supplier.corporate_name }}
                            </li>
                        </ul>
                    </div>
                    <div class="input__item">
                        <label for="corporate_name" class="form__label">Razão Social</label>
                        <input @keydown="removeStyleInputError"
                               type="text" class="form__input form__disabled" name="" id="corporate_name"
                               :value="supplier.corporate_name ?? ''"
                               disabled
                        >
                    </div>
                    <div class="input__item input__item-flex">
                        <div class="input__item-item">
                            <label for="products" class="form__label">Produtos</label>
                            <SearchBar
                                placeholder="Pesquise por um produto"
                                @search="getProducts"
                                custom-id="items"
                                custom-name="items"
                                custom-type="text"
                                :custom-value="customProductValue"
                                @clear-form="handleClearFormProducts"
                                ref="search_bar_supplier"

                            />
                            <ul class="search__list" v-if="resultsProductsVisible">
                                <li
                                    class="search__list-item form__input"
                                    v-for="(product, index) in products"
                                    :key="product.id"
                                    :tabindex="index"
                                    @click="handleSelectProduct(product)"
                                >
                                    {{
                                        product.name + " | Estoque: " + product.stock + " | Mínimo: " + product.minimum + " | Processando: " + product.processing
                                    }}
                                </li>
                            </ul>
                        </div>
                        <div>
                            <label for="product_quantity" class="form__label">Quantidade</label>
                            <input type="number" class="form__input" name="product_quantity" id="product_quantity"
                                   min="1" value="1">
                        </div>
                        <div class="input__item-save">
                            <ButtonPrimary
                                name="Adicionar"
                                @click="addProducts"
                                :delay="0"
                            />
                        </div>
                    </div>
                    <div class="input__item-product">
                        <div class="main__table" v-if="items.length > 0">
                            <table class="table__products">
                                <thead class="header__table">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtde</th>
                                    <th>Status</th>
                                    <th>Remover</th>
                                </tr>
                                </thead>
                                <tbody class="table__products-tbody">
                                <tr
                                    class="table__products-row"
                                    v-for="item in items" :key="product.product_id"
                                >
                                    <td class="">
                                        {{ item.product_name }}
                                    </td>
                                    <td class="">
                                        {{ item.product_quantity }}
                                    </td>
                                    <td class="">
                                        <span :class="['status__' + item.status]">
                                            {{ useTranslateText(item.status, 'pt') }}
                                        </span>
                                    </td>
                                    <td class="remove__item">
                                        <Anchor name="remover" @click.prevent="removeProducts(item)" />
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="updating">
                            <div class="input__item input__item--additional-information">
                                <div>
                                    <label for="status" class="form__label">Status</label>
                                    <select class="form__input" name="status" id="status"
                                            @change="removeStyleInputError"
                                            @keydown.prevent.enter="preventEnterAction"
                                            v-model="purchaseStatus"
                                            disabled
                                    >
                                        <option v-if="!isOperator || disableOptionsIfOperator" value="approved">Aprovado</option>
                                        <option v-if="!isOperator || disableOptionsIfOperator" value="refused">Recusado</option>
                                        <option value="completed">Finalizado</option>
                                        <option value="in_progress">Em andamento</option>
                                        <option value="canceled">Cancelado</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="created_at" class="form__label">Criado em</label>
                                    <input
                                        type="datetime-local" class="form__input form__disabled" name="" id="created_at"
                                        :value="useFormatDate(purchaseProp.created_at, 'input')"
                                        disabled
                                    >
                                </div>
                                <div>
                                    <label for="updated_at" class="form__label">Última atualização</label>
                                    <input
                                        type="datetime-local" class="form__input form__disabled" name="" id="updated_at"
                                        :value="useFormatDate(purchaseProp.updated_at, 'input')"
                                        disabled
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<style scoped>
@import "/resources/css/components/purchases/form-purchase.css";
</style>
