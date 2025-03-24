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
import ButtonSecondary from "@/Components/ButtonSecondary.vue";
import ModalPreview from "@/Components/ModalPreview.vue";
import useGetLastSegment from "@/Composables/useGetLastSegment.js";

export default {
    name: "FormOrder",
    components: {ModalPreview, ButtonSecondary, Anchor, ButtonDanger, ButtonPrimary, SearchBar},
    directives: {mask},
    props: {
        preventEnterAction: {
            type: Function,
            required: true
        },
        orderProp: {
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
        this.order = this.orderProp;
        this.user = this.orderProp.requester_user ?? {};
        this.items = this.orderProp.order_items ?? [];
        this.orderStatus = this.orderProp.status ?? '';
        this.toast = useSwalAlert({}, 2000);
        this.inputFile = this.$refs.proof_file;
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
        async getUsers(searchValue) {
            try {
                const response = await axios.get(this.authUser.level + '/user/get', {
                    params: {
                        per_page: 12,
                        order_by: 'created_at',
                        order_direction: 'desc',
                        filters: {
                            search: searchValue
                        }
                    }
                });

                this.users = response.data.data.data;

                if (response.data.data.total === 0) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return
                }
                this.resultsUsersVisible = true;
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
            }
        },
        async getProducts(searchValue) {
            try {
                const response = await axios.get(this.authUser.level + '/product/get', {
                    params: {
                        per_page: 12,
                        order_by: 'stock',
                        order_direction: 'desc',
                        filters: {
                            status: 'active',
                            in_stock: 1,
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
            }
        },
        handleSelectSupplier(user) {
            this.resultsUsersVisible = false;
            this.user = user;
        },
        handleClearFormSuppliers() {
            this.resultsUsersVisible = false;
            this.user = {};
        },
        handleSelectProduct(product) {
            this.customProductValue = product.name
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
                status: productQuantity.value > (this.product.stock - this.product.reserved) ? 'refused' : 'approved',
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
        },
        handlePreviewFile() {
            if (this.order.order_proof_path) {
                this.orderProofPath = new URL(this.order.order_proof_path, window.location.origin).href;
                this.file = null;
                this.modalPreviewShow = true;
                return
            } else if (this.$refs.proof_file.value === '') {
                return this.toast.fire({
                    icon: "warning",
                    title: 'Selecione um documento para visualizar.'
                });
            }
            this.file = this.$refs.proof_file.files[0]
            this.modalPreviewShow = true;
        },
        handleSelectNewFile(event) {
            this.order.order_proof_path = null;
            const valueArray = event.target.value.split('\\');
            this.$refs.input_text.value = valueArray[valueArray.length - 1]
        }
    },
    data() {
        return {
            order: {},
            users: {},
            user: {},
            products: {},
            product: {},
            items: [],
            customProductValue: '',
            formLoading: false,
            resultsUsersVisible: false,
            resultsProductsVisible: false,
            orderStatus: '',
            itemsOld: [],
            modalPreviewShow: false,
            file: File,
            orderProofPath: '',
            inputFile: undefined,
            textareaValue: 'asd'
        }
    },
    computed: {
        userData() {
            if (Object.keys(this.user).length !== 0) {
                return this.user.email + ' - ' + this.user.phone + ' - ' + this.user.sector
            }
            return 'Email - Telefone - Setor';
        },
        updating() {
            return this.orderProp.created_at !== undefined
        },
        isOperator() {
            return this.authUser.level === 'operator';
        },
        disableOptionsIfOperator() {
            if (this.isOperator) {
                return this.orderStatus === 'refused' || this.orderStatus === 'approved'
            }
            return false
        },
        fileFormated() {
            if (this.updating) {
                if (this.order.order_proof_path !== null) {
                    return useGetLastSegment(new URL(this.order.order_proof_path, window.location.origin).href);
                } else {
                    return this.$refs.input_text.value
                }
            }
        },
    }
}
</script>

<template>
    <ModalPreview
        v-if="modalPreviewShow"
        :visible="modalPreviewShow"
        @close="modalPreviewShow = false"
        :file="file" ref="modal_preview"
        :url="orderProofPath"
    />
    <div class="form__user">
        <form :id="formId">
            <div class="form__content" v-if="!formLoading">
                <div class="form__col">
                    <div class="input__item">
                        <label for="requester_user_id" class="form__label">Solicitante</label>
                        <SearchBar
                            placeholder="Pesquise por um usuário"
                            @search="getUsers"
                            custom-id="requester_user_id"
                            custom-name="requester_user_id"
                            custom-type="text"
                            :custom-value="user.name ?? ''"
                            @clear-form="handleClearFormSuppliers"
                            ref="search_bar"
                        />
                        <ul class="search__list" v-if="resultsUsersVisible">
                            <li
                                class="search__list-item form__input"
                                v-for="(user, index) in users"
                                :key="user.id"
                                :tabindex="index"
                                @click="handleSelectSupplier(user)"
                            >
                                {{ user.name + " - " + user.email }}
                            </li>
                        </ul>
                    </div>
                    <div class="input__item">
                        <label for="corporate_name" class="form__label">Detalhes do usuário</label>
                        <input @keydown="removeStyleInputError"
                               type="text" class="form__input form__disabled" name="" id="corporate_name"
                               :value="userData"
                               disabled
                        >
                    </div>
                    <div class="input__item input__item-file">
                        <div class="form__file_path">
                            <label for="" class="form__label">Anexo de comprovação de pedido</label>
                            <input id="proof_file" v-if="!updating" type="text"
                                   class="form__input form__disabled button__preview"
                                   placeholder="Nenhum documento selecionado" disabled ref="input_text">
                            <input id="proof_file" v-if="updating" type="text"
                                   class="form__input form__disabled button__preview"
                                   :value="fileFormated" disabled ref="input_text">
                            <input type="file" name="proof_file"
                                   id="proof_file" ref="proof_file"
                                   style="display: none"
                                   accept=".jpeg, .png, .jpg, .gif, .svg, .pdf"
                                   @change="handleSelectNewFile"
                            >
                        </div>
                        <ButtonSecondary class="button__preview" name="Visualizar" @click="handlePreviewFile"/>
                        <ButtonPrimary class="button__add_document" :name="updating ? 'Atualizar' : 'Novo'"
                                       @click="inputFile.click()"/>
                    </div>
                    <div class="input__item">
                        <label for="internal_information" class="input__label">Informações Adicionais</label>
                        <textarea class="form__input" id="internal_information" name="internal_information"
                                  placeholder="ex: Entrega agendada para dia dd/mm/aaaa">{{ order.internal_information ?? '' }}</textarea>
                    </div>
                </div>
                <div class="form__col form__col-two"></div>
                <div class="form__col">
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
                                ref="search_bar_user"

                            />
                            <ul class="search__list" v-if="resultsProductsVisible">
                                <li
                                    class="search__list-item form__input"
                                    v-for="(product, index) in products"
                                    :key="product.id"
                                    :tabindex="index"
                                    @click="handleSelectProduct(product)">
                                    {{
                                        product.name + " | Estoque: " + product.stock + " | Mínimo: " + product.minimum + " | Processando: " + product.processing + " | Reservado: " + product.reserved
                                    }}
                                </li>
                            </ul>
                        </div>
                        <div class="product__quantity">
                            <label for="product_quantity" class="form__label">Quantidade</label>
                            <input type="number" class="form__input" name="product_quantity" id="product_quantity"
                                   @keydown.prevent.enter="addProducts"
                                   min="1" value="1" :max="product.stock - product.reserved">
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
                        <div class="main__table">
                            <table class="table__products">
                                <thead class="header__table">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtde</th>
                                    <th>Status</th>
                                    <th>Remover</th>
                                </tr>
                                </thead>
                                <tbody class="table__products-tbody" v-if="items.length === 0">
                                <tr
                                    class="table__products-row"
                                >
                                    <td colspan="4">
                                        <p class="td__no_item">
                                            Adicione produtos para prosseguir. Mínimo 1.
                                        </p>
                                    </td>
                                </tr>
                                </tbody>
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
                                        <Anchor name="remover" @click.prevent="removeProducts(item)"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="updating">
                        <div class="input__item input__item--additional-information">
                            <div>
                                <label for="created_at" class="form__label">Criado em</label>
                                <input
                                    type="datetime-local" class="form__input form__disabled" name="" id="created_at"
                                    :value="useFormatDate(orderProp.created_at, 'input')"
                                    disabled
                                >
                            </div>
                            <div>
                                <label for="status" class="form__label">Status</label>
                                <select class="form__input" name="status" id="status"
                                        @change="removeStyleInputError"
                                        @keydown.prevent.enter="preventEnterAction"
                                        v-model="orderStatus"
                                        disabled
                                >
                                    <option v-if="!isOperator || disableOptionsIfOperator" value="approved">
                                        Aprovado
                                    </option>
                                    <option v-if="!isOperator || disableOptionsIfOperator" value="refused">
                                        Recusado
                                    </option>
                                    <option value="completed">Finalizado</option>
                                    <option value="in_progress">Em andamento</option>
                                    <option value="canceled">Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label for="updated_at" class="form__label">Última atualização</label>
                                <input
                                    type="datetime-local" class="form__input form__disabled" name="" id="updated_at"
                                    :value="useFormatDate(orderProp.updated_at, 'input')"
                                    disabled
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
<style scoped>
@import "/resources/css/components/order/form-order.css";
</style>
