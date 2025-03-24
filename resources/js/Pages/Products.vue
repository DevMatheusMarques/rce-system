<script>
import {Head} from "@inertiajs/vue3";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonSecondary from "@/Components/ButtonSecondary.vue";
import ThTable from "@/Components/Table/ThTable.vue";
import axios from "axios";
import Pagination from "@/Components/Table/Pagination.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import RegisterProduct from "@/Components/Products/RegisterProduct.vue";
import TableProductsLoading from "@/Components/Products/TableProductsLoading.vue";
import UpdateProduct from "@/Components/Products/UpdateProduct.vue";
import ModalConfirm from "@/Components/ModalConfirm.vue";
import InputCheckbox from "@/Components/InputCheckbox.vue";
import SearchBar from "@/Components/SearchBar.vue";
import TrashButton from "@/Components/TrashButton.vue";
import CloneProduct from "@/Components/Products/CloneProduct.vue";
import useTranslateText from "@/Composables/useTranslateText.js";

export default {
    name: "Products",
    components: {
        CloneProduct,
        TrashButton,
        SearchBar,
        InputCheckbox,
        ModalConfirm,
        UpdateProduct,
        TableProductsLoading,
        RegisterProduct, Dropdown, DropdownLink, Pagination, ThTable, ButtonSecondary, ButtonPrimary, Head
    },
    methods: {
        useTranslateText,
        async getProducts() {
            //inicia carregamento da tabela
            this.loading = true;
            try {
                //requisição para recuperar os produtos
                const response = await axios.get(this.toRoute, {
                    params: {
                        per_page: this.perPage,
                        order_by: this.orderBy,
                        order_direction: this.orderDirection,
                        filters: this.filters
                    }
                });

                this.count = response.data.count;

                //atualiza produtos
                this.products = response.data.data.data;
                //atualiza paginação
                this.pagination = response.data.data;

                //para cada produto, verifica se algum produto estava selecionado antes da requisição
                //isso aqui é usado para permanecer checked durante a paginação
                this.products.forEach(product => {
                    product.checked = this.selectedIds.includes(product.id);
                });
                if (response.data.data.total === 0) {
                    this.toast.fire({
                        icon: "warning",
                        title: response.data.message
                    });
                    return
                }
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
                this.loading = false;
            }
        },
        orderItems(order) {
            //ordena os itens conforme necessidade
            //propriedade reativa para manter ordenação entre as requisições (ex: alterando entre as páginas)
            if (order) {
                this.orderBy = order.order_by;
                this.orderDirection = order.order_direction;
                this.getProducts();
            } else {
                this.orderBy = 'created_at';
                this.orderDirection = 'desc';
                this.getProducts();
            }
        },
        //navegando entre as páginas de fato
        async changePage(url) {
            this.toRoute = url;
            await this.getProducts();

            //checkbox principal inicia com true, se encontrar algum item não selecionado, ele se torna falso,
            //pois o checkbox princial só pode estar marcado, se todos os itens na quela lista estiverem marcados
            this.checkboxAll.checked = true;
            this.products.forEach(product => {
                if (!product.checked) {
                    this.checkboxAll.checked = false;
                }
            });
        },
        handleInputCheckbox(event, productId) {
            const inputsCheckbox = document.querySelectorAll('.input__checkbox');
            if (event.target.checked) {//se o check foi marcado
                this.selectedIds.push(productId);// adiciona o item na lista reativa de itens selecionados
                if (inputsCheckbox.length === this.selectedIds.length) {// se todos itens estão selecionados, o check principal é marcado
                    this.checkboxAll.checked = true
                }
            } else {//se o item foi desmarcado
                this.selectedIds = this.selectedIds.filter(id => id !== productId); //remova da lista
                this.checkboxAll.checked = false;// o check principal não está mais marcado
            }
        },
        selectAllCheckbox(event) {//checkbox principal
            const inputCheckbox = document.querySelectorAll('.input__checkbox');
            if (event.target.checked) {// se o principal foi marcado
                inputCheckbox.forEach(checkbox => {//cada item da página atual é marcado
                    checkbox.checked = true;
                    const productId = checkbox.getAttribute('id');
                    if (!this.selectedIds.includes(productId)) {//se não está na lista
                        this.selectedIds.push(productId);//é adicinado na lista
                    }
                });
            } else {//se o principal foi desmarcado
                inputCheckbox.forEach(checkbox => {// cada item da página atual é desmarcado
                    checkbox.checked = false;
                    const productId = checkbox.getAttribute('id');
                    if (this.selectedIds.includes(productId)) {//se está na lista
                        this.selectedIds = this.selectedIds.filter(id => id !== productId); //é removido e a lista é atualizada
                    }
                });
            }
        },
        handleUpdateProduct(product) {
            //abrindo modal com produto selecionado
            this.updateProduct = product;
            this.updateProductShow = true;
        },
        handleCloneProduct(product) {
            //abrindo modal clonado com produto selecionado
            this.cloneProduct = product;
            this.cloneProductShow = true;
        },
        handleDeleteProduct(productId) {
            //deletando 1 item pelo dropdown link
            this.deleteProductsShow = true;
            this.selectedIds = [productId];
        },
        existItemSelected() {
            if (this.selectedIds.length === 0) {//verifica se um item foi selecionado
                this.toast.fire({
                    icon: 'warning',
                    title: 'Selecione um item para continuar.',
                });
                return false
            }
            return true
        },
        handleDeleteManyProducts() {
            //ação em massa, deletando itens selecionados
            if (!this.existItemSelected()) {
                return
            }
            this.deleteProductsShow = true;
        },
        async handleStatusProduct(productId, currentStatus) {
            //altera status de 1 item
            let status = currentStatus === 'active' ? 'inactive' : 'active'// se ativo, inativo, se inativo, ativo
            this.selectedIds = [productId]
            await this.updateStatuses(status);
        },
        checkIfCurrentStatusHasRecords() {
            //verifica se no filtro atual (geral, ativos e inativos) possui algum item
            //se não possuir, o filtro é limpo (impedindo que quando estiver na página de ativos, e eu estiver exluindo/atualizando status)
            //a página não quebre
            const statusMap = {
                active: 'total',
                inactive: 'total'
            };
            /**
             * O statusMap é um objeto que mapeia os filtros "active" e "inactive" para "total".
             * Isso significa que se o filtro atual (statusSelected) for "active" ou "inactive" e não houver itens correspondentes
             * (this.count[this.statusSelected] === 0), o filtro será redefinido para "total".
             */
            if (statusMap[this.statusSelected] && this.count[this.statusSelected] === 0) {
                this.statusSelected = statusMap[this.statusSelected];
                this.clearAllFilters();
            }
        },
        async updateStatuses(status) {
            if (!this.existItemSelected()) { // inpede requisição sem itens
                return
            }
            try {
                this.loading = true
                const endpointRoute = `/${this.authUser.level}/product/update/statuses`;
                const response = await axios.put(endpointRoute, {
                    product_ids: this.selectedIds,
                    status: status,
                });

                if (!this.filteredBySearch()) {
                    this.count = response.data.data;
                }

                this.toast.fire({
                    icon: 'success',
                    title: response.data.message,
                });

                this.checkIfCurrentStatusHasRecords();
                this.clearSelected();
                await this.getProducts();
            } catch (error) {
                this.errors = error.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors,
                });
            } finally {
                this.loading = false
            }
        },
        clearStatusFilters() {
            //limpa os filtros atuais
            this.filters.status = null;
            //limpa a rota (volta para a página 1)
            this.toRoute = 'admin/product/get';
        },
        clearAllFilters() {
            document.getElementById('search-input').value = '';
            this.filterSearchActive = false;

            const filters = this.filters;
            Object.keys(filters).forEach(key => {
                filters[key] = null;
            });
            this.statusSelected = 'total';
        },
        clearSelected() {
            //todos os itens serão desmarcados
            this.selectedIds = [];
            this.checkboxAll.checked = false;
        },
        async changeStatusFilters(status) { //alterando busca entre os status (geral, ativo, inativo)

            //mensagens dinâmicas
            const noItemsMessage = {
                active: 'Não existe itens ativos',
                inactive: 'Não existe itens inativos'
            };

            if (status !== 'total') {
                const itemCount = this.count[status];
                if (itemCount === 0 || Object.keys(this.count).length === 0) {// sem itens selecionados, ou o status não possui registros
                    return this.toast.fire({
                        icon: 'info',
                        title: noItemsMessage[status]
                    });
                }
                this.filters.status = status;//adiciona novo filtro
            } else {
                this.clearStatusFilters();// se total, limpa os filtros
            }
            this.clearSelected();
            this.toRoute = this.defaultRoute;
            this.getProducts();
            this.statusSelected = status;//propriedade reativa que controla o style das opções de filtros por status (Total, Ativos, Inativos)
        },
        filteredBySearch() {
            return this.filters.search !== '' && this.filters.search
        },
        async deleteProduct() {//função para requisição de deletar itens
            try {
                const endpointRoute = '/' + this.authUser.level + '/product/delete';
                const response = await axios.delete(endpointRoute, {
                    params: {
                        product_ids: this.selectedIds
                    }
                });
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                if (this.filterSearchActive) {
                    if (this.count.total === this.selectedIds.length) {
                        this.clearAllFilters();
                        this.filterSearchActive = false;
                    }
                } else {
                    this.count = response.data.data.count;
                    this.checkIfCurrentStatusHasRecords();
                }

                if (this.selectedIds.length === this.products.length) {
                    this.toRoute = this.defaultRoute;
                }

                this.getProducts();
                this.clearSelected();
                this.deleteProductsShow = false;
            } catch
                (errors) {
                this.errors = errors.response.data.message;
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        async handleSearch(val) {
            if (val === '') {//verifica se um item foi selecionado
                return this.toast.fire({
                    icon: 'warning',
                    title: 'Insira algum valor para continuar.',
                });
            }
            let value = useTranslateText(val, 'en');
            this.clearAllFilters();
            this.statusSelected = 'total';
            this.filters.search = value;
            this.toRoute = this.defaultRoute;
            this.filterSearchActive = await this.getProducts();
            if (!this.filterSearchActive) {
                this.clearAllFilters();
                await this.getProducts()
            }
        },
        async clearFilterButton() {
            this.clearAllFilters()
            await this.getProducts();
        }
    },
    async mounted() {//carragamento de componentes
        this.toast = useSwalAlert({}, 2000);
        await this.getProducts();
        this.checkboxAll = document.getElementById('checkbox-all');
    },
    data() {
        return {
            products: [],
            pagination: {},
            toRoute: this.authUser.level + '/product/get',
            checkboxAll: '',
            selectedIds: [],
            registerProductShow: false,
            updateProductShow: false,
            cloneProductShow: false,
            deleteProductsShow: false,
            updateProduct: '',
            cloneProduct: '',
            stateDropdown: true,
            loading: false,
            count: {},
            statusSelected: 'total',
            filters: {},
            orderBy: 'updated_at',
            orderDirection: 'desc',
            perPage: 20,
            filterSearchActive: false,
            defaultRoute: this.authUser.level + '/product/get'
        }
    },
    props: {
        authUser: {
            type: Object,
            required: true
        }
    }
}
</script>

