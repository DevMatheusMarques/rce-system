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
import ModalConfirm from "@/Components/ModalConfirm.vue";
import SearchBar from "@/Components/SearchBar.vue";
import TrashButton from "@/Components/TrashButton.vue";
import useTranslateText from "@/Composables/useTranslateText.js";
import RegisterSupplier from "@/Components/Suppliers/RegisterSupplier.vue";
import TableSupplierLoading from "@/Components/Suppliers/TableSupplierLoading.vue";
import UpdateSupplier from "@/Components/Suppliers/UpdateSupplier.vue";

export default {
    name: "Supplier",
    components: {
        UpdateSupplier,
        TableSupplierLoading,
        RegisterSupplier,
        TrashButton,
        SearchBar,
        ModalConfirm,
        Dropdown, DropdownLink, Pagination, ThTable, ButtonSecondary, ButtonPrimary, Head
    },
    methods: {
        useTranslateText,
        async getSuppliers() {
            //inicia carregamento da tabela
            this.loading = true;
            try {
                //requisição para recuperar os fornecedores
                const response = await axios.get(this.toRoute, {
                    params: {
                        per_page: this.perPage,
                        order_by: this.orderBy,
                        order_direction: this.orderDirection,
                        filters: this.filters
                    }
                });

                //atualiza fornecedores
                this.suppliers = response.data.data.data;
                //atualiza paginação
                this.pagination = response.data.data;

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
                this.getSuppliers();
            } else {
                this.orderBy = 'created_at';
                this.orderDirection = 'desc';
                this.getSuppliers();
            }
        },
        //navegando entre as páginas de fato
        async changePage(url) {
            this.toRoute = url;
            await this.getSuppliers();
        },
        handleUpdateSupplier(supplier) {
            //abrindo modal com fornecedor selecionado
            this.supplier = supplier;
            this.updateSupplierShow = true;
        },
        handleDeleteProduct(supplier) {
            //deletando 1 item pelo dropdown link
            this.deleteSupplierShow = true;
            this.supplier = supplier;
        },
        clearAllFilters() {
            const filters = this.filters;
            Object.keys(filters).forEach(key => {
                filters[key] = null;
            });
        },
        async deleteProduct() {//função para requisição de deletar itens
            try {

                const endpointRoute = '/' + this.authUser.level + '/supplier/delete/' + this.supplier.id;
                const response = await axios.delete(endpointRoute);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });

                if (this.suppliers.length === 1) {//se for o último fornecedor da página atual
                    this.toRoute = this.defaultRoute;
                }

                this.getSuppliers();
                this.deleteSupplierShow = false;
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
            this.clearAllFilters();
            this.filters.search = val;
            this.toRoute = this.defaultRoute;
            await this.getSuppliers();
        },
        async clearFilterButton() {
            this.clearAllFilters()
            document.getElementById('search-input').value = '';
            await this.getSuppliers();
        }
    },
    async mounted() {//carragamento de componentes
        this.toast = useSwalAlert({}, 1500);
        await this.getSuppliers();
    },
    data() {
        return {
            suppliers: [],
            pagination: {},
            supplier: {},
            toRoute: this.authUser.level + '/supplier/get',
            registerSupplierShow: false,
            updateSupplierShow: false,
            deleteSupplierShow: false,
            stateDropdown: true,
            loading: false,
            filters: {},
            orderBy: 'updated_at',
            orderDirection: 'desc',
            perPage: 20,
            defaultRoute: this.authUser.level + '/supplier/get'
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
        <Head name="Suppliers"/>
        <ModalConfirm
            item="fornecedor"
            :additional-information="'Fornecedor ' + supplier.cnpj + ' selecionado.'"
            :visible="deleteSupplierShow"
            @delete="deleteProduct"
            @close="deleteSupplierShow = false"
        />
        <RegisterSupplier
            :visible="registerSupplierShow"
            :auth-user="authUser"
            @close="registerSupplierShow = false"
            @new-supplier="getSuppliers"
        />
        <UpdateSupplier
            :supplier="supplier"
            :visible="updateSupplierShow"
            :auth-user="authUser"
            @close="updateSupplierShow = false"
            @update-supplier="getSuppliers"
        />
        <div class="content">
            <div class="content__header">
                <ButtonPrimary
                    name="Novo Fornecedor"
                    @click="registerSupplierShow = true"
                />
            </div>
            <div class="content__main">
                <div class="main__search">
                    <SearchBar @search="handleSearch"/>
                    <TrashButton @click="clearFilterButton"/>
                </div>
                <div class="main__abstract">
                </div>
                <div class="main__table">
                    <table class="table">
                        <thead class="table__thead">
                        <tr>
                            <ThTable th-id="cnpj" @order="orderItems">
                                CNPJ
                            </ThTable>
                            <ThTable th-id="corporate_name" @order="orderItems" class="th__center">
                                Razão Social
                            </ThTable>
                            <ThTable th-id="address_city" @order="orderItems" class="th__center">
                                Cidade
                            </ThTable>
                            <ThTable th-id="address_state" @order="orderItems" class="th__center">
                                Estado
                            </ThTable>
                            <template v-if="authUser.level === 'admin'">
                                <ThTable th-id="id" :th-ordered="false" class="th__center">Ações</ThTable>
                            </template>
                        </tr>
                        </thead>
                        <TableSupplierLoading v-if="loading" :auth-user="authUser"/>
                        <Transition name="fade">
                            <tbody v-if="!loading">
                            <tr v-if="suppliers.length === 0">
                                <td colspan="9" class="row not--found">Sem registros encontrados, adicione um novo
                                    fornecedor.
                                </td>
                            </tr>
                            <tr class="row" v-for="supplier in suppliers" :key="supplier.id">
                                <td class="cursor__pointer adjustment__text" @click="handleUpdateSupplier(supplier)">
                                    {{ supplier.cnpj }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateSupplier(supplier)">
                                    {{ supplier.corporate_name }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateSupplier(supplier)">
                                    {{ supplier.address_city }}
                                </td>
                                <td class="cursor__pointer text__center" @click="handleUpdateSupplier(supplier)">
                                    {{ supplier.address_state }}
                                </td>
                                <template v-if="authUser.level === 'admin'">
                                    <td class="action__button">
                                        <div class="dropdown">
                                            <Dropdown button-label="Ações" :opened="stateDropdown"
                                                      @click="this.stateDropdown = !this.stateDropdown">
                                                    <DropdownLink @click="handleDeleteProduct(supplier)">Excluir
                                                    </DropdownLink>
                                            </Dropdown>
                                        </div>
                                    </td>
                                </template>
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
@import "/resources/css/pages/supplier.css";
</style>