<template>
    <div class="content__content">
        <Head name="Produtos"/>
        <ModalConfirm
            item="produtos"
            :additional-information="'(' + selectedIds.length + ' produtos selecionados)'"
            :visible="deleteProductsShow"
            @delete="deleteProduct"
            @close="deleteProductsShow = false"
        />
        <RegisterProduct
            :visible="registerProductShow"
            :auth-user="authUser"
            @close="registerProductShow = false"
            @new-product="getProducts"
        />
        <UpdateProduct
            :product="updateProduct"
            :visible="updateProductShow"
            :auth-user="authUser"
            @close="updateProductShow = false"
            @update-product="getProducts"
        />
        <CloneProduct
            :product="cloneProduct"
            :visible="cloneProductShow"
            :auth-user="authUser"
            @close="cloneProductShow = false"
            @new-product="getProducts"
        />
        <div class="content">
            <div class="content__header">
                <ButtonPrimary
                    name="Novo Produto"
                    @click="registerProductShow = true"
                />
            </div>
            <div class="content__main">
                <div class="main__search">
                    <SearchBar @search="handleSearch"/>
                    <TrashButton @click="clearFilterButton"/>
                </div>
                <div class="main__abstract">
                    <div class="abstract__statuses">
                        <div
                            id="total"
                            :class="['item', statusSelected === 'total' ? 'item__status--total' : '',
                                 filterSearchActive ? 'item__status--search' : '']"
                            @click="changeStatusFilters('total')"
                        >
                            <div class="item__status--text">
                                Total
                            </div>
                            <div class="item__status--count">
                                {{ this.count.total ?? 0 }}
                            </div>
                        </div>
                        <div
                            id="active"
                            :class="['item', statusSelected === 'active' ? 'item__status--active' : '',
                                 filterSearchActive ? 'item__status--search-none' : '']"
                            @click="changeStatusFilters('active')"
                        >
                            <div class="item__status--text">
                                Ativos
                            </div>
                            <div class="item__status--count">
                                {{ this.count.active ?? 0 }}
                            </div>
                        </div>
                        <div
                            id="inactive"
                            :class="['item', statusSelected === 'inactive' ? 'item__status--inactive' : '',
                                 filterSearchActive ? 'item__status--search-none' : '']"
                            @click="changeStatusFilters('inactive')"
                        >
                            <div class="item__status--text">
                                Inativos
                            </div>
                            <div class="item__status--count">
                                {{ this.count.inactive ?? 0 }}
                            </div>
                        </div>
                    </div>
                    <div class="abstract__selected">
                        <div class="selected">
                            {{ selectedIds.length }} registro(s) selecionado(s)
                        </div>
                        <div class="options">
                            <ButtonSecondary name="Ativar" @click="updateStatuses('active')"/>
                            <ButtonSecondary name="Inativar" @click="updateStatuses('inactive')"/>
                            <template v-if="authUser.level === 'admin'">
                                <ButtonSecondary name="Excluir" @click="handleDeleteManyProducts"/>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="main__table">
                    <table class="table">
                        <thead class="table__thead">
                        <tr>
                            <ThTable th-id="id" :th-ordered="false">
                                <InputCheckbox @click="selectAllCheckbox" ckb-id="checkbox-all"/>
                            </ThTable>
                            <ThTable th-id="name" @order="orderItems">
                                Nome
                            </ThTable>
                            <ThTable th-id="category" @order="orderItems" class="th__center">
                                Categoria
                            </ThTable>
                            <ThTable th-id="minimum" @order="orderItems" class="th__center">
                                Mínimo
                            </ThTable>
                            <ThTable th-id="stock" @order="orderItems" class="th__center">
                                Em Estoque
                            </ThTable>
                            <ThTable th-id="processing" @order="orderItems" class="th__center">
                                Processando
                            </ThTable>
                            <ThTable th-id="status" @order="orderItems" class="th__center">
                                Status
                            </ThTable>
                            <ThTable th-id="id" :th-ordered="false" class="th__center">Ações</ThTable>
                        </tr>
                        </thead>
                        <TableProductsLoading v-if="loading"/>
                        <Transition name="fade">
                            <tbody v-if="!loading">
                            <tr v-if="products.length === 0">
                                <td colspan="9" class="row not--found">Sem registros encontrados, adicione um novo
                                    produto.
                                </td>
                            </tr>
                            <tr class="row" v-for="product in products" :key="product.id">
                                <td>
                                    <InputCheckbox
                                        :ckb-id="product.id.toString()"
                                        :ckb-checked="product.checked"
                                        ckb-class="input__checkbox"
                                        @click="handleInputCheckbox($event, product.id)"
                                    />
                                </td>
                                <td class="cursor__pointer adjustment__text" @click="handleUpdateProduct(product)">
                                    {{ product.name }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateProduct(product)">
                                    {{ useTranslateText(product.category) }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateProduct(product)">
                                    {{ product.minimum }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateProduct(product)">
                                    {{ product.stock }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateProduct(product)">
                                    {{ product.processing }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateProduct(product)">
                                    <p :class="[product.status === 'active' ? 'status--active' : 'status--inactive']">
                                        {{ product.status === 'active' ? 'Ativo' : 'Inativo' }}
                                    </p>
                                </td>
                                <td class="action__button">
                                    <div class="dropdown">
                                        <Dropdown button-label="Ações" :opened="stateDropdown"
                                                  @click="this.stateDropdown = !this.stateDropdown">
                                            <DropdownLink @click="handleStatusProduct(product.id, product.status)">
                                                {{ product.status === 'active' ? 'Inativar' : 'Ativar' }}
                                            </DropdownLink>
                                            <DropdownLink @click="handleCloneProduct(product)">Clonar</DropdownLink>
                                            <template v-if="authUser.level === 'admin'">
                                                <DropdownLink @click="handleDeleteProduct(product.id)">Excluir
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </Transition>
                    </table>
                </div>
                <div class="content__footer">
                    <Pagination @to-page="changePage" :pagination="pagination"/>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import "/resources/css/pages/products.css";
</style>
